<?php
session_start();
include('conexion.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre_usuario = trim($_POST['nombre']);
    $contrasena = trim($_POST['contrasena']);

    // Validar si los campos no están vacíos
    if (!empty($nombre_usuario) && !empty($contrasena)) {
        // Preparar la consulta para obtener el hash de la contraseña
        $stmt = $conexion->prepare("SELECT contrasena FROM clientes WHERE nombre = ?");
        $stmt->bind_param("s", $nombre_usuario);
        $stmt->execute();
        $stmt->store_result();

        // Si el usuario existe
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($hash);
            $stmt->fetch();

            // Verificar la contraseña usando password_verify
            if (password_verify($contrasena, $hash)) {
                // Iniciar sesión y redirigir al panel de control
                $_SESSION['nombre'] = $nombre_usuario;
                header("Location: cpanel.php");
                exit();
            } else {
                echo "Credenciales incorrectas.";
            }
        } else {
            echo "Usuario no encontrado.";
        }
    } else {
        echo "Por favor, completa ambos campos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Login</h1>
    <form method="POST" action="">
        <label for="nombre">Nombre de usuario:</label><br>
        <input type="text" name="nombre" id="nombre" required><br><br>

        <label for="contrasena">Contraseña:</label><br>
        <input type="password" name="contrasena" id="contrasena" required><br><br>

        <input type="submit" value="Iniciar sesión">
    </form>
</body>
</html>
