<?php
require_once '../Assets/Scripts/conexion.php';
session_start();
if(!isset($_SESSION['admin_id'])) { header('Location: index.php'); exit; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Productos - Admin</title>
    <link rel="stylesheet" href="estilos_admin.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script>
        function toggleFormulario() {
            const form = document.getElementById('form-crear');
            const lista = document.getElementById('lista-productos');
            const btn = document.getElementById('btn-toggle');
            
            if(form.style.display === 'none') {
                form.style.display = 'block';
                lista.style.display = 'none';
                btn.textContent = 'Cancelar y Volver a la Lista';
                btn.classList.add('btn-danger');
            } else {
                form.style.display = 'none';
                lista.style.display = 'block';
                btn.textContent = '+ Agregar Nuevo Producto';
                btn.classList.remove('btn-danger');
            }
        }

        // Función corregida para deshabilitar campos ocultos y evitar errores de validación
        function toggleTipoColor(esVarios) {
            const secUnico = document.getElementById('sec-unico');
            const secVarios = document.getElementById('sec-varios');
            
            secUnico.style.display = esVarios ? 'none' : 'block';
            secVarios.style.display = esVarios ? 'block' : 'none';
            
            const inputsUnico = secUnico.querySelectorAll('input');
            inputsUnico.forEach(input => input.disabled = esVarios);

            const inputsVarios = secVarios.querySelectorAll('input, select');
            inputsVarios.forEach(input => input.disabled = !esVarios);
        }

        function agregarFilaColor() {
            const container = document.getElementById('container-colores');
            const div = document.createElement('div');
            div.className = 'fila-color';
            div.style = "display: flex; gap: 10px; margin-bottom: 10px; border: 1px dashed #ccc; padding: 10px; align-items: center;";
            
            div.innerHTML = `
                <div style="flex: 1;">
                    <input type="text" name="nombres_colores[]" placeholder="Nombre (Ej: Miel)" required style="width: 100%; margin-bottom: 5px;">
                    <div style="display: flex; align-items: center; gap: 5px;">
                        <label style="font-size: 0.8em;">Hex:</label>
                        <input type="color" name="hex_colores[]" value="#000000" style="width: 50px; cursor: pointer;">
                    </div>
                </div>
                
                <div style="flex: 2;">
                    <label style="font-size: 0.8em; display:block;">Foto del color:</label>
                    <input type="file" name="imagenes_colores[]" accept="image/*" required style="width: 100%;">
                </div>
                
                <button type="button" class="btn-danger" style="width: auto; padding: 10px;" onclick="this.parentElement.remove()">X</button>
            `;
            container.appendChild(div);

            const esVarios = document.querySelector('input[name="tipo_color"][value="varios"]').checked;
            if (!esVarios) {
                const nuevosInputs = div.querySelectorAll('input');
                nuevosInputs.forEach(inpt => inpt.disabled = true);
            }
        }
    </script>
</head>
<body>
    <div class="sidebar">
        <h2>Mayoreo Etnia</h2>
        <a href="panel.php"><i class='bx bxs-dashboard'></i> Inicio</a>
        <a href="populares.php"><i class='bx bxs-star'></i> Populares</a>
        <a href="productos.php" class="activo"><i class='bx bxs-shopping-bag'></i> Productos</a>
        <a href="ofertas.php"><i class='bx bxs-offer'></i> Ofertas</a>
    </div>

    <div class="contenido">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h1>Gestión de Productos</h1>
            <button id="btn-toggle" onclick="toggleFormulario()" class="btn btn-primary" style="width: auto;">+ Agregar Nuevo Producto</button>
        </div>

        <div id="lista-productos">
            <?php
            $prodSQL = "SELECT z.id_zapato, z.nombre, z.precio, 
                        (SELECT ruta FROM imagenes_zapato WHERE id_zapato = z.id_zapato LIMIT 1) as imagen 
                        FROM zapatos z ORDER BY z.id_zapato DESC";
            $resProd = $conexion->query($prodSQL);
            
            if($resProd && $resProd->num_rows > 0): ?>
                <table style="width: 100%; border-collapse: collapse; background: white;">
                    <thead>
                        <tr style="background: #222; color: white;">
                            <th style="padding: 10px;">Img</th>
                            <th>Nombre</th>
                            <th>Precio (Par)</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while($p = $resProd->fetch_assoc()): 
                        $img = $p['imagen'] ? '../' . $p['imagen'] : '../Assets/Imagenes/default.png';
                    ?>
                        <tr style="border-bottom: 1px solid #ddd; text-align: center;">
                            <td style="padding: 5px;"><img src="<?= $img ?>" style="height: 50px; object-fit: contain;"></td>
                            <td><?= $p['nombre'] ?></td>
                            <td>$<?= number_format($p['precio'], 2) ?></td>
                            <td style="padding: 10px;">
                                <form action="backend.php" method="POST" onsubmit="return confirm('¿Borrar?');">
                                    <input type="hidden" name="accion" value="borrar_producto">
                                    <input type="hidden" name="id_zapato" value="<?= $p['id_zapato'] ?>">
                                    <button class="btn-danger" style="padding: 5px 10px; font-size: 0.8em;">Borrar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No hay productos registrados.</p>
            <?php endif; ?>
        </div>

        <div id="form-crear" class="card" style="display: none;">
            <h3>Nuevo Producto</h3>
            <form action="backend.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="accion" value="crear_producto">
                
                <label>Nombre del Modelo:</label>
                <input type="text" name="nombre" required placeholder="Ej: Botín Chelsea">

                <div style="display: flex; gap: 20px;">
                    <div style="flex: 1;">
                        <label>Precio Individual (Por Par):</label>
                        <input type="number" step="0.01" name="precio" required placeholder="Ej: 200.00">
                    </div>
                    <div style="flex: 1;">
                        <label>Categoría:</label>
                        <select name="categoria" required>
                            <option value="1">Casual</option>
                            <option value="2">Balerina</option>
                            <option value="3">Botines</option>
                            <option value="4">Guante</option>
                            <option value="5">Confort Sandalia</option>
                            <option value="6">Sandalia</option>
                            <option value="7">Sandalia de Tacón</option>
                            <option value="8">Tacón Cerrado</option>
                        </select>
                    </div>
                </div>

                <div style="margin: 20px 0; padding: 15px; background: #f9f9f9; border-radius: 4px;">
                    <label style="font-weight: bold;">Variantes:</label>
                    <div style="margin-top: 5px;">
                        <label style="margin-right: 20px;">
                            <input type="radio" name="tipo_color" value="unico" checked onclick="toggleTipoColor(false)"> 
                            Un solo color (Estándar)
                        </label>
                        <label>
                            <input type="radio" name="tipo_color" value="varios" onclick="toggleTipoColor(true)"> 
                            Varios Colores
                        </label>
                    </div>
                </div>

                <div id="sec-unico">
                    <label>Imagen Principal:</label>
                    <input type="file" name="imagen_unica" id="input-img-unica" accept="image/*">
                </div>

                <div id="sec-varios" style="display: none;">
                    <p>Sube una foto por cada color disponible:</p>
                    <div id="container-colores"></div>
                    <button type="button" class="btn" style="background: #555; margin-top: 10px;" onclick="agregarFilaColor()">+ Agregar Color</button>
                </div>

                <hr style="margin: 20px 0;">
                <button type="submit" class="btn btn-primary">GUARDAR PRODUCTO</button>
            </form>
        </div>
    </div>
    
    <script>
        agregarFilaColor();
    </script>
</body>
</html>