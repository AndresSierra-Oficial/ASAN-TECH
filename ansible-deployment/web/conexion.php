<?php
$servidor = "192.168.1.13";
$usuario = "root";
$contrasena = "";
$base_de_datos = "asan_tech";

$conexion = new mysqli($servidor, $usuario, $contrasena, $base_de_datos);

if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}
?>
