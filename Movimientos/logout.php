<?php

include_once("config_mov.php");
session_start();

// Asegúrate de que la sesión esté iniciada
if (isset($_SESSION['nombre_usuario'])) {
    // Registra el evento de cierre de sesión en el log
    $nombre_usuario = $_SESSION['nombre_usuario'];
    $log_message = "User $nombre_usuario logged out";

    // Conecta a la base de datos
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "gategourmet";

    // Crear conexión
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Verificar conexión
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Preparar la consulta
    $stmt = $conn->prepare("INSERT INTO movimientos (nombre_usuario, accion, fecha) VALUES (?, ?, NOW())");
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }

    // Vincular los parámetros
    $stmt->bind_param("ss", $nombre_usuario, $log_message);

    // Ejecutar la consulta
    if (!$stmt->execute()) {
        die("Execute failed: " . $stmt->error);
    }

    // Cerrar la declaración y la conexión
    $stmt->close();
    $conn->close();

    // Destruir la sesión
    session_destroy();

    // Confirmar el cierre de sesión
    echo "Cierre de sesión exitoso y registro en el log.";

    // Redirigir al usuario al inicio de sesión
    header("Location: http://localhost/GateGourmet/login/login3.php");
    exit();
} else {
    // Si no hay sesión activa, redirige a la página de inicio de sesión
    header("Location: http://localhost/GateGourmet/login/login3.php");
    exit();
}
?>
