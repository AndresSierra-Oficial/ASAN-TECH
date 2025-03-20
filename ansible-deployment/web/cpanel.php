<?php
session_start();

// Verificar si la sesión está activa
if (!isset($_SESSION['nombre'])) {
    // Si no hay sesión activa, redirigir al login
    header("Location: index.html");
    exit;
}

// Obtener el nombre del usuario desde la sesión
$nombre_usuario = $_SESSION['nombre'];

include('conexion.php');

// Obtener los servicios del usuario
$stmt_servicios = $conexion->prepare("SELECT nombre_servicio FROM servicios WHERE id_cliente = (SELECT id_cliente FROM clientes WHERE nombre = ?)");
$stmt_servicios->bind_param("s", $nombre_usuario);
$stmt_servicios->execute();
$resultado_servicios = $stmt_servicios->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Bienvenido, <?php echo htmlspecialchars($nombre_usuario); ?></h1>
    </header>

    <section id="servicios">
        <h2>Tus Servicios</h2>
        <table>
            <tr>
                <th>Servicio</th>
                <th>Acciones</th>
            </tr>
            <?php
            while ($servicio = $resultado_servicios->fetch_assoc()) {
                echo "<tr>
                        <td>" . htmlspecialchars($servicio['nombre_servicio']) . "</td>
                        <td><button onclick='window.location.href=\"abrir_servicio.php?servicio=" . urlencode($servicio['nombre_servicio']) . "\"'>Abrir</button></td>
                      </tr>";
            }
            ?>
        </table>
    </section>

    <section id="cerrar-sesion">
        <form method="POST" action="cerrar_sesion.php">
            <button type="submit" style="background-color: red; color: white; padding: 10px 20px; border: none; cursor: pointer;">Cerrar sesión</button>
        </form>
    </section>

</body>
</html>
