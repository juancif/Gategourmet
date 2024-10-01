<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$database = "gategourmet";

$connect = new mysqli($servername, $username, $password, $database);

// Verificar la conexión
if ($connect->connect_error) {
    die("Error de conexión: " . $connect->connect_error);
}

// Variable para el mensaje de error
$error_message = '';

// Verificar si se ha enviado el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['nombre_usuario']) && isset($_POST['contrasena'])) {
        $nombre_usuario = $_POST['nombre_usuario'];
        $contrasena = $_POST['contrasena'];

        // Inicializar variable para el rol
        $rol = null;

        // Buscar en la tabla de usuarios
        $stmt = $connect->prepare("SELECT * FROM usuarios WHERE nombre_usuario = ?");
        $stmt->bind_param("s", $nombre_usuario);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $usuario = $result->fetch_assoc();
            $hash_contrasena = $usuario['contrasena'];
            $rol = $usuario['rol'];

            if (password_verify($contrasena, $hash_contrasena) || $contrasena === $hash_contrasena) {
                $area = $usuario['area'];

                // Registrar el inicio de sesión en la tabla de movimientos
                $sql = "INSERT INTO movimientos (nombre_usuario, rol, accion, fecha) VALUES (?, ?, 'Inicio de sesión como: $rol', NOW())";
                $stmt = $connect->prepare($sql);
                $stmt->bind_param("ss", $nombre_usuario, $rol);
                $stmt->execute();

                // Guardar el área en la sesión
                $_SESSION['area'] = $area;
                $_SESSION['nombre_usuario'] = $nombre_usuario;

                // Redirigir al dashboard con el área del usuario
                header("Location: http://localhost/GateGourmet/Index/index_user.php");
                exit();
            } else {
                $error_message = "Nombre de usuario o contraseña incorrectos.";
            }
        } else {
            // Verificar en la tabla de administradores
            $stmt = $connect->prepare("SELECT * FROM administradores WHERE nombre_usuario = ?");
            $stmt->bind_param("s", $nombre_usuario);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $admin = $result->fetch_assoc();
                $hash_contrasena = $admin['contrasena'];
                $rol = 'Administrador'; // Asignar el rol de administrador

                if (password_verify($contrasena, $hash_contrasena) || $contrasena === $hash_contrasena) {
                    $area = $admin['area'];

                    // Registrar el inicio de sesión en la tabla de movimientos
                    $sql = "INSERT INTO movimientos (nombre_usuario, rol, accion, fecha) VALUES (?, ?, 'Inicio de sesión como administrador', NOW())";
                    $stmt = $connect->prepare($sql);
                    $stmt->bind_param("ss", $nombre_usuario, $rol);
                    $stmt->execute();

                    // Guardar el área en la sesión
                    $_SESSION['area'] = $area;
                    $_SESSION['nombre_usuario'] = $nombre_usuario;

                    // Redirigir al dashboard con el área del administrador
                    header("Location: http://localhost/GateGourmet/Index/index_admin.php");
                    exit();
                } else {
                    $error_message = "Nombre de usuario o contraseña incorrectos.";
                }
            } else {
                $error_message = "Nombre de usuario o contraseña incorrectos.";
            }
        }
    } else {
        $error_message = "Por favor, ingrese nombre de usuario y contraseña.";
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
    <title>Inicio Sesión Gategourmet</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&family=Poppins:wght@400;600&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="style_login3.css">
</head>
<body>
    <header class="header">
        <img src="../Imagenes/Logo_oficial_B-N.png" alt="Gate Gourmet Logo" class="logo">
    </header>
    <main class="main-content">
        <div class="login-container">
            <div class="login-box">
                <img src="../Imagenes/image.png" alt="User Icon" class="user-icon">
                <h2>BIENVENIDO</h2>
                <form method="post" action="">
                    <div class="input-group tooltip">
                        <label for="nombre_usuario">Nombre de usuario</label>
                        <div class="input-icon">
                            <i class="fas fa-user"></i>
                            <input type="text" id="nombre_usuario" name="nombre_usuario" required placeholder="Nombre de usuario" value="<?php if(isset($_POST['nombre_usuario'])) echo htmlspecialchars($_POST['nombre_usuario']); ?>"/>
                            <span class="tooltiptext">Recuerda, que el nombre de usuario es la primera letra de tu nombre y primer apellido completo".</span>
                        </div>
                    </div>
                    <div class="input-group">
                        <label for="contrasena">Contraseña</label>
                        <div class="input-icon password-group">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="contrasena" name="contrasena" required placeholder="Contraseña" />
                            <span class="toggle-password" onclick="togglePassword('contrasena', 'eye_contrasena')">
                                <img src="../Imagenes/ojo_invisible.png" id="eye_contrasena" alt="Mostrar contraseña" />
                            </span>
                        </div>
                    </div>
                    <div class="buttons">
                        <input type="submit" value="Ingresar">
                        <a href="http://localhost/GateGourmet/register/register3.php" class="button">Registrarse</a>
                        <a href="http://localhost/GateGourmet/restablecer/reestablecer.php" class="button-reestablecer">Restablecer Contraseña</a>
                    </div>
                </form>

                <?php if (!empty($error_message)): ?>
                <div class="error-message">
                    <?php echo $error_message; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    <footer class="footer">
        <p><a href="#">Ayuda</a> | <a href="#">Términos de servicio</a></p>
    </footer>
    <script>
        function togglePassword(inputId, eyeId) {
            const input = document.getElementById(inputId);
            const eye = document.getElementById(eyeId);
            if (input.type === "password") {
                input.type = "text";
                eye.src = "../Imagenes/ojo_visible.png";
            } else {
                input.type = "password";
                eye.src = "../Imagenes/ojo_invisible.png";
            }
        }
    </script>
</body>
</html>
