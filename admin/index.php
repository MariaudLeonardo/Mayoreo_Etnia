<?php
session_start();
// Si ya hay sesión, mandar directo al panel
if(isset($_SESSION['admin_id'])) { header('Location: panel.php'); exit; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login Admin - Etnia</title>
    <link rel="stylesheet" href="estilos_admin.css">
</head>
<body>
    <div class="login-wrapper">
        <div class="login-box">
            <h2 style="color: #A07855;">ADMINISTRACIÓN</h2>
            <form action="backend.php" method="POST">
                <input type="hidden" name="accion" value="login">
                <input type="text" name="usuario" placeholder="Usuario" required>
                <input type="password" name="password" placeholder="Contraseña" required>
                <button type="submit" class="btn btn-primary">ENTRAR</button>
            </form>
        </div>
    </div>
</body>
</html>