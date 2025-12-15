<?php
$host = "localhost";
$usuario = "root";
$password = "";
$bd = "tienda_etnia";
$puerto = 3308;

$conexion = new mysqli($host, $usuario, $password, $bd, $puerto);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
?>

