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

    // Validación de datos
    if (empty($macroproceso) || empty($proceso) || empty($usuario) || empty($cargo) || empty($email) || empty($rol)) {
        echo "Todos los campos son obligatorios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Formato de correo inválido.";
    } else {
        // Insertar datos en la base de datos
        $stmt = $conn->prepare("INSERT INTO procesos (macroproceso, proceso, usuario, cargo, email, rol) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $macroproceso, $proceso, $usuario, $cargo, $email, $rol);

        if ($stmt->execute()) {
            // Redirigir a la página principal con un parámetro de éxito
            header("Location: http://localhost/Gategourmet/Index/procesos.php?success=1");
        } else {
            echo "Error: " . $stmt->error;
        }

        // Cerrar la consulta
        $stmt->close();
    }

    // Cerrar la conexión
    $conn->close();
}
?>
