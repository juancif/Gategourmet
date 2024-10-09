<?php
// Conexión a la base de datos MySQL
$servername = "localhost";
$username = "root";
$password = "";
$database = "gategourmet";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $database);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Obtener datos del formulario
$macroprocesos = isset($_POST['macroproceso']) ? $_POST['macroproceso'] : [];
$proceso = isset($_POST['proceso']) ? $_POST['proceso'] : '';
$usuario = isset($_POST['usuario']) ? $_POST['usuario'] : '';
$cargo = isset($_POST['cargo']) ? $_POST['cargo'] : '';
$email = isset($_POST['email']) ? $_POST['email'] : '';
$rol = isset($_POST['rol']) ? $_POST['rol'] : '';

// Convertir el array de macroprocesos a una cadena separada por comas
$macroproceso_string = implode(', ', $macroprocesos);

// Consulta SQL para insertar el nuevo proceso
$stmt = $conn->prepare("INSERT INTO procesos (macroproceso, proceso, usuario, cargo, email, rol) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssss", $macroproceso_string, $proceso, $usuario, $cargo, $email, $rol);

if ($stmt->execute()) {
    echo "Proceso agregado exitosamente.";
} else {
    echo "Error al agregar el proceso: " . $stmt->error;
}

// Cerrar la declaración y conexión
$stmt->close();
$conn->close();
?>
