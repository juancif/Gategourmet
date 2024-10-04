<?php
session_start();
$mysqli = new mysqli("localhost", "root", "", "gategourmet"); // Conexión a la base de datos

if ($mysqli->connect_errno) {
    echo "Error al conectar a la base de datos: " . $mysqli->connect_error;
    exit();
}

// Verificar si el usuario está logueado
if (!isset($_SESSION['nombre_usuario'])) {
    // Redirigir a la página de login si no está logueado
    header('Location: http://localhost/GateGourmet/login/login3.php');
    exit();
}

$nombre_usuario = $_SESSION['nombre_usuario']; // El nombre de usuario guardado en la sesión

// Verificar que se haya recibido un ID de correo y un estado
if (isset($_POST['id_correo']) && isset($_POST['estado'])) {
    $id_correo = $_POST['id_correo'];
    $estado = $_POST['estado'];

    // Función para guardar o actualizar la acción del usuario
    function guardarAccion($nombre_usuario, $id_correo, $estado) {
        global $mysqli;

        // Verificar si ya existe una acción previa para este correo
        $query = "SELECT * FROM acciones_usuarios WHERE nombre_usuario = '$nombre_usuario' AND id_correo = '$id_correo'";
        $result = $mysqli->query($query);

        if ($result->num_rows > 0) {
            // Si ya existe, actualizar el estado
            $query = "UPDATE acciones_usuarios SET estado = '$estado' WHERE nombre_usuario = '$nombre_usuario' AND id_correo = '$id_correo'";
        } else {
            // Si no existe, insertar una nueva acción
            $query = "INSERT INTO acciones_usuarios (nombre_usuario, id_correo, estado) VALUES ('$nombre_usuario', '$id_correo', '$estado')";
        }

        $mysqli->query($query);
    }

    // Guardar la acción realizada
    guardarAccion($nombre_usuario, $id_correo, $estado);

    // Redirigir de vuelta a la página principal después de guardar la acción
    header('Location: http://localhost/GateGourmet/Index/index_admin.php');
    exit();
} else {
    echo "Error: No se recibieron los datos correctos.";
    exit();
}
?>
