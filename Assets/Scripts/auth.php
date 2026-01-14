<?php
session_start();
require_once "conexion.php";

// CORRECCIÓN: Usamos $_REQUEST para aceptar tanto GET (Links) como POST (Formularios)
$accion = $_REQUEST['accion'] ?? '';

// --- REGISTRO ---
if ($accion == 'registro') {
    $nombre = $conexion->real_escape_string($_POST['nombre']);
    $email = $conexion->real_escape_string($_POST['email']);
    $pass = $_POST['password'];

    if (!preg_match('/^(?=.*\d).{8,}$/', $pass)) {
        die(json_encode(['status' => 'error', 'msg' => 'La contraseña debe tener 8 caracteres y un número.']));
    }

    $check = $conexion->query("SELECT id_usuario FROM usuarios WHERE email = '$email'");
    if ($check->num_rows > 0) {
        die(json_encode(['status' => 'error', 'msg' => 'Este correo ya está registrado.']));
    }

    $passHash = password_hash($pass, PASSWORD_DEFAULT);
    $sql = "INSERT INTO usuarios (nombre, email, password) VALUES ('$nombre', '$email', '$passHash')";

    if ($conexion->query($sql)) {
        $nuevo_id = $conexion->insert_id;

        // Verificamos que realmente se generó un ID
        if ($nuevo_id > 0) {
            $_SESSION['id_usuario'] = $nuevo_id;
            $_SESSION['nombre'] = $nombre;
            echo json_encode(['status' => 'success']);
        } else {
            // Esto sucede si la tabla no tiene AUTO_INCREMENT
            echo json_encode(['status' => 'error', 'msg' => 'Error: No se pudo generar ID de usuario.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'msg' => 'Error en base de datos: ' . $conexion->error]);
    }
}

// --- LOGIN ---
if ($accion == 'login') {
    $email = $conexion->real_escape_string($_POST['email']);
    $pass = $_POST['password'];

    $sql = "SELECT id_usuario, nombre, password FROM usuarios WHERE email = '$email'";
    $res = $conexion->query($sql);

    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        if (password_verify($pass, $row['password'])) {
            $_SESSION['id_usuario'] = $row['id_usuario'];
            $_SESSION['nombre'] = $row['nombre'];
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'msg' => 'Contraseña incorrecta.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'msg' => 'Usuario no encontrado.']);
    }
}

// --- LOGOUT (CORREGIDO) ---
if ($accion == 'logout') {
    // 1. Vaciar el array de sesión
    $_SESSION = array();

    // 2. Borrar la cookie de sesión del navegador (si existe)
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    // 3. Destruir la sesión en el servidor
    session_destroy();

    // 4. Redirigir al Index
    // Como auth.php está en Assets/Scripts, subimos 2 niveles
    header("Location: ../../index.php");
    exit; // IMPORTANTE: Detiene el script para evitar pantallas blancas
}
?>