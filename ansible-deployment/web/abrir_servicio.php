<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['nombre'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $servicio = $_POST['servicio'];

    // Simulamos el "abrir" del servicio (esto podría redirigir a otra página o iniciar un servicio)
    echo "<h1>Estás abriendo el servicio: $servicio</h1>";
    echo "<p>Este es el panel de administración del servicio seleccionado. Aquí puedes gestionar tu servicio.</p>";

    // Aquí podrías hacer una redirección o una acción real (por ejemplo, acceder a una página de administración de ese servicio)
    // Si fuera Nextcloud o WordPress, podrías redirigir a las páginas correspondientes o lanzar alguna acción.
}
?>
