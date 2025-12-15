<!-- PHP CONEXION -->
<?php
require_once "Assets/Scripts/conexion.php";

// Configuración de codificación
header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding('UTF-8');

// Función para escapar strings para JavaScript
function js_escape($string)
{
    return str_replace(["\r", "\n", "'", '"'], ['', '', "\\'", '\\"'], $string);
}

$sql = "
SELECT 
    z.id_zapato,
    z.nombre,
    z.precio,
    c.nombre AS nombre_categoria,
    i.ruta AS ruta_imagen
FROM zapatos z
INNER JOIN categorias c
    ON z.id_categoria = c.id_categoria
LEFT JOIN imagenes_zapato i
    ON z.id_zapato = i.id_zapato
    AND i.orden = 1
";

$resultado = $conexion->query($sql);

if (!$resultado) {
    die("Error en la consulta SQL: " . $conexion->error);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tienda Emprendimiento Principal</title>
    <link rel="stylesheet" href="Assets\Scripts\estilos.css">
    <link rel="stylesheet" href="https://www.flaticon.es/icon-fonts-mas-descargados?weight=bold&type=uicon.css">
</head>

<body>
    <script src="Assets\Scripts\controlador.js"></script>

    <header>
        <div class="container__header">
            <section class="layout">
                <div class="logo">
                    <a href="#">
                        <img src="Assets/Imagenes/Logo/etnia_logo.png" alt="Logo Etnia">
                    </a>
                </div>

                <div class="menu__tienda">
                    <nav>
                        <ul>
                            <li><a href="#">HOME</a></li>
                            <li><a href="#">POPULARES</a></li>
                            <li><a href="#">OFERTAS</a></li>
                            <li><a href="#">FAVORITOS</a></li>
                            <li><a href="#">
                                    <div class="icono__busqueda">
                                        <img src="Assets\Imagenes\Iconos\busqueda.png" alt="Buscar">
                                    </div>
                                </a></li>
                        </ul>
                    </nav>
                </div>

                <div class="menu__usuario">
                    <nav>
                        <ul>
                            <li><a href="#" id="btn-abrir-carrito">CARRITO</a></li>
                            <li><a href="#">CUENTA</a></li>
                            <li><a href="#">
                                    <div class="icono__usuario">
                                        <img src="Assets\Imagenes\Iconos\usuario.png" alt="Usuario">
                                    </div>
                                </a></li>
                        </ul>
                    </nav>
                </div>
            </section>
        </div>
    </header>

    <main>
        <!-- INICIO - Portada de inicio -->
        <div class="container__portada div__offset" id="inicio">
            <div class="portada">
                <section class="text__portada">
                    <h1>Emprende con Etnia</h1>
                    <h2>Únete al equipo y comienza a generar ingresos</h2>
                    <p>
                        Escoge Modelo punto y color<br>
                        Paquetes de 6 o 12 para cumplir con la promoción<br><br>
                        Puntos a considerar:<br>
                        - El cliente cubre gastos de envío<br>
                        - Consultar si el modelo esta disponible.
                    </p>
                    <a href="Assets\Catalogos\EMPRENDE CON ETNIA CATALOGO.pdf"
                        download="EMPRENDE CON ETNIA CATALOGO.pdf" class="btn__catalogo">
                        Descargar catálogo
                    </a>
                </section>

                <section class="image__portada">
                    <img src="Assets\Imagenes\Portada\imagen_portada.jpeg" alt="Portada Tienda">
                </section>
            </div>
        </div>

        <!-- Productos -->
        <section class="layout__productos">
            <?php while ($zapato = $resultado->fetch_assoc()):
                $id = $zapato['id_zapato'];
                $nombreEscapado = js_escape($zapato['nombre']);
                $categoriaEscapada = js_escape($zapato['nombre_categoria']);

                // Consulta de imágenes del zapato actual
                $sqlImagenes = "
                SELECT ruta
                FROM imagenes_zapato
                WHERE id_zapato = ?
                ORDER BY orden ASC
                ";

                $stmt = $conexion->prepare($sqlImagenes);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $resultadoImagenes = $stmt->get_result();

                $imagenes = [];
                while ($img = $resultadoImagenes->fetch_assoc()) {
                    $imagenes[] = $img['ruta'];
                }

                $imagenPrincipal = $imagenes[0] ?? 'Assets/Imagenes/default.png';
                $imagenPrincipalEscapada = js_escape($imagenPrincipal);

                // Consulta de colores del zapato actual
                $sqlColores = "
                SELECT hex
                FROM colores_zapato
                WHERE id_zapato = ?
                ORDER BY id_color
                ";

                $stmtColores = $conexion->prepare($sqlColores);
                $stmtColores->bind_param("i", $id);
                $stmtColores->execute();
                $resultadoColores = $stmtColores->get_result();

                $colores = [];
                while ($color = $resultadoColores->fetch_assoc()) {
                    $colores[] = $color['hex'];
                }

                $sqlTallas = "
                SELECT t.valor 
                FROM zapato_talla zt
                INNER JOIN tallas t ON zt.id_talla = t.id_talla
                WHERE zt.id_zapato = ?
                ORDER BY t.valor ASC
                ";

                $stmtTallas = $conexion->prepare($sqlTallas);
                $stmtTallas->bind_param("i", $id);
                $stmtTallas->execute();
                $resultadoTallas = $stmtTallas->get_result();

                $tallas = [];
                while ($talla = $resultadoTallas->fetch_assoc()) {
                    $tallas[] = floatval($talla['valor']); // Convertir a número
                }

                // Preparar arrays para JavaScript
                $coloresJSON = json_encode($colores, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
                $imagenesJSON = json_encode($imagenes, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
                $tallasJSON = json_encode($tallas, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
                ?>

                <article class="container__item_01">
                    <div class="img__item_01">
                        <figure>
                            <img id="item_<?= $id ?>" src="<?= htmlspecialchars($imagenPrincipal) ?>"
                                alt="<?= htmlspecialchars($zapato['nombre']) ?>" onclick='abrirModalProducto(<?=
                                      str_replace("'", "\'", json_encode([
                                          'id' => $id,
                                          'nombre' => $zapato['nombre'],
                                          'categoria' => $zapato['nombre_categoria'],
                                          'precio' => floatval($zapato['precio']),
                                          'imagen' => $imagenPrincipal,
                                          'colores' => $colores,
                                          'imagenes' => $imagenes,
                                          'tallas' => $tallas  // ✅ TALLAS DESDE BD
                                      ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE))
                                      ?>)' </figure>

                            <!-- COLORES DINÁMICOS -->
                            <?php if (count($imagenes) > 0 && count($colores) > 0): ?>
                                <section class="colores__item_01">
                                    <?php foreach ($colores as $index => $colorHex):
                                        $rutaImagen = $imagenes[$index] ?? $imagenPrincipal;
                                        $rutaImagenEscapada = js_escape($rutaImagen);
                                        ?>
                                        <button class="btn__color" style="background-color: <?= htmlspecialchars($colorHex) ?>;"
                                            onclick="establecerImagen(
                                                'item_<?= $id ?>',
                                                '<?= $rutaImagenEscapada ?>',
                                                '<?= $nombreEscapado ?>'
                                            )" title="Color <?= $index + 1 ?>">
                                        </button>
                                    <?php endforeach; ?>
                                </section>
                            <?php endif; ?>

                            <!-- PAQUETES -->
                            <section class="paquete__item_01">
                                <button class="btn__paquete_seis"
                                    onclick="cambiarPrecio('precio_item_<?= $id ?>', 'seis', <?= $id ?>)">
                                    6
                                </button>
                                <button class="btn__paquete_doce"
                                    onclick="cambiarPrecio('precio_item_<?= $id ?>', 'doce', <?= $id ?>)">
                                    12
                                </button>
                            </section>
                    </div>

                    <section class="info__item_01">
                        <h1><?= htmlspecialchars($zapato['nombre_categoria']) ?></h1>
                        <h2><?= htmlspecialchars($zapato['nombre']) ?></h2>
                        <p class="precio_01" id="precio_item_<?= $id ?>"
                            data-precio-individual="<?= number_format($zapato['precio'], 2, '.', '') ?>"
                            data-paquete-actual="seis"
                            data-precio-paquete="<?= number_format($zapato['precio'] * 6 * 0.9, 2, '.', '') ?>">
                            $<?= number_format($zapato['precio'] * 6 * 0.9, 2) ?>
                        </p>
                    </section>

                    <div class="seleccionar__cantidad_01">
                        <div class="cantidad__control_01">
                            <button class="btn__cantidad_menos_01" onclick="controlarCantidad('input_<?= $id ?>', -1)">
                                -
                            </button>
                            <input id="input_<?= $id ?>" type="text" value="1" class="input__cantidad_01"
                                onchange="validarEntrada('input_<?= $id ?>')" onkeypress="return soloNumeros(event)">
                            <button class="btn__cantidad_mas_01" onclick="controlarCantidad('input_<?= $id ?>', 1)">
                                +
                            </button>
                        </div>
                        <button class="btn__carrito_01" onclick="abrirModalTallas(<?= $id ?>)">
                            <img src="Assets/Imagenes/Iconos/carrito-de-compras.png" alt="Agregar al carrito">
                        </button>
                    </div>
                </article>
            <?php endwhile; ?>
        </section>
    </main>

    <!-- Modal de Selección de Tallas -->
    <div id="modal-tallas" class="modal-tallas-overlay">
        <div class="modal-tallas-content">
            <span class="btn-cerrar-modal" onclick="cerrarModalTallas()">&times;</span>
            <h3>Selecciona tu talla</h3>
            <div id="grid-tallas" class="grid-tallas"></div>
        </div>
    </div>

    <!-- Modal de Producto Detallado -->
    <div id="modalProducto" class="modal__overlay">
        <div class="modal__contenido">
            <button class="modal__cerrar" onclick="cerrarModalProducto()">&times;</button>

            <div class="modal__grid">
                <!-- Sección de imágenes -->
                <div class="modal__imagen">
                    <img id="modalImagenPrincipal" src="" alt="" class="imagen__principal">

                    <div class="miniaturas" id="modalMiniaturas">
                        <!-- Miniaturas se llenarán con JavaScript -->
                    </div>
                </div>

                <!-- Sección de información -->
                <div class="modal__info">
                    <h2 id="modalCategoria"></h2>
                    <h1 id="modalNombre"></h1>
                    <p class="modal__precio" id="modalPrecio"></p>

                    <!-- Colores -->
                    <div class="modal__colores" id="modalColores">
                        <!-- Botones de colores se llenarán con JavaScript -->
                    </div>

                    <!-- Tallas -->
                    <div class="modal__tallas" id="modalTallas">
                        <h3>Selecciona tu talla:</h3>
                        <!-- Botones de tallas se llenarán con JavaScript -->
                    </div>

                    <!-- Cantidad -->
                    <div class="modal__cantidad_container">
                        <h3>Cantidad:</h3>
                        <div class="cantidad__control_01" style="margin: 10px 0;">
                            <button class="btn__cantidad_menos_01" onclick="modalControlarCantidad(-1)">-</button>
                            <input id="modalCantidad" type="text" value="1" class="input__cantidad_01"
                                onchange="modalValidarEntrada()" onkeypress="return soloNumeros(event)">
                            <button class="btn__cantidad_mas_01" onclick="modalControlarCantidad(1)">+</button>
                        </div>
                    </div>

                    <!-- Paquetes -->
                    <!-- Paquetes -->
                    <div class="modal__paquetes">
                        <h3>Pares:</h3>
                        <div style="display: flex; gap: 15px; margin: 10px 0;">
                            <button class="btn__paquete_seis" onclick="modalCambiarPaquete('seis')">6</button>
                            <button class="btn__paquete_doce" onclick="modalCambiarPaquete('doce')">12</button>
                        </div>
                    </div>

                    <!-- Botón de agregar al carrito -->
                    <button class="btn__agregar_modal" onclick="modalAgregarAlCarrito()">
                        AGREGAR AL CARRITO
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Carrito Lateral -->
    <div class="carrito-overlay" id="carrito-overlay"></div>
    <div class="carrito-lateral" id="carrito-lateral">
        <div class="carrito-header">
            <h3>Tu Carrito</h3>
            <button class="btn-cerrar-carrito" id="btn-cerrar-carrito">&times;</button>
        </div>
        <div class="carrito-body" id="carrito-contenido">
            <p style="text-align: center; margin-top: 20px;">Tu carrito está vacío por ahora.</p>
        </div>
        <div class="carrito-footer">
            <div class="carrito-subtotal">
                <span>SUBTOTAL:</span>
                <span id="carrito-precio-final">$0.00</span>
            </div>
            <button class="btn-hacer-pedido">HACER PEDIDO</button>
        </div>
    </div>
</body>

</html>