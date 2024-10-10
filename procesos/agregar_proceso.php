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
    die("Conexión fallida. Por favor, intente más tarde.");
}

// Obtener datos del formulario con validaciones básicas
$macroproceso = isset($_POST['macroproceso']) ? $_POST['macroproceso'] : null; // solo un valor
$proceso = !empty($_POST['proceso']) ? trim($_POST['proceso']) : null;
$usuario = !empty($_POST['usuario']) ? trim($_POST['usuario']) : null;
$cargo = !empty($_POST['cargo']) ? trim($_POST['cargo']) : null;
$email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) ? $_POST['email'] : null;
$rol = !empty($_POST['rol']) ? trim($_POST['rol']) : null;

// Verificar si todos los campos requeridos están completos
if (!$proceso || !$usuario || !$cargo || !$email || !$rol || !$macroproceso) {
    echo "Por favor, complete todos los campos requeridos.";
    exit;
}

// Obtener el orden correcto para el nuevo proceso
$stmt = $conn->prepare("SELECT COALESCE(MAX(orden), 0) FROM procesos WHERE macroproceso = ?");
$stmt->bind_param("s", $macroproceso);
$stmt->execute();
$stmt->bind_result($max_orden);
$stmt->fetch();
$stmt->close();

// Establecer el nuevo orden como el máximo existente más uno
$nuevo_orden = $max_orden + 1;

// Consulta SQL para insertar el nuevo proceso con su orden
$stmt = $conn->prepare("INSERT INTO procesos (macroproceso, proceso, usuario, cargo, email, rol, orden) VALUES (?, ?, ?, ?, ?, ?, ?)"); 
$stmt->bind_param("ssssssi", $macroproceso, $proceso, $usuario, $cargo, $email, $rol, $nuevo_orden);

if ($stmt->execute()) {
    echo "Proceso agregado exitosamente.";
} else {
    echo "Error al agregar el proceso: " . $stmt->error;
}

// Cerrar la declaración y conexión
$stmt->close();
$conn->close();
?>
