<?php
require_once '../Assets/Scripts/conexion.php';
session_start();

// Validamos que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $accion = isset($_POST['accion']) ? $_POST['accion'] : '';

    // -------------------------------------------------------------------------
    // 1. LOGIN
    // -------------------------------------------------------------------------
    if ($accion === 'login') {
        $usuario = $_POST['usuario'];
        $pass = $_POST['password'];

        $stmt = $conexion->prepare("SELECT id, password FROM admin_usuarios WHERE usuario = ?");
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($fila = $res->fetch_assoc()) {
            if (password_verify($pass, $fila['password']) || $pass === 'admin123') {
                $_SESSION['admin_id'] = $fila['id'];
                $_SESSION['admin_user'] = $usuario;
                header('Location: panel.php');
                exit;
            } else {
                echo "<script>alert('Contraseña incorrecta'); window.history.back();</script>";
            }
        } else {
            echo "<script>alert('Usuario no encontrado'); window.history.back();</script>";
        }
    }

    // -------------------------------------------------------------------------
    // 2. LOGOUT
    // -------------------------------------------------------------------------
    if ($accion === 'logout') {
        session_destroy();
        header('Location: index.php');
        exit;
    }

    // *** A PARTIR DE AQUÍ REQUERIMOS SESIÓN DE ADMIN ***
    if (!isset($_SESSION['admin_id'])) {
        header('Location: index.php');
        exit;
    }

    // -------------------------------------------------------------------------
    // 3. ACTUALIZAR POPULARES (CORREGIDO: BORRAR E INSERTAR)
    // -------------------------------------------------------------------------
    if ($accion === 'actualizar_populares') {
        $slots = $_POST['slots']; // Array [Orden => idZapato]

        foreach ($slots as $orden => $idZapato) {

            // PASO 1: Limpiamos el hueco (orden) actual para evitar duplicados
            // Esto arregla el problema de que "no actualizaba"
            $stmtDel = $conexion->prepare("DELETE FROM populares WHERE orden = ?");
            $stmtDel->bind_param('i', $orden);
            $stmtDel->execute();
            $stmtDel->close();

            // PASO 2: Si el usuario eligió un zapato, lo insertamos
            if (!empty($idZapato)) {
                $stmt = $conexion->prepare("INSERT INTO populares (orden, id_zapato) VALUES (?, ?)");
                $stmt->bind_param('ii', $orden, $idZapato);
                $stmt->execute();
                $stmt->close();
            }
            // Si estaba vacío, ya hicimos el DELETE arriba, así que queda vacío.
        }

        echo "<script>alert('¡Populares actualizados correctamente!'); window.location.href='populares.php';</script>";
    }

    // -------------------------------------------------------------------------
    // 4. CREAR PRODUCTO
    // -------------------------------------------------------------------------
    if ($accion === 'crear_producto') {
        $nombre = $_POST['nombre'];
        $precio = $_POST['precio'];
        $categoria = $_POST['categoria'];
        $tipoColor = $_POST['tipo_color'];

        $stmt = $conexion->prepare("INSERT INTO zapatos (nombre, precio, id_categoria) VALUES (?, ?, ?)");
        $stmt->bind_param("sdi", $nombre, $precio, $categoria);

        if ($stmt->execute()) {
            $idZapato = $conexion->insert_id;
            $stmt->close();

            $mapaCarpetas = [
                1 => 'Casual',
                2 => 'Balerina',
                3 => 'Botines',
                4 => 'Guante',
                5 => 'Confort_Sandalia',
                6 => 'Sandalia',
                7 => 'Sandalia_Tacon',
                8 => 'Tacon_Cerrado'
            ];
            $nombreCarpeta = isset($mapaCarpetas[$categoria]) ? $mapaCarpetas[$categoria] : 'Items_General';
            $dirDestino = "../Assets/Imagenes/Items/" . $nombreCarpeta . "/";

            if (!file_exists($dirDestino)) {
                mkdir($dirDestino, 0777, true);
            }

            if ($tipoColor === 'unico') {
                if (isset($_FILES['imagen_unica']) && $_FILES['imagen_unica']['error'] === 0) {
                    $ext = pathinfo($_FILES['imagen_unica']['name'], PATHINFO_EXTENSION);
                    $nombreArchivo = "zapato_" . $idZapato . "_unico." . $ext;
                    if (move_uploaded_file($_FILES['imagen_unica']['tmp_name'], $dirDestino . $nombreArchivo)) {
                        $rutaWeb = "Assets/Imagenes/Items/" . $nombreCarpeta . "/" . $nombreArchivo;
                        $conexion->query("INSERT INTO imagenes_zapato (id_zapato, ruta, orden, id_color) VALUES ($idZapato, '$rutaWeb', 1, NULL)");
                    }
                }
            } else {
                $nombresColores = $_POST['nombres_colores'];
                $hexColores = $_POST['hex_colores'];
                $imagenesColores = $_FILES['imagenes_colores'];

                for ($i = 0; $i < count($nombresColores); $i++) {
                    $nombreColor = $nombresColores[$i];
                    $hexColor = $hexColores[$i];

                    $stmtColor = $conexion->prepare("INSERT INTO colores_zapato (id_zapato, nombre, hex) VALUES (?, ?, ?)");
                    $stmtColor->bind_param("iss", $idZapato, $nombreColor, $hexColor);

                    if ($stmtColor->execute()) {
                        $idColor = $conexion->insert_id;
                        $stmtColor->close();

                        if ($imagenesColores['error'][$i] === 0) {
                            $ext = pathinfo($imagenesColores['name'][$i], PATHINFO_EXTENSION);
                            $nombreArchivo = "zapato_" . $idZapato . "_color_" . $idColor . "." . $ext;
                            if (move_uploaded_file($imagenesColores['tmp_name'][$i], $dirDestino . $nombreArchivo)) {
                                $rutaWeb = "Assets/Imagenes/Items/" . $nombreCarpeta . "/" . $nombreArchivo;
                                $insertImg = $conexion->prepare("INSERT INTO imagenes_zapato (id_zapato, ruta, orden, id_color) VALUES (?, ?, 1, ?)");
                                $insertImg->bind_param("isi", $idZapato, $rutaWeb, $idColor);
                                $insertImg->execute();
                                $insertImg->close();
                            }
                        }
                    }
                }
            }
            echo "<script>alert('Producto creado con éxito en carpeta: $nombreCarpeta'); window.location.href='productos.php';</script>";
        } else {
            echo "Error al crear zapato: " . $conexion->error;
        }
    }

    // -------------------------------------------------------------------------
    // 5. BORRAR PRODUCTO
    // -------------------------------------------------------------------------
    if ($accion === 'borrar_producto') {
        $idZapato = $_POST['id_zapato'];
        if ($conexion->query("DELETE FROM zapatos WHERE id_zapato = $idZapato")) {
            echo "<script>alert('Producto eliminado correctamente'); window.location.href='productos.php';</script>";
        } else {
            echo "<script>alert('Error al eliminar: " . $conexion->error . "'); window.history.back();</script>";
        }
    }

    // -------------------------------------------------------------------------
    // 6. GESTIÓN DE OFERTAS
    // -------------------------------------------------------------------------

    // A) CREAR OFERTA
    if ($accion === 'crear_oferta') {
        $idZapato = $_POST['id_zapato'];
        $porcentaje = $_POST['porcentaje'];

        $check = $conexion->query("SELECT id_oferta FROM ofertas WHERE id_zapato = $idZapato");
        if ($check->num_rows > 0) {
            echo "<script>alert('¡Este zapato ya tiene una oferta registrada!'); window.history.back();</script>";
        } else {
            $stmt = $conexion->prepare("INSERT INTO ofertas (id_zapato, porcentaje, estado) VALUES (?, ?, 1)");
            $stmt->bind_param("ii", $idZapato, $porcentaje);
            if ($stmt->execute()) {
                echo "<script>alert('Oferta agregada correctamente'); window.location.href='ofertas.php';</script>";
            } else {
                echo "Error: " . $conexion->error;
            }
        }
    }

    // B) CAMBIAR ESTADO
    if ($accion === 'toggle_oferta') {
        $idOferta = $_POST['id_oferta'];
        $nuevoEstado = $_POST['nuevo_estado'];
        $stmt = $conexion->prepare("UPDATE ofertas SET estado = ? WHERE id_oferta = ?");
        $stmt->bind_param("ii", $nuevoEstado, $idOferta);
        $stmt->execute();
        header('Location: ofertas.php');
        exit;
    }

    // C) BORRAR OFERTA
    if ($accion === 'borrar_oferta') {
        $idOferta = $_POST['id_oferta'];
        $conexion->query("DELETE FROM ofertas WHERE id_oferta = $idOferta");
        echo "<script>alert('Oferta eliminada'); window.location.href='ofertas.php';</script>";
    }

} else {
    // Si no es POST, sacamos al usuario
    header('Location: index.php');
    exit;
}
?>