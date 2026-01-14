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

// api_carrito.php
if ($accion === 'agregar') {
    $producto = $input['producto'];
    $idUsuario = $_SESSION['id_usuario'];
    $idGrupo = $conexion->real_escape_string($producto['id_grupo']); // Capturamos el ID del grupo

    // CAPTURA DE DATOS (Con seguridad para evitar el Warning)
    $idZapato = intval($producto['id']);
    $categoriaId = intval($producto['id_categoria'] ?? 1);
    $precioBase = floatval($producto['precioUnitario'] ?? 0);
    $tipoPaquete = $conexion->real_escape_string($producto['paquete'] ?? 'seis');
    $idColor = (!empty($producto['idColor'])) ? intval($producto['idColor']) : "NULL";

    // CÁLCULO DEL PRECIO SEGÚN PAQUETE
    // 6 pares = 10% desc (0.9) | 12 pares = 15% desc (0.85)
    $factorPaquete = ($tipoPaquete === 'doce') ? 0.85 : 0.90;
    $precioFinalPorPar = $precioBase * $factorPaquete;


    foreach ($producto['desgloseTallas'] as $tallaValor => $cantidad) {
        $cantidad = intval($cantidad);
        if ($cantidad > 0) {
            // Buscamos el id_talla (1-8) según el valor (22.5-26.0)
            $resTalla = $conexion->query("SELECT id_talla FROM tallas WHERE valor = '$tallaValor' LIMIT 1");
            $filaTalla = $resTalla->fetch_assoc();
            $idTalla = $filaTalla['id_talla'];

            // CALCULAR SUBTOTAL REAL
            $subtotalFila = $precioFinalPorPar * $cantidad;

            $sql = "INSERT INTO carrito_zapato 
                (id_usuario, id_grupo, id_zapato, id_talla, id_color, cantidad, categoria_id, tipo_paquete, subtotal) 
                VALUES 
                ($idUsuario, '$idGrupo', $idZapato, $idTalla, $idColor, $cantidad, $categoriaId, '$tipoPaquete', $subtotalFila)";

            $conexion->query($sql);
        }
    }
    echo json_encode(['status' => 'success']);
    exit;
}

if ($accion === 'eliminar_grupo') {
    $idGrupo = $conexion->real_escape_string($input['id_grupo']);
    $idUsuario = $_SESSION['id_usuario'];

    // Borramos todas las filas que coincidan con el grupo y el usuario actual
    $sql = "DELETE FROM carrito_zapato WHERE id_grupo = '$idGrupo' AND id_usuario = $idUsuario";

    if ($conexion->query($sql)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'msg' => $conexion->error]);
    }
    exit;
}

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

    // URL base para que las imágenes carguen en todas las páginas (Ofertas, Populares, etc.)
    $urlBase = "/Mayoreo_Etnia/"; 

    if ($res) {
        while ($fila = $res->fetch_assoc()) {
            $idG = $fila['id_grupo'];

            if (!isset($carritoFormateado[$idG])) {
                $rutaLimpia = ltrim($fila['ruta_imagen'], '/');

                $carritoFormateado[$idG] = [
                    'id_grupo' => $idG,
                    'id' => $fila['id_zapato'],
                    'nombre' => $fila['zapato_nombre'],
                    // Usamos ruta absoluta para evitar iconos rotos fuera del index
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
            // Sumamos el subtotal real de la fila (ej. 2065.50)
            $carritoFormateado[$idG]['precioTotal'] += floatval($fila['subtotal']); 
            $carritoFormateado[$idG]['cantidadTotal'] += intval($fila['cantidad']);
        }
    }

    echo json_encode(['status' => 'success', 'carrito' => array_values($carritoFormateado)]);
    exit;
}

?>