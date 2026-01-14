<?php
session_start();
if(!isset($_SESSION['admin_id'])) { header('Location: index.php'); exit; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Control</title>
    <link rel="stylesheet" href="estilos_admin.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <div class="sidebar">
        <h2>Mayoreo Etnia</h2>
        <a href="panel.php" class="activo"><i class='bx bxs-dashboard'></i> Inicio</a>
        <a href="populares.php"><i class='bx bxs-star'></i> Populares (Top 9)</a>
        <a href="productos.php"><i class='bx bxs-shopping-bag'></i> Productos</a>
        <a href="ofertas.php"><i class='bx bxs-offer'></i> Ofertas</a>
        
        <form action="backend.php" method="POST" style="margin-top: auto;">
            <input type="hidden" name="accion" value="logout">
            <button type="submit" class="btn btn-danger">Cerrar Sesión</button>
        </form>
    </div>

    <div class="contenido">
        <h1>Bienvenido al Panel</h1>
        <div class="card">
            <p>Selecciona una opción del menú de la izquierda para administrar:</p>
            <ul>
                <li><strong>Populares:</strong> Cambia los 9 zapatos de la portada.</li>
                <li><strong>Productos:</strong> Agrega, edita o elimina zapatos.</li>
                <li><strong>Ofertas:</strong> Gestiona descuentos.</li>
            </ul>
        </div>
    </div>
</body>
</html>