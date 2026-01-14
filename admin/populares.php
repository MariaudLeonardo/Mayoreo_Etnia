<?php
require_once '../Assets/Scripts/conexion.php';
session_start();
if(!isset($_SESSION['admin_id'])) { header('Location: index.php'); exit; }

// 1. Obtener lista de TODOS los zapatos para el select
$todosZapatos = [];
$resZ = $conexion->query("SELECT id_zapato, nombre FROM zapatos ORDER BY nombre ASC");
while($row = $resZ->fetch_assoc()) $todosZapatos[] = $row;

// 2. Obtener la configuración actual (1 al 9)
$popularesData = [];
$resP = $conexion->query("SELECT p.orden, p.id_zapato, z.nombre,
         (SELECT ruta FROM imagenes_zapato WHERE id_zapato = z.id_zapato ORDER BY orden ASC LIMIT 1) as imagen
         FROM populares p
         LEFT JOIN zapatos z ON p.id_zapato = z.id_zapato
         ORDER BY p.orden ASC");

while($row = $resP->fetch_assoc()) {
    $popularesData[$row['orden']] = $row;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Populares</title>
    <link rel="stylesheet" href="estilos_admin.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <div class="sidebar">
        <h2>Mayoreo Etnia</h2>
        <a href="panel.php"><i class='bx bxs-dashboard'></i> Inicio</a>
        <a href="populares.php" class="activo"><i class='bx bxs-star'></i> Populares</a>
        <a href="productos.php"><i class='bx bxs-shopping-bag'></i> Productos</a>
        <a href="ofertas.php"><i class='bx bxs-offer'></i> Ofertas</a>
    </div>

    <div class="contenido">
        <h1>Gestionar Top 9 Populares</h1>
        <div class="card">
            <form action="backend.php" method="POST">
                <input type="hidden" name="accion" value="actualizar_populares">
                
                <div class="grid-populares">
                    <?php for($i=1; $i<=9; $i++): 
                        $actual = $popularesData[$i] ?? null;
                        // Ajustamos la ruta para que se vea desde la carpeta admin
                        $img = ($actual && $actual['imagen']) ? '../' . $actual['imagen'] : '../Assets/Imagenes/default.png';
                    ?>
                    <div style="text-align: center; border: 1px solid #eee; padding: 10px; border-radius: 4px;">
                        <strong>Posición #<?= $i ?></strong>
                        <img src="<?= $img ?>" class="preview-img">
                        <select name="slots[<?= $i ?>]">
                            <option value="">-- Espacio Vacío --</option>
                            <?php foreach($todosZapatos as $z): ?>
                                <option value="<?= $z['id_zapato'] ?>" 
                                    <?= ($actual && $actual['id_zapato'] == $z['id_zapato']) ? 'selected' : '' ?>>
                                    <?= $z['nombre'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endfor; ?>
                </div>
                <button type="submit" class="btn btn-primary" style="margin-top: 20px;">GUARDAR CAMBIOS</button>
            </form>
        </div>
    </div>
</body>
</html>