<?php
session_start();
include('conexion.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre_usuario = trim($_POST['nombre']);
    $contrasena = trim($_POST['contrasena']);
    $servicios_seleccionados = isset($_POST['servicios']) ? $_POST['servicios'] : [];

    // Validar si los campos no están vacíos
    if (!empty($nombre_usuario) && !empty($contrasena)) {
        // Hashear la contraseña antes de almacenarla
        $contrasena_hash = password_hash($contrasena, PASSWORD_DEFAULT);

        // Preparar la consulta para insertar el nuevo cliente
        $stmt_cliente = $conexion->prepare("INSERT INTO clientes (nombre, contrasena) VALUES (?, ?)");
        $stmt_cliente->bind_param("ss", $nombre_usuario, $contrasena_hash);

        if ($stmt_cliente->execute()) {
            // Obtener el ID del cliente insertado
            $id_cliente = $conexion->insert_id;

            // Insertar los servicios seleccionados en la tabla 'servicios'
            if (!empty($servicios_seleccionados)) {
                $stmt_servicio = $conexion->prepare("INSERT INTO servicios (id_cliente, nombre_servicio) VALUES (?, ?)");
                foreach ($servicios_seleccionados as $servicio) {
                    $stmt_servicio->bind_param("is", $id_cliente, $servicio);
                    $stmt_servicio->execute();
                }
            }

            // Actualizar la sesión con el nombre del usuario registrado
            $_SESSION['nombre'] = $nombre_usuario;

            // Redirigir al cliente a cpanel.php después del registro exitoso
            header("Location: cpanel.php");
            exit; // Asegúrate de que no se ejecute más código después de la redirección
        } else {
            echo "<p>Error al registrar el usuario.</p>";
        }
    } else {
        echo "<p>Por favor, completa todos los campos.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Registro de Cliente</h1>
    <form method="POST" action="">
        <label for="nombre">Nombre de usuario:</label><br>
        <input type="text" name="nombre" id="nombre" required><br><br>

        <label for="contrasena">Contraseña:</label><br>
        <input type="password" name="contrasena" id="contrasena" required><br><br>

        <label>Selecciona los servicios:</label><br>
        <input type="checkbox" name="servicios[]" value="Nextcloud"> Nextcloud<br>
        <input type="checkbox" name="servicios[]" value="WordPress"> WordPress<br>
        <br>

        <input type="submit" value="Registrarse">
    </form>
</body>
</html>
