<?php
session_start();
require_once "conexion.php";

// 1. CONSULTA DE POPULARES CON OFERTAS
$sql = "
SELECT 
    z.id_zapato, z.nombre, z.precio, z.id_categoria, c.nombre AS nombre_categoria,    
    p.orden,
    o.porcentaje AS descuento
FROM populares p
INNER JOIN zapatos z ON p.id_zapato = z.id_zapato
INNER JOIN categorias c ON z.id_categoria = c.id_categoria
LEFT JOIN ofertas o ON z.id_zapato = o.id_zapato AND o.estado = 1
ORDER BY p.orden ASC
";

$resultado = $conexion->query($sql);

$todosLosPopulares = [];
while ($row = $resultado->fetch_assoc()) {
    $todosLosPopulares[] = $row;
}

// AGREGAMOS ESTA FUNCIÓN (IGUAL QUE EN INDEX.PHP)
function js_escape($string)
{
    return str_replace(["\r", "\n", "'", '"'], ['', '', "\\'", '\\"'], $string);
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Los Más Deseados - Etnia</title>
    <link rel="stylesheet" href="estilos.css">
    <link rel="stylesheet" href="https://www.flaticon.es/icon-fonts-mas-descargados?weight=bold&type=uicon.css">
    <style>
        /* --- ESTILOS DEL PODIO (ESCALAS) --- */

        /* El contenedor alinea los items abajo (flex-end) para que crezcan hacia arriba */
        .contenedor-podio {
            display: flex;
            justify-content: center;
            align-items: flex-end;
            gap: 15px;
            margin-top: 20px;
            margin-bottom: 80px;
            /* Separación grande con la fila de abajo */
            flex-wrap: nowrap;
            /* Forzamos una fila en desktop */
        }

        /* #1: EL REY (Grande) */
        .podio-grande {
            transform: scale(1.30);
            /* 30% más grande */
            z-index: 10;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            /* Sombra más fuerte */
            border: 2px solid var(--color_uno);
            /* Borde café para resaltar */
        }

        /* #2 y #3: MEDIANOS */
        .podio-medio {
            transform: scale(1.15);
            /* 15% más grande */
            z-index: 5;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        /* #4 y #5: NORMALES (Sin escala extra, solo z-index base) */
        .podio-normal {
            transform: scale(1);
            z-index: 1;
            opacity: 0.9;
            /* Un poquito menos llamativos */
        }

        .podio-normal:hover {
            opacity: 1;
        }

        /* Contenedor del resto de zapatos */
        .contenedor-resto {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 20px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            /* Una línea sutil de separación */
            margin-top: 20px;
        }

        /* Badges */
        .etiqueta-flotante-oferta {
            position: absolute;
            top: 10px;
            left: 10px;
            background: #e74c3c;
            color: white;
            padding: 4px 8px;
            font-size: 12px;
            font-weight: bold;
            border-radius: 4px;
            z-index: 15;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .etiqueta-lugar {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 14px;
            z-index: 15;
            pointer-events: none;
        }

        .badge-descuento {
            background-color: #e74c3c;
            color: white;
            font-size: 11px;
            font-weight: bold;
            padding: 2px 5px;
            border-radius: 4px;
            margin-left: 5px;
            vertical-align: middle;
        }

        .img__item_01 figure {
            position: relative;
        }

        /* Ajuste Responsive para que no se rompa en celular */
        @media (max-width: 768px) {
            .contenedor-podio {
                flex-wrap: wrap;
                transform: scale(0.9);
                margin-bottom: 20px;
            }

            .podio-grande,
            .podio-medio,
            .podio-normal {
                transform: scale(1) !important;
                width: 100%;
            }
        }
    </style>
</head>

<body>

    <header>
        <div class="container__header">
            <section class="layout">
                <div class="logo">
                    <a href="/Mayoreo_Etnia/index.php"><img src="../Imagenes/Logo/etnia_logo.png" alt="Logo"></a>
                </div>
                <div class="menu__tienda">
                    <nav>
                        <ul>
                            <li><a href="../../index.php">HOME</a></li>
                            <li><a href="populares.php" style="color: var(--color_uno);">POPULARES</a></li>
                            <li><a href="ofertas.php">OFERTAS</a></li>
                            <li><a href="favoritos.php">FAVORITOS</a></li>
                            <li><a href="#" onclick="toggleBuscador(event)">
                                    <div class="icono__busqueda"><img src="../Imagenes/Iconos/busqueda.png"
                                            alt="Buscar"></div>
                                </a></li>
                        </ul>
                    </nav>
                </div>
                <div class="menu__usuario">
                    <nav>
                        <ul>
                            <li><a href="#" id="btn-abrir-carrito">CARRITO</a></li>

                            <?php if (isset($_SESSION['id_usuario'])): ?>
                                <li><a href="#" style="color:var(--color_uno); font-weight:bold; font-size:13px;">HOLA,
                                        <?= strtoupper($_SESSION['nombre']) ?></a></li>
                                <li><a href="auth.php?accion=logout" style="color:#e74c3c; font-size:11px;">(SALIR)</a></li>
                            <?php else: ?>
                                <li><a href="#" onclick="abrirModalAuth()">CUENTA</a></li>
                            <?php endif; ?>

                            <li><a href="#">
                                    <div class="icono__usuario"><img src="../Imagenes/Iconos/usuario.png" alt="Usuario">
                                    </div>
                                </a></li>
                        </ul>
                    </nav>
                </div>
            </section>
        </div>
    </header>

    <main>
        <div class="container__portada div__offset"
            style="height: auto; min-height: auto; background: none; margin-bottom: 10px;">
            <div class="portada" style="display: block; text-align: center; padding-top: 20px;">
                <h1 style="color: var(--color_tres); font-size: 3rem; font-weight: 800; text-transform: uppercase;">LOS
                    MÁS DESEADOS</h1>
                <p style="font-size: 1.2rem; color: #555;">El Top 5 de la temporada</p>
            </div>
        </div>

        <?php
        // Función modificada para aceptar una CLASE EXTRA MANUAL ($clasePodio)
        // Función corregida usando el estilo del INDEX (cerrando PHP)
        function renderizarTarjeta($zapato, $conexion, $clasePodio = '')
        {
            $id = $zapato['id_zapato']; // ID de la base de datos (ej: 1)
            
            // 1. VARIABLES SEGURAS (Igual que en el Index)
            $idCategoria = intval($zapato['id_categoria']);
            $nombreJS = js_escape($zapato['nombre']);

            // 2. PRECIO
            $precioNormal = floatval($zapato['precio']);
            $descuento = intval($zapato['descuento']);
            $precioFinal = $precioNormal;
            if ($descuento > 0) {
                $precioFinal = $precioNormal - ($precioNormal * ($descuento / 100));
            }

            // 3. IMÁGENES
            $stmt = $conexion->prepare("SELECT ruta, id_color FROM imagenes_zapato WHERE id_zapato = ? ORDER BY orden ASC");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $resImg = $stmt->get_result();

            $imagenesPorColor = [];
            $todasLasImagenes = [];

            while ($img = $resImg->fetch_assoc()) {
                $rutaVisual = "../../" . $img['ruta'];
                $rutaAbsoluta = "/Mayoreo_Etnia/" . $img['ruta'];

                if ($img['id_color']) {
                    $imagenesPorColor[$img['id_color']]['visual'][] = $rutaVisual;
                    $imagenesPorColor[$img['id_color']]['abs'][] = $rutaAbsoluta;
                }
                $todasLasImagenes['visual'][] = $rutaVisual;
                $todasLasImagenes['abs'][] = $rutaAbsoluta;
            }

            $imgPrincipalVisual = $todasLasImagenes['visual'][0] ?? '../../Assets/Imagenes/default.png';
            $imgPrincipalAbs = $todasLasImagenes['abs'][0] ?? '/Mayoreo_Etnia/Assets/Imagenes/default.png';

            // 4. COLORES
            $stmtC = $conexion->prepare("SELECT id_color, hex, nombre FROM colores_zapato WHERE id_zapato = ?");
            $stmtC->bind_param("i", $id);
            $stmtC->execute();
            $resCol = $stmtC->get_result();
            $coloresData = [];
            while ($c = $resCol->fetch_assoc()) {
                $coloresData[] = $c;
            }

            // 5. TALLAS
            $stmtT = $conexion->prepare("SELECT t.valor FROM zapato_talla zt JOIN tallas t ON zt.id_talla = t.id_talla WHERE zt.id_zapato = ? ORDER BY t.valor");
            $stmtT->bind_param("i", $id);
            $stmtT->execute();
            $resTal = $stmtT->get_result();
            $tallas = [];
            while ($t = $resTal->fetch_assoc()) {
                $tallas[] = floatval($t['valor']);
            }

            // JSON PARA EL MODAL DE DETALLES
            $jsonDatos = json_encode([
                'id' => $id,
                'nombre' => $zapato['nombre'],
                'categoria' => $zapato['nombre_categoria'],
                'precio' => $precioFinal,
                'imagenPortada' => $imgPrincipalVisual,
                'colores' => $coloresData,
                'tallas' => $tallas
            ], JSON_HEX_APOS | JSON_UNESCAPED_UNICODE);

            $estiloBorde = ($descuento > 0) ? 'border: 2px solid #e74c3c;' : '';

            // --- HTML LIMPIO (Copiado del Index usando $id en lugar de $uid) ---
            ?>
            <article class="container__item_01 <?= $clasePodio ?>" style="<?= $estiloBorde ?> position:relative; background:white;">
                <div class="img__item_01">
                    <figure style="position: relative;">
                        <img id="item_<?= $id ?>" src="<?= $imgPrincipalVisual ?>"
                             alt="<?= htmlspecialchars($zapato['nombre']) ?>"
                             onclick='abrirModalProducto(<?= $jsonDatos ?>)'>
                        
                        <div class="etiqueta-lugar">#<?= $zapato['orden'] ?></div>

                        <?php if ($descuento > 0): ?>
                            <div class="etiqueta-flotante-oferta">-<?= $descuento ?>%</div>
                        <?php endif; ?>

                        <?php if (count($coloresData) > 1): ?>
                            <div class="colores__item_01">
                                <?php
                                $esPrimero = true;
                                foreach ($coloresData as $color):
                                    $rutaC = $imagenesPorColor[$color['id_color']]['visual'][0] ?? $imgPrincipalVisual;
                                    $rutaJS = js_escape($rutaC);
                                    $claseActivo = $esPrimero ? 'activo' : '';
                                    ?>
                                    <button class="btn__color <?= $claseActivo ?>" data-id-color="<?= $color['id_color'] ?>"
                                            style="background-color: <?= $color['hex'] ?>;" title="<?= $color['nombre'] ?>"
                                            onclick="seleccionarColorTarjeta(this, 'item_<?= $id ?>', '<?= $rutaJS ?>', '<?= $nombreJS ?>'); event.stopPropagation();">
                                    </button>
                                    <?php
                                    $esPrimero = false;
                                endforeach;
                                ?>
                            </div>
                        <?php endif; ?>

                        <div class="paquete__item_01">
                             <button class="btn__paquete_seis activo" onclick="cambiarPrecio('precio_item_<?= $id ?>', 'seis', <?= $id ?>)">6</button>
                             <button class="btn__paquete_doce" onclick="cambiarPrecio('precio_item_<?= $id ?>', 'doce', <?= $id ?>)">12</button>
                        </div>
                    </figure>
                </div>

                <div class="info__item_01">
                    <h1><?= htmlspecialchars($zapato['nombre_categoria']) ?></h1>
                    <h2><?= htmlspecialchars($zapato['nombre']) ?></h2>
                    <p class="precio_01" id="precio_item_<?= $id ?>" data-precio-individual="<?= $precioFinal ?>"
                        data-paquete-actual="seis"
                        style="<?= ($descuento > 0) ? 'color:#e74c3c; font-weight:bold;' : '' ?>">
                        $<?= number_format($precioFinal * 6 * 0.9, 2) ?>
                        <?php if ($descuento > 0): ?>
                            <span class="badge-descuento">-<?= $descuento ?>%</span>
                        <?php endif; ?>
                    </p>
                </div>

                <div class="seleccionar__cantidad_01">
                    <div class="cantidad__control_01">
                        <button class="btn__cantidad_menos_01" onclick="controlarCantidad('input_<?= $id ?>', -1)">-</button>
                        <input id="input_<?= $id ?>" type="text" value="1" class="input__cantidad_01"
                               onkeypress="return soloNumeros(event)" onchange="validarEntrada('input_<?= $id ?>')">
                        <button class="btn__cantidad_mas_01" onclick="controlarCantidad('input_<?= $id ?>', 1)">+</button>
                    </div>

                    <button class="btn__carrito_01"
                            onclick="abrirModalTallasConDatos(<?= $id ?>, '<?= $nombreJS ?>', <?= $precioFinal ?>, '<?= $imgPrincipalAbs ?>', <?= $idCategoria ?>)">
                        <img src="../../Assets/Imagenes/Iconos/carrito-de-compras.png">
                    </button>
                </div>
            </article>
            <?php
        }
        ?>


        <section class="contenedor-podio">
            <?php
            $ordenVisualIndices = [3, 1, 0, 2, 4]; // Orden de impresión: #4, #2, #1, #3, #5
            
            foreach ($ordenVisualIndices as $idx) {
                if (isset($todosLosPopulares[$idx])) {
                    $zapato = $todosLosPopulares[$idx];
                    $ranking = $idx + 1; // 1 es el primero, 2 el segundo...
            
                    // Definimos la clase de tamaño según el ranking real
                    $claseTamano = 'podio-normal'; // Por defecto (#4 y #5)
            
                    if ($ranking == 1) {
                        $claseTamano = 'podio-grande'; // #1 Gigante
                    } elseif ($ranking == 2 || $ranking == 3) {
                        $claseTamano = 'podio-medio'; // #2 y #3 Medianos
                    }

                    renderizarTarjeta($zapato, $conexion, $claseTamano);
                }
            }
            ?>
        </section>

        <h2 style="text-align: center; margin-top: 50px; color: var(--color_cuatro); font-size: 1.2rem;">Otros favoritos
            de la colección</h2>

        <section class="contenedor-resto">
            <?php
            for ($i = 5; $i < count($todosLosPopulares); $i++) {
                renderizarTarjeta($todosLosPopulares[$i], $conexion, '');
            }
            ?>
        </section>

    </main>

    <script>
        const usuarioLogueado = <?= isset($_SESSION['id_usuario']) ? 'true' : 'false' ?>;
    </script>

    <?php include "componentes_modales.php"; ?>
    <?php include "componentes_buscador.php"; ?>

    <script src="controlador.js"></script>

</body>

</html>