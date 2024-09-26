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

// Comprobar si el formulario fue enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recoger los datos del formulario
    $macroproceso = $_POST['macroproceso'];
    $proceso = $_POST['proceso'];
    $usuario = $_POST['usuario'];
    $cargo = $_POST['cargo'];
    $email = $_POST['email'];
    $rol = $_POST['rol'];

    // Insertar datos en la base de datos
    $stmt = $conn->prepare("INSERT INTO procesos (macroproceso, proceso, usuario, cargo, email, rol) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $macroproceso, $proceso, $usuario, $cargo, $email, $rol);

    if ($stmt->execute()) {
        echo "Nuevo proceso agregado exitosamente.";
    } else {
        echo "Error: " . $stmt->error;
    }

    // Cerrar la consulta y la conexión
    $stmt->close();
    $conn->close();

    // Redirigir de vuelta a la página principal
    header("Location: index.php");
    exit;
}
?>
