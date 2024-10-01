<?php
require '../vendor/autoload.php'; // Incluye el autoload de Composer
require 'enviar_correo_restablecimiento.php'; // Incluye el archivo con la función enviarCorreo

// Establecer la conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$database = "gategourmet";

$connect = new mysqli($servername, $username, $password, $database);

// Verificar la conexión
if ($connect->connect_error) {
    die("Error de conexión: " . $connect->connect_error);
}

// Verificar si se ha enviado el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['correo'])) {
    $correo = $_POST['correo'];

    // Verificar si el correo electrónico existe en la tabla usuarios
    $stmt = $connect->prepare("SELECT correo FROM usuarios WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $result_usuarios = $stmt->get_result();

    // Verificar si el correo electrónico existe en la tabla administradores
    $stmt = $connect->prepare("SELECT correo FROM administradores WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $result_administradores = $stmt->get_result();

    if ($result_usuarios->num_rows == 1 || $result_administradores->num_rows == 1) {
        // Generar el token aleatorio y su fecha de expiración (1 hora)
        $token = bin2hex(random_bytes(50));
        $token_expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Insertar el token en password_resets solo si el correo existe en una tabla
        $stmt = $connect->prepare("INSERT INTO password_resets (correo, token, token_expiry) VALUES (?, ?, ?)");
        
        if ($stmt) {
            $stmt->bind_param("sss", $correo, $token, $token_expiry);
            if ($stmt->execute()) {
                // Enviar el correo electrónico con el enlace de restablecimiento
                enviarCorreo($correo, $token);

                // Redirigir a la página de éxito
                header('Location: reestablecer_exitoso.php');
                exit();
            } else {
                echo "<script>alert('Error al intentar restablecer la contraseña.');</script>";
            }
        } else {
            echo "<script>alert('Error en la preparación de la consulta.');</script>";
        }
    } else {
        echo "<script>alert('El correo electrónico no está registrado.');</script>";
    }
} else {
    // Mostrar formulario de solicitud de restablecimiento
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Restablecer Contraseña</title>
    <link rel="stylesheet" href="restablecer.css"> <!-- Aquí enlazamos la hoja de estilos mejorada -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- FontAwesome para los íconos -->
</head>
<body>
    <header class="header">
        <img src="../Imagenes/Logo_oficial_B-N.png" alt="Gate Gourmet Logo" class="logo">
    </header>
    <main class="main-content">
        <div class="reset-container">
            <h2>Restablecer Contraseña</h2>
            <form method="post" action="">
                <div class="input-group">
                    <i class="fa fa-envelope"></i> <!-- Icono de correo -->
                    <input type="email" id="correo" name="correo" required placeholder="Correo Electrónico"/>
                </div>
                <div class="buttons">
                    <input type="submit" value="Enviar Enlace de Restablecimiento">
                </div>
            </form>
        </div>
    </main>
</body>
</html>
<?php
}
$connect->close();
?>
