<?php
session_start();
require_once "conexion.php";

header('Content-Type: application/json');

// 1. Validar sesión
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['status' => 'error', 'msg' => 'No hay sesión iniciada']);
    exit;
}

$idUsuario = $_SESSION['id_usuario'];
$input = json_decode(file_get_contents('php://input'), true);
$accion = $input['accion'] ?? '';

// --------------------------------------------------------------------------------
// ACCIÓN: AGREGAR AL CARRITO (BLINDADO)
// --------------------------------------------------------------------------------
if ($accion === 'agregar') {
    $producto = $input['producto'];
    $idUsuario = $_SESSION['id_usuario'];
    $idGrupo = $conexion->real_escape_string($producto['id_grupo']); 

    $idZapato = intval($producto['id']);
    $categoriaId = intval($producto['id_categoria'] ?? 1);
    $tipoPaquete = $conexion->real_escape_string($producto['paquete'] ?? 'seis');
    // Si viene idColor, úsalo, si no, NULL
    $idColor = (!empty($producto['idColor'])) ? intval($producto['idColor']) : "NULL";

    // --- CORRECCIÓN 1: OBTENER PRECIO REAL Y OFERTA DESDE LA BD ---
    // No confiamos en lo que envía el JS. Consultamos el precio original y si tiene oferta activa.
    $sqlPrecio = "SELECT z.precio, 
                         (SELECT porcentaje FROM ofertas WHERE id_zapato = z.id_zapato AND estado = 1 LIMIT 1) as descuento
                  FROM zapatos z 
                  WHERE z.id_zapato = $idZapato";
    
    $resP = $conexion->query($sqlPrecio);
    if (!$resP || $resP->num_rows === 0) {
        echo json_encode(['status' => 'error', 'msg' => 'Producto no encontrado']);
        exit;
    }
    
    $datosZapato = $resP->fetch_assoc();
    $precioBaseBD = floatval($datosZapato['precio']);
    $porcentajeDesc = intval($datosZapato['descuento'] ?? 0);

    // 1. Aplicar oferta si existe (Ej: Zapato $200 con 10% off = $180)
    if ($porcentajeDesc > 0) {
        $precioBaseBD = $precioBaseBD - ($precioBaseBD * ($porcentajeDesc / 100));
    }

    // 2. Aplicar descuento por volumen (Paquete)
    // CORRECCIÓN 2: Ajustado a 0.8 (20%) para coincidir con tu Javascript
    $factorPaquete = ($tipoPaquete === 'doce') ? 0.80 : 0.90; 
    $precioFinalPorPar = $precioBaseBD * $factorPaquete;

    // --- FIN DE CÁLCULO DE PRECIO ---

    foreach ($producto['desgloseTallas'] as $tallaValor => $cantidad) {
        $cantidad = intval($cantidad);
        if ($cantidad > 0) {
            // Buscamos el id_talla
            $resTalla = $conexion->query("SELECT id_talla FROM tallas WHERE valor = '$tallaValor' LIMIT 1");
            if($resTalla && $resTalla->num_rows > 0){
                $filaTalla = $resTalla->fetch_assoc();
                $idTalla = $filaTalla['id_talla'];

                // CALCULAR SUBTOTAL REAL (Precio BD * Cantidad)
                $subtotalFila = $precioFinalPorPar * $cantidad;

                $sql = "INSERT INTO carrito_zapato 
                    (id_usuario, id_grupo, id_zapato, id_talla, id_color, cantidad, categoria_id, tipo_paquete, subtotal) 
                    VALUES 
                    ($idUsuario, '$idGrupo', $idZapato, $idTalla, $idColor, $cantidad, $categoriaId, '$tipoPaquete', $subtotalFila)";

                $conexion->query($sql);
            }
        }
    }
    echo json_encode(['status' => 'success']);
    exit;
}

// --------------------------------------------------------------------------------
// ACCIÓN: ELIMINAR GRUPO
// --------------------------------------------------------------------------------
if ($accion === 'eliminar_grupo') {
    $idGrupo = $conexion->real_escape_string($input['id_grupo']);
    $idUsuario = $_SESSION['id_usuario'];

    $sql = "DELETE FROM carrito_zapato WHERE id_grupo = '$idGrupo' AND id_usuario = $idUsuario";

    if ($conexion->query($sql)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'msg' => $conexion->error]);
    }
    exit;
}

// --------------------------------------------------------------------------------
// ACCIÓN: OBTENER CARRITO (CORRECCIÓN IMÁGENES)
// --------------------------------------------------------------------------------
if ($accion === 'obtener_carrito') {
    $idUsuario = $_SESSION['id_usuario'];

    $sql = "SELECT 
                cz.*, 
                z.nombre AS zapato_nombre, 
                t.valor AS talla_valor,
                c.nombre AS color_nombre,
                (
                    SELECT ruta 
                    FROM imagenes_zapato 
                    WHERE id_zapato = cz.id_zapato 
                    AND (id_color = cz.id_color OR id_color IS NULL) 
                    ORDER BY (id_color = cz.id_color) DESC, orden ASC 
                    LIMIT 1
                ) AS ruta_imagen
            FROM carrito_zapato cz
            INNER JOIN zapatos z ON cz.id_zapato = z.id_zapato
            INNER JOIN tallas t ON cz.id_talla = t.id_talla
            LEFT JOIN colores_zapato c ON cz.id_color = c.id_color
            WHERE cz.id_usuario = $idUsuario
            ORDER BY cz.id DESC";

    $res = $conexion->query($sql);
    $carritoFormateado = [];

    // URL base absoluta del proyecto
    $urlBase = "/Mayoreo_Etnia/"; 

    if ($res) {
        while ($fila = $res->fetch_assoc()) {
            $idG = $fila['id_grupo'];

            if (!isset($carritoFormateado[$idG])) {
                
                // CORRECCIÓN 3: LIMPIEZA PROFUNDA DE LA RUTA
                // Si la ruta viene como "../Assets/...", quitamos el "../"
                $rutaDB = $fila['ruta_imagen'];
                $rutaLimpia = str_replace('../', '', $rutaDB); // Quita los puntos
                $rutaLimpia = ltrim($rutaLimpia, '/'); // Quita barra inicial si queda

                $carritoFormateado[$idG] = [
                    'id_grupo' => $idG,
                    'id' => $fila['id_zapato'],
                    'nombre' => $fila['zapato_nombre'],
                    // Resultado final: /Mayoreo_Etnia/Assets/Imagenes/...
                    'imagen' => $urlBase . $rutaLimpia, 
                    'paquete' => $fila['tipo_paquete'],
                    'color' => $fila['color_nombre'] ?? 'Estándar',
                    'precioTotal' => 0,
                    'cantidadTotal' => 0,
                    'desgloseTallas' => []
                ];
            }

            $talla = $fila['talla_valor'];
            $carritoFormateado[$idG]['desgloseTallas'][$talla] = intval($fila['cantidad']);
            $carritoFormateado[$idG]['precioTotal'] += floatval($fila['subtotal']); 
            $carritoFormateado[$idG]['cantidadTotal'] += intval($fila['cantidad']);
        }
    }

    echo json_encode(['status' => 'success', 'carrito' => array_values($carritoFormateado)]);
    exit;
}
?>