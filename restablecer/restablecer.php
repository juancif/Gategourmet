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

    // Verificar si el correo electrónico existe en la base de datos
    $stmt = $connect->prepare("SELECT correo FROM usuarios WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        // Generar el token aleatorio y su fecha de expiración (1 hora)
        $token = bin2hex(random_bytes(50));
        $token_expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Guardar el token y la dirección de correo en la base de datos
        $stmt = $connect->prepare("INSERT INTO password_resets (correo, token, token_expiry) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $correo, $token, $token_expiry);
        $stmt->execute();

        // Enviar el correo electrónico con el enlace de restablecimiento
        enviarCorreo($correo, $token);
        // Redirigir a la página de éxito
        header('Location: reestablecer_exitoso.php');
        exit();
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
    <link rel="stylesheet" href="restablecer.css">
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
                    <label for="correo">Correo Electrónico</label>
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
