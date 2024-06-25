<?php

// Establecer la conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$database = "gategoumet";

$connect = new mysqli($servername, $username, $password, $database);

// Verificar la conexión
if ($connect->connect_error) {
    die("Error de conexión: " . $connect->connect_error);
}

// Verificar si se ha enviado el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verificar si los campos del formulario fueron enviados
    if (isset($_POST['nombre_usuario']) && isset($_POST['contrasena'])) {
        // Recuperar los datos del formulario
        $nombre_usuario = $_POST['nombre_usuario'];
        $contrasena = $_POST['contrasena'];

        // Preparar la consulta para verificar las credenciales
        $stmt = $connect->prepare("SELECT * FROM usuarios WHERE nombre_usuario = ? AND contrasena = ?");
        $stmt->bind_param("ss", $nombre_usuario, $contrasena);
        $stmt->execute();
        $result = $stmt->get_result();

        // Verificar si se encontraron resultados
        if ($result->num_rows > 0) {
            // Las credenciales son correctas, redirigir al usuario a la página principal
            header("Location: http://localhost/Pagina_Prueba/index/Prueba_Index/index5.html");
            exit(); // Terminar el script después de la redirección
        } 
    } else {
        // Si no se enviaron los campos del formulario, mostrar un mensaje de error
        echo "Por favor, ingrese nombre de usuario y contrasena.";
    }
}

// Cerrar la conexión
$connect->close();
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inicio Sesión Gateourmet</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&family=Poppins:wght@400;600&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="style_login3.css">
</head>
<body>
    <header class="header">
        <img src="logo_oficial_color.png" alt="Gate Gourmet Logo" class="logo">
    </header>
    <main class="main-content">
        <div class="login-container">
            <div class="login-box">
                <img src="../../../../imagenes/user.png" alt="User Icon" class="user-icon">
                <h2>INGRESO</h2>
                <form method="post" action="">
                    <div class="input-group">
                        <label for="nombre_usuario">Nombres y apellidos</label>
                        <div class="input-icon">
                            <i class="fas fa-user"></i>
                            <input type="text" id="nombre_usuario" name="nombre_usuario" required placeholder="Nombres de usuario" value="<?php if(isset($_POST['nombre_usuario'])) echo $_POST['nombre_usuario'] ?>"/>
                        </div>
                    </div>
                    <div class="input-group">
                        <label for="contrasena">Contraseña</label>
                        <div class="input-icon password-group">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="contrasena" name="contrasena" required placeholder="Contraseña" value="<?php if(isset($_POST['contrasena'])) echo $_POST['contrasena'] ?>" class="campo_contrasena" />
                        </div>
                    </div>
                    <div class="buttons">
                        <input type="submit" value="Ingresar">
                        <a href="http://localhost/Pagina_Prueba/index/Prueba_index/login_register/register/register3.php" class="button">Registrarse</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
    <footer class="footer">
        <p><a href="#">Ayuda</a> | <a href="#">Términos de servicio</a></p>
    </footer>
    <script src="script2.js"></script>
</body>
</html>
