<?php
session_start();
require_once "conexion.php";
header('Content-Type: application/json');

// Si no hay usuario logueado, no podemos hacer nada (excepto quizás checkear y devolver false)
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['status' => 'guest', 'is_favorite' => false]);
    exit;
}

$idUsuario = $_SESSION['id_usuario'];
$accion = $_POST['accion'] ?? '';
$idZapato = $_POST['id_zapato'] ?? 0;

if ($idZapato == 0) {
    echo json_encode(['status' => 'error', 'msg' => 'ID de producto inválido']);
    exit;
}

// 1. VERIFICAR ESTADO (Para saber si pintar la estrella llena o vacía al abrir)
if ($accion == 'verificar') {
    $sql = "SELECT * FROM favoritos WHERE id_usuario = $idUsuario AND id_zapato = $idZapato";
    $res = $conexion->query($sql);
    $esFavorito = ($res->num_rows > 0);
    echo json_encode(['status' => 'success', 'is_favorite' => $esFavorito]);
    exit;
}

// 2. TOGGLE (Agregar o Quitar)
if ($accion == 'toggle') {
    // Primero revisamos si ya existe
    $sqlCheck = "SELECT * FROM favoritos WHERE id_usuario = $idUsuario AND id_zapato = $idZapato";
    $res = $conexion->query($sqlCheck);

    if ($res->num_rows > 0) {
        // YA EXISTE -> LO BORRAMOS (Quitar de favoritos)
        $conexion->query("DELETE FROM favoritos WHERE id_usuario = $idUsuario AND id_zapato = $idZapato");
        echo json_encode(['status' => 'success', 'action' => 'removed']);
    } else {
        // NO EXISTE -> LO AGREGAMOS
        $conexion->query("INSERT INTO favoritos (id_usuario, id_zapato) VALUES ($idUsuario, $idZapato)");
        echo json_encode(['status' => 'success', 'action' => 'added']);
    }
    exit;
}
?>