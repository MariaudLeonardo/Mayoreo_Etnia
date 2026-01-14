<?php
session_start();
require_once "conexion.php";

// RECOGER PARÁMETROS DE BÚSQUEDA
$queryTexto = isset($_GET['q']) ? $conexion->real_escape_string($_GET['q']) : '';
$categoriasSeleccionadas = isset($_GET['cats']) ? $_GET['cats'] : [];

// CONSTRUIR CONSULTA SQL DINÁMICA
$sql = "
SELECT 
    z.id_zapato, z.nombre, z.precio, c.nombre AS nombre_categoria,
    o.porcentaje AS descuento
FROM zapatos z
INNER JOIN categorias c ON z.id_categoria = c.id_categoria
LEFT JOIN ofertas o ON z.id_zapato = o.id_zapato AND o.estado = 1
WHERE 1=1 
";

if (!empty($queryTexto)) {
    $sql .= " AND z.nombre LIKE '%$queryTexto%' ";
}

if (!empty($categoriasSeleccionadas)) {
    $idsCats = implode(",", array_map('intval', $categoriasSeleccionadas));
    $sql .= " AND z.id_categoria IN ($idsCats) ";
}

$sql .= " ORDER BY z.id_zapato DESC";

$resultado = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados de Búsqueda - Etnia</title>
    <link rel="stylesheet" href="estilos.css">
    <link rel="stylesheet" href="https://www.flaticon.es/icon-fonts-mas-descargados?weight=bold&type=uicon.css">
    <style>
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
            z-index: 2;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .img__item_01 figure {
            position: relative;
        }
    </style>
</head>

<body>

    <header>
        <div class="container__header">
            <section class="layout">
                <div class="logo">
                    <a href="../../index.php"><img src="../Imagenes/Logo/etnia_logo.png" alt="Logo"></a>
                </div>
                <div class="menu__tienda">
                    <nav>
                        <ul>
                            <li><a href="../../index.php">HOME</a></li>
                            <li><a href="populares.php">POPULARES</a></li>
                            <li><a href="ofertas.php">OFERTAS</a></li>
                            <li><a href="favoritos.php">FAVORITOS</a></li>

                            <li><a href="#" onclick="toggleBuscador(event)">
                                    <div class="icono__busqueda">
                                        <img src="../Imagenes/Iconos/busqueda.png" alt="Buscar"
                                            style="transform: scale(1.4); filter: drop-shadow(0 0 2px rgba(0,0,0,0.2)); transition: 0.3s;">
                                    </div>
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
            style="height: auto; min-height: auto; background: none; margin-bottom: 20px;">
            <div class="portada" style="display: block; text-align: center; padding-top: 20px;">
                <h1 style="color: var(--color_uno);">Resultados de Búsqueda</h1>
                <p style="color: #666; margin-top: 10px;">
                    <?php
                    if ($resultado->num_rows > 0)
                        echo "Encontramos " . $resultado->num_rows . " producto(s) para tu búsqueda.";
                    else
                        echo "No encontramos coincidencias con esos filtros.";
                    ?>
                </p>
            </div>
        </div>

        <section class="layout__productos">
            <?php
            function renderizarTarjetaBusqueda($zapato, $conexion)
            {
                $id = $zapato['id_zapato'];

                $precioNormal = floatval($zapato['precio']);
                $descuento = intval($zapato['descuento']);
                $precioFinal = $precioNormal;
                if ($descuento > 0)
                    $precioFinal = $precioNormal - ($precioNormal * ($descuento / 100));

                $stmt = $conexion->prepare("SELECT ruta, id_color FROM imagenes_zapato WHERE id_zapato = ? ORDER BY orden ASC");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $resImg = $stmt->get_result();
                $imagenesPorColor = [];
                $todasLasImagenes = [];
                while ($img = $resImg->fetch_assoc()) {
                    $ruta = "../../" . $img['ruta'];
                    if ($img['id_color'])
                        $imagenesPorColor[$img['id_color']][] = $ruta;
                    $todasLasImagenes[] = $ruta;
                }
                $imgPrincipal = $todasLasImagenes[0] ?? '../../Assets/Imagenes/default.png';

                $stmtC = $conexion->prepare("SELECT id_color, hex, nombre FROM colores_zapato WHERE id_zapato = ?");
                $stmtC->bind_param("i", $id);
                $stmtC->execute();
                $resCol = $stmtC->get_result();
                $coloresData = [];
                while ($c = $resCol->fetch_assoc())
                    $coloresData[] = ['id' => $c['id_color'], 'hex' => $c['hex'], 'nombre' => $c['nombre']];

                $stmtT = $conexion->prepare("SELECT t.valor FROM zapato_talla zt JOIN tallas t ON zt.id_talla = t.id_talla WHERE zt.id_zapato = ? ORDER BY t.valor");
                $stmtT->bind_param("i", $id);
                $stmtT->execute();
                $resTal = $stmtT->get_result();
                $tallas = [];
                while ($t = $resTal->fetch_assoc())
                    $tallas[] = floatval($t['valor']);

                $jsonDatos = json_encode([
                    'id' => $id,
                    'nombre' => $zapato['nombre'],
                    'categoria' => $zapato['nombre_categoria'],
                    'precio' => $precioFinal,
                    'imagenPortada' => $imgPrincipal,
                    'colores' => $coloresData,
                    'imagenesPorColor' => $imagenesPorColor,
                    'tallas' => $tallas
                ], JSON_HEX_APOS | JSON_UNESCAPED_UNICODE);

                $estiloBorde = ($descuento > 0) ? "style='border: 2px solid #e74c3c;'" : "";
                $uid = $id . '_search';

                echo "
                <article class='container__item_01' {$estiloBorde}>
                    <div class='img__item_01'>
                        <figure style='position:relative;'>
                            <img id='item_{$uid}' src='{$imgPrincipal}' alt='{$zapato['nombre']}' onclick='abrirModalProducto({$jsonDatos})'>
                            ";
                if ($descuento > 0)
                    echo "<div class='etiqueta-flotante-oferta'>-{$descuento}%</div>";

                if (count($coloresData) > 1) {
                    echo "<div class='colores__item_01'>";
                    foreach ($coloresData as $color) {
                        $rutaColor = $imagenesPorColor[$color['id']][0] ?? $imgPrincipal;
                        $rutaJS = str_replace("'", "\\'", $rutaColor);
                        echo "<button class='btn__color' style='background-color: {$color['hex']};' title='{$color['nombre']}' onclick=\"establecerImagen('item_{$uid}', '{$rutaJS}', 'Color'); event.stopPropagation();\"></button>";
                    }
                    echo "</div>";
                }
                echo "
                            <div class='paquete__item_01'>
                                 <button class='btn__paquete_seis activo' onclick=\"cambiarPrecio('precio_item_{$uid}', 'seis', '{$uid}')\">6</button>
                                 <button class='btn__paquete_doce' onclick=\"cambiarPrecio('precio_item_{$uid}', 'doce', '{$uid}')\">12</button>
                            </div>
                        </figure>
                    </div>
                    <div class='info__item_01'>
                        <h1>{$zapato['nombre_categoria']}</h1>
                        <h2>{$zapato['nombre']}</h2>
                        <p class='precio_01' id='precio_item_{$uid}' 
                            data-precio-individual='{$precioFinal}' data-paquete-actual='seis'
                            style='" . ($descuento > 0 ? "color:#e74c3c; font-weight:bold;" : "") . "'>
                            $" . number_format($precioFinal * 6 * 0.9, 2) . "
                            " . ($descuento > 0 ? "<span class='badge-descuento'>-{$descuento}%</span>" : "") . "
                        </p>
                    </div>
                    <div class='seleccionar__cantidad_01'>
                        <div class='cantidad__control_01'>
                            <button class='btn__cantidad_menos_01' onclick=\"controlarCantidad('input_{$uid}', -1)\">-</button>
                            <input id='input_{$uid}' type='text' value='1' class='input__cantidad_01' onkeypress='return soloNumeros(event)' onchange=\"validarEntrada('input_{$uid}')\">
                            <button class='btn__cantidad_mas_01' onclick=\"controlarCantidad('input_{$uid}', 1)\">+</button>
                        </div>
                         <button class='btn__carrito_01' onclick='abrirModalTallas(\"{$uid}\")'>
                            <img src='../../Assets/Imagenes/Iconos/carrito-de-compras.png'> 
                         </button>
                    </div>
                </article>
                ";
            }

            while ($row = $resultado->fetch_assoc()) {
                renderizarTarjetaBusqueda($row, $conexion);
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