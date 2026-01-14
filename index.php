<?php
session_start();
require_once "Assets/Scripts/conexion.php";

header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding('UTF-8');

function js_escape($string)
{
    return str_replace(["\r", "\n", "'", '"'], ['', '', "\\'", '\\"'], $string);
}

// 1. CONSULTA PRINCIPAL CON OFERTAS (LEFT JOIN)
$sql = "
SELECT 
    z.id_zapato,
    z.id_categoria,
    z.nombre,
    z.precio,
    c.nombre AS nombre_categoria,
    o.porcentaje AS descuento
FROM zapatos z
INNER JOIN categorias c ON z.id_categoria = c.id_categoria
LEFT JOIN ofertas o ON z.id_zapato = o.id_zapato AND o.estado = 1
ORDER BY z.id_zapato DESC";


$resultado = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tienda Emprendimiento Principal</title>
    <link rel="stylesheet" href="Assets/Scripts/estilos.css">
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
    </style>
</head>

<body>

    <header>
        <div class="container__header">
            <section class="layout">
                <div class="logo">
                    <a href="index.php"><img src="Assets/Imagenes/Logo/etnia_logo.png" alt="Logo Etnia"></a>
                </div>
                <div class="menu__tienda">
                    <nav>
                        <ul>
                            <li><a href="index.php" style="color: var(--color_uno);">HOME</a></li>
                            <li><a href="Assets/Scripts/populares.php">POPULARES</a></li>
                            <li><a href="Assets/Scripts/ofertas.php">OFERTAS</a></li>
                            <li><a href="Assets/Scripts/favoritos.php">FAVORITOS</a></li>
                            <li><a href="#" onclick="toggleBuscador(event)">
                                    <div class="icono__busqueda"><img src="Assets/Imagenes/Iconos/busqueda.png"
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
                                <li><a href="Assets/Scripts/auth.php?accion=logout"
                                        style="color:#e74c3c; font-size:11px;">(SALIR)</a></li>
                            <?php else: ?>
                                <li><a href="#" onclick="abrirModalAuth()">CUENTA</a></li>
                            <?php endif; ?>

                            <li><a href="#">
                                    <div class="icono__usuario"><img src="Assets/Imagenes/Iconos/usuario.png"
                                            alt="Usuario"></div>
                                </a></li>
                        </ul>
                    </nav>
                </div>
            </section>
        </div>
    </header>

    <main>
        <div class="container__portada div__offset" id="inicio">
            <div class="portada">
                <section class="text__portada">
                    <h1>Emprende con Etnia</h1>
                    <h2>Únete al equipo y comienza a generar ingresos</h2>
                    <p>Escoge Modelo punto y color<br>Paquetes de 6 o 12 para cumplir con la promoción<br><br>Puntos a
                        considerar:<br>- El cliente cubre gastos de envío<br>- Consultar si el modelo esta disponible.
                    </p>
                    <a href="Assets/Catalogos/EMPRENDE CON ETNIA CATALOGO.pdf"
                        download="EMPRENDE CON ETNIA CATALOGO.pdf" class="btn__catalogo">Descargar catálogo</a>
                </section>
                <section class="image__portada">
                    <img src="Assets/Imagenes/Portada/imagen_portada.jpeg" alt="Portada Tienda">
                </section>
            </div>
        </div>

        <section class="layout__productos">
            <?php while ($zapato = $resultado->fetch_assoc()):
                $id = $zapato['id_zapato'];

                $precioNormal = floatval($zapato['precio']);
                $descuento = intval($zapato['descuento']);
                $precioFinal = $precioNormal;

                if ($descuento > 0) {
                    $precioFinal = $precioNormal - ($precioNormal * ($descuento / 100));
                }

                $stmt = $conexion->prepare("SELECT ruta, id_color FROM imagenes_zapato WHERE id_zapato = ? ORDER BY orden ASC");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $resImg = $stmt->get_result();
                $imagenesPorColor = [];
                $todasLasImagenes = [];
                while ($img = $resImg->fetch_assoc()) {
                    $ruta = $img['ruta'];
                    if ($img['id_color'])
                        $imagenesPorColor[$img['id_color']][] = $ruta;
                    $todasLasImagenes[] = $ruta;
                }
                $imagenPrincipal = $todasLasImagenes[0] ?? 'Assets/Imagenes/default.png';

                $stmtC = $conexion->prepare("SELECT id_color, hex, nombre FROM colores_zapato WHERE id_zapato = ?");
                $stmtC->bind_param("i", $id);
                $stmtC->execute();
                $resCol = $stmtC->get_result();
                $coloresData = [];
                while ($c = $resCol->fetch_assoc())
                    $coloresData[] = ['id' => $c['id_color'], 'hex' => $c['hex'], 'nombre' => $c['nombre']];

                $stmtT = $conexion->prepare("SELECT t.valor FROM zapato_talla zt JOIN tallas t ON zt.id_talla = t.id_talla WHERE zt.id_zapato = ? ORDER BY t.valor ASC");
                $stmtT->bind_param("i", $id);
                $stmtT->execute();
                $resTal = $stmtT->get_result();
                $tallas = [];
                while ($t = $resTal->fetch_assoc())
                    $tallas[] = floatval($t['valor']);

                $jsonDatos = json_encode([
                    'id' => $id,
                    'id_categoria' => $zapato['id_categoria'],
                    'nombre' => $zapato['nombre'],
                    'categoria' => $zapato['nombre_categoria'],
                    'precio' => $precioFinal,
                    'imagenPortada' => $imagenPrincipal,
                    'colores' => $coloresData,
                    'imagenesPorColor' => $imagenesPorColor,
                    'tallas' => $tallas
                ], JSON_HEX_APOS | JSON_UNESCAPED_UNICODE);

                $estiloBorde = ($descuento > 0) ? "style='border: 2px solid #e74c3c;'" : "";
                ?>

                <article class="container__item_01" <?= $estiloBorde ?>>
                    <div class="img__item_01">
                        <figure>
                            <img id="item_<?= $id ?>" src="<?= htmlspecialchars($imagenPrincipal) ?>"
                                alt="<?= htmlspecialchars($zapato['nombre']) ?>"
                                onclick='abrirModalProducto(<?= $jsonDatos ?>)'>

                            <?php if ($descuento > 0): ?>
                                <div class="etiqueta-flotante-oferta">-<?= $descuento ?>%</div>
                            <?php endif; ?>

                            <?php if (count($coloresData) > 1): ?>
                                <div class="colores__item_01">
                                    <?php
                                    $esPrimero = true;
                                    foreach ($coloresData as $color):
                                        $rutaColor = $imagenesPorColor[$color['id']][0] ?? $imagenPrincipal;
                                        ?>
                                        <button class="btn__color <?= $esPrimero ? 'activo' : '' ?>"
                                            style="background-color: <?= $color['hex'] ?>;" title="<?= $color['nombre'] ?>"
                                            data-id-color="<?= $color['id'] ?>"
                                            onclick="seleccionarColorTarjeta(this, 'item_<?= $id ?>', '<?= $rutaColor ?>', '<?= js_escape($zapato['nombre']) ?>'); event.stopPropagation();">
                                        </button>
                                        <?php
                                        $esPrimero = false;
                                    endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <div class="paquete__item_01">
                                <button class="btn__paquete_seis activo"
                                    onclick="cambiarPrecio('precio_item_<?= $id ?>', 'seis', <?= $id ?>)">6</button>
                                <button class="btn__paquete_doce"
                                    onclick="cambiarPrecio('precio_item_<?= $id ?>', 'doce', <?= $id ?>)">12</button>
                            </div>
                        </figure>
                    </div>

                    <div class="info__item_01">
                        <h1><?= htmlspecialchars($zapato['nombre_categoria']) ?></h1>
                        <h2><?= htmlspecialchars($zapato['nombre']) ?></h2>

                        <p class="precio_01" id="precio_item_<?= $id ?>"
                            data-precio-individual="<?= number_format($precioFinal, 2, '.', '') ?>"
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
                            <button class="btn__cantidad_menos_01"
                                onclick="controlarCantidad('input_<?= $id ?>', -1)">-</button>
                            <input id="input_<?= $id ?>" type="text" value="1" class="input__cantidad_01"
                                onchange="validarEntrada('input_<?= $id ?>')" onkeypress="return soloNumeros(event)">
                            <button class="btn__cantidad_mas_01"
                                onclick="controlarCantidad('input_<?= $id ?>', 1)">+</button>
                        </div>
                        <button class="btn__carrito_01" onclick="abrirModalTallasConDatos(
                                <?= $id ?>, 
                                '<?= js_escape($zapato['nombre']) ?>', 
                                <?= $precioFinal ?>, 
                                '<?= $imagenPrincipal ?>',
                                <?= $zapato['id_categoria'] ?> 
                            )">
                            <img src="Assets/Imagenes/Iconos/carrito-de-compras.png" alt="Agregar">
                        </button>
                    </div>
                </article>
            <?php endwhile; ?>
        </section>
    </main>

    <script>
        const usuarioLogueado = <?= isset($_SESSION['id_usuario']) ? 'true' : 'false' ?>;
    </script>

    <?php include "Assets/Scripts/componentes_modales.php"; ?>
    <?php include "Assets/Scripts/componentes_buscador.php"; ?>

    <script src="Assets/Scripts/controlador.js"></script>

</body>

</html>