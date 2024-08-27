<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "gategourmet";

$connect = new mysqli($servername, $username, $password, $database);

// Verificar la conexión
if ($connect->connect_error) {
    die("Error de conexión: " . $connect->connect_error);
}

// Iniciar la sesión
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['nombre_usuario'])) {
    // Redirigir al usuario a la página de inicio de sesión si no está logueado
    header("Location: login3.php");
    exit();
}

// Verificar si se han recibido los parámetros de acción y redirección
if (isset($_GET['action']) && isset($_GET['redirect'])) {
    $action = $_GET['action'];
    $redirect = $_GET['redirect'];
    $nombre_usuario = $_SESSION['nombre_usuario'];

    // Registrar la acción en la base de datos
    $sql = "INSERT INTO movimientos (nombre_usuario, accion, fecha) VALUES (?, ?, NOW())";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("ss", $nombre_usuario, $action);
    $stmt->execute();

    // Redirigir al usuario a la página deseada
    header("Location: $redirect");
    exit();
} else {
    echo "Acción o redirección no especificada.";
}

// Cerrar la conexión
$connect->close();
?>
