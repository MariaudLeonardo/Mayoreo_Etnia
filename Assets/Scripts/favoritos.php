<?php
session_start();
require_once "conexion.php";

// VERIFICAR SI HAY SESIÓN
$idUsuario = isset($_SESSION['id_usuario']) ? $_SESSION['id_usuario'] : null;
$resultado = null;

if ($idUsuario) {
    // CONSULTA: Traer zapatos que estén en la tabla FAVORITOS de este usuario
    // Usamos LEFT JOIN en ofertas para mostrar el precio correcto si tiene descuento
    $sql = "
    SELECT 
        z.id_zapato, z.nombre, z.precio, 
        c.nombre AS nombre_categoria, 
        z.id_categoria,
        o.porcentaje AS descuento,
        f.fecha_agregado
    FROM favoritos f
    INNER JOIN zapatos z ON f.id_zapato = z.id_zapato
    INNER JOIN categorias c ON z.id_categoria = c.id_categoria
    LEFT JOIN ofertas o ON z.id_zapato = o.id_zapato AND o.estado = 1
    WHERE f.id_usuario = $idUsuario
    ORDER BY f.fecha_agregado DESC
    "; 
    $resultado = $conexion->query($sql);
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Favoritos - Etnia</title>
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

        /* Estilo para mensaje de vacío */
        .mensaje-vacio {
            text-align: center;
            padding: 50px 20px;
            color: #666;
        }

        .btn-ir-tienda {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: var(--color_uno);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
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
                            <li><a href="favoritos.php" style="color: var(--color_uno); font-weight:bold;">FAVORITOS</a>
                            </li>
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

                            <?php if ($idUsuario): ?>
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
                <h1 style="color: var(--color_uno); font-size: 2.5em;">★ Mis Favoritos ★</h1>
            </div>
        </div>

        <section class="layout__productos">
            <?php
            // CASO 1: NO LOGUEADO
            if (!$idUsuario) {
                echo "
                <div class='mensaje-vacio' style='width: 100%;'>
                    <h2>Necesitas iniciar sesión</h2>
                    <p>Ingresa a tu cuenta para ver tus productos guardados.</p>
                    <button onclick='abrirModalAuth()' class='btn-buscar-real' style='background:var(--color_uno); color:white; width:auto; padding:10px 30px;'>Iniciar Sesión</button>
                </div>";
            }
            // CASO 2: LOGUEADO PERO SIN FAVORITOS
            elseif ($resultado->num_rows === 0) {
                echo "
                <div class='mensaje-vacio' style='width: 100%;'>
                    <h2>Aún no tienes favoritos</h2>
                    <p>Agrega productos haciendo clic en la estrella de los detalles.</p>
                    <a href='populares.php' class='btn-ir-tienda'>Ver Populares</a>
                </div>";
            }
            // CASO 3: MOSTRAR PRODUCTOS
            else {
                while ($zapato = $resultado->fetch_assoc()) {
                    renderizarTarjetaFavorito($zapato, $conexion);
                }
            }

            // FUNCIÓN DE RENDERIZADO (Reutilizamos la lógica visual)
            function renderizarTarjetaFavorito($zapato, $conexion)
            {
                $id = $zapato['id_zapato'];

                // CÁLCULO PRECIO (Considerando Oferta)
                $precioNormal = floatval($zapato['precio']);
                $descuento = intval($zapato['descuento']); // Puede ser NULL o 0
                $precioFinal = $precioNormal;

                if ($descuento > 0) {
                    $precioFinal = $precioNormal - ($precioNormal * ($descuento / 100));
                }

                // IMÁGENES
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

                // COLORES
                $stmtC = $conexion->prepare("SELECT id_color, hex, nombre FROM colores_zapato WHERE id_zapato = ?");
                $stmtC->bind_param("i", $id);
                $stmtC->execute();
                $resCol = $stmtC->get_result();
                $coloresData = [];
                while ($c = $resCol->fetch_assoc())
                    $coloresData[] = ['id' => $c['id_color'], 'hex' => $c['hex'], 'nombre' => $c['nombre']];

                // TALLAS
                $stmtT = $conexion->prepare("SELECT t.valor FROM zapato_talla zt JOIN tallas t ON zt.id_talla = t.id_talla WHERE zt.id_zapato = ? ORDER BY t.valor");
                $stmtT->bind_param("i", $id);
                $stmtT->execute();
                $resTal = $stmtT->get_result();
                $tallas = [];
                while ($t = $resTal->fetch_assoc())
                    $tallas[] = floatval($t['valor']);

                // JSON
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

                // Estilo borde oferta
                $estiloBorde = ($descuento > 0) ? "style='border: 2px solid #e74c3c;'" : "";
                $uid = $id . '_fav';

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