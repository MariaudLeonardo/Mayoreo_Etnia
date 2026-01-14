<?php
require_once '../Assets/Scripts/conexion.php';
session_start();
if(!isset($_SESSION['admin_id'])) { header('Location: index.php'); exit; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ofertas - Admin</title>
    <link rel="stylesheet" href="estilos_admin.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        /* Estilos extra para las etiquetas de estado */
        .badge { padding: 5px 10px; border-radius: 15px; font-size: 0.8em; font-weight: bold; }
        .badge-active { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .badge-inactive { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        .precio-tachado { text-decoration: line-through; color: #999; font-size: 0.9em; margin-right: 5px; }
        .precio-oferta { color: #e74c3c; font-weight: bold; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Mayoreo Etnia</h2>
        <a href="panel.php"><i class='bx bxs-dashboard'></i> Inicio</a>
        <a href="populares.php"><i class='bx bxs-star'></i> Populares</a>
        <a href="productos.php"><i class='bx bxs-shopping-bag'></i> Productos</a>
        <a href="ofertas.php" class="activo"><i class='bx bxs-offer'></i> Ofertas</a>
    </div>

    <div class="contenido">
        <h1>Gestión de Ofertas</h1>
        
        <div class="card">
            <h3>+ Crear Nueva Oferta</h3>
            <form action="backend.php" method="POST" style="display: flex; gap: 15px; align-items: flex-end;">
                <input type="hidden" name="accion" value="crear_oferta">
                
                <div style="flex: 2;">
                    <label>Selecciona el Zapato:</label>
                    <select name="id_zapato" required>
                        <option value="">-- Elige un modelo --</option>
                        <?php
                        // Traemos zapatos que NO tengan oferta activa actualmente (opcional, o todos)
                        // Para simplificar traemos todos ordenados alfabéticamente
                        $sqlZ = "SELECT id_zapato, nombre, precio FROM zapatos ORDER BY nombre ASC";
                        $resZ = $conexion->query($sqlZ);
                        while($z = $resZ->fetch_assoc()): 
                        ?>
                            <option value="<?= $z['id_zapato'] ?>">
                                <?= $z['nombre'] ?> (Precio Base: $<?= $z['precio'] ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div style="flex: 1;">
                    <label>Porcentaje (%):</label>
                    <input type="number" name="porcentaje" min="1" max="99" placeholder="Ej: 15" required>
                </div>

                <button type="submit" class="btn btn-primary" style="height: 42px; margin-bottom: 10px;">AGREGAR OFERTA</button>
            </form>
        </div>

        <div class="card">
            <h3>Ofertas Registradas</h3>
            <?php
            // Consulta con JOIN para traer datos del zapato y su primera imagen
            $sqlO = "SELECT o.*, z.nombre, z.precio, 
                     (SELECT ruta FROM imagenes_zapato WHERE id_zapato = z.id_zapato ORDER BY orden ASC LIMIT 1) as imagen
                     FROM ofertas o
                     INNER JOIN zapatos z ON o.id_zapato = z.id_zapato
                     ORDER BY o.estado DESC, o.id_oferta DESC";
            $resO = $conexion->query($sqlO);
            
            if($resO->num_rows > 0): 
            ?>
            <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                <thead style="background: #333; color: white;">
                    <tr>
                        <th style="padding: 10px;">Foto</th>
                        <th>Producto</th>
                        <th>Descuento</th>
                        <th>Cálculo (Par)</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($oferta = $resO->fetch_assoc()): 
                        $precioOriginal = floatval($oferta['precio']);
                        $descuento = intval($oferta['porcentaje']);
                        $precioFinal = $precioOriginal - ($precioOriginal * ($descuento / 100));
                        $img = $oferta['imagen'] ? '../' . $oferta['imagen'] : '../Assets/Imagenes/default.png';
                        $estado = $oferta['estado'] == 1;
                    ?>
                    <tr style="border-bottom: 1px solid #eee; text-align: center;">
                        <td style="padding: 10px;">
                            <img src="<?= $img ?>" style="width: 50px; height: 50px; object-fit: contain;">
                        </td>
                        <td><?= $oferta['nombre'] ?></td>
                        <td style="font-weight: bold; color: #e74c3c;">-<?= $descuento ?>%</td>
                        <td>
                            <span class="precio-tachado">$<?= number_format($precioOriginal, 2) ?></span>
                            <br>
                            <span class="precio-oferta">$<?= number_format($precioFinal, 2) ?></span>
                        </td>
                        <td>
                            <?php if($estado): ?>
                                <span class="badge badge-active">ACTIVA</span>
                            <?php else: ?>
                                <span class="badge badge-inactive">INACTIVA</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 10px;">
                            <div style="display: flex; gap: 5px; justify-content: center;">
                                <form action="backend.php" method="POST">
                                    <input type="hidden" name="accion" value="toggle_oferta">
                                    <input type="hidden" name="id_oferta" value="<?= $oferta['id_oferta'] ?>">
                                    <input type="hidden" name="nuevo_estado" value="<?= $estado ? 0 : 1 ?>">
                                    <button type="submit" class="btn" style="background: <?= $estado ? '#f39c12' : '#27ae60' ?>; padding: 5px 10px; font-size: 0.8em;" title="<?= $estado ? 'Pausar' : 'Activar' ?>">
                                        <i class='bx bx-power-off'></i>
                                    </button>
                                </form>

                                <form action="backend.php" method="POST" onsubmit="return confirm('¿Eliminar esta oferta permanentemente?');">
                                    <input type="hidden" name="accion" value="borrar_oferta">
                                    <input type="hidden" name="id_oferta" value="<?= $oferta['id_oferta'] ?>">
                                    <button type="submit" class="btn btn-danger" style="padding: 5px 10px; font-size: 0.8em; margin: 0;" title="Eliminar">
                                        <i class='bx bx-trash'></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
                <p style="text-align: center; padding: 20px; color: #666;">No hay ofertas registradas.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>