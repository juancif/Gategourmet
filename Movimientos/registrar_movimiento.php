<?php
function registrarMovimiento($nombre_usuario, $accion) {
    include 'config_mov.php'; // Incluye el archivo de conexión a la base de datos

    $sql = "INSERT INTO movimientos (nombre_usuario, accion) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $nombre_usuario, $accion);

    if ($stmt->execute()) {
        echo "Movimiento registrado con éxito.";
    } else {
        echo "Error al registrar el movimiento: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>