

<?php
include_once("config_register.php");

function generarNombreUsuario($nombre, $apellido, $dbConn) {
    // Genera el nombre de usuario basado en la primera letra del nombre y el apellido
    $nombre_usuario = strtolower(substr($nombre, 0, 1) . $apellido);

    // Verifica si el nombre de usuario ya existe
    $checkDocSql = "SELECT COUNT(*) FROM administradores WHERE nombre_usuario = :nombre_usuario";
    $checkDocQuery = $dbConn->prepare($checkDocSql);
    $checkDocQuery->bindparam(':nombre_usuario', $nombre_usuario);
    $checkDocQuery->execute();
    $count = $checkDocQuery->fetchColumn();

    if ($count > 0) {
        // Si el nombre de usuario ya existe, genera uno nuevo con las dos primeras letras del nombre
        $nombre_usuario = strtolower(substr($nombre, 0, 2) . $apellido);

        // Verifica nuevamente si el nombre de usuario generado existe
        $checkDocQuery->bindparam(':nombre_usuario', $nombre_usuario);
        $checkDocQuery->execute();
        $count = $checkDocQuery->fetchColumn();

        // Asegúrate de que el nombre de usuario sea único añadiendo un número si es necesario
        $i = 1;
        while ($count > 0) {
            $nombre_usuario = strtolower(substr($nombre, 0, 2) . $apellido . $i);
            $checkDocQuery->bindparam(':nombre_usuario', $nombre_usuario);
            $checkDocQuery->execute();
            $count = $checkDocQuery->fetchColumn();
            $i++;
        }
    }

    return $nombre_usuario;
}

if (isset($_POST['Submit'])) {
    $correo = $_POST['correo'];
    $nombres_apellidos = $_POST['nombres_apellidos'];
    $nombre_usuario = $_POST['nombre_usuario'];
    $contrasena = $_POST['contrasena'];
    $confirmar_contrasena = $_POST['confirmar_contrasena'];
    $area = $_POST['area'];
    $cargo = $_POST['cargo'];
    $rol = $_POST['rol'];

    // Separar nombre y apellido
    list($nombre, $apellido) = explode(' ', $nombres_apellidos, 2);

    // Generar nombre de usuario si no está definido
    if (empty($nombre_usuario)) {
        $nombre_usuario = generarNombreUsuario($nombre, $apellido, $dbConn);
    }

    // Verificar si algún campo está vacío
    if (empty($correo) || empty($nombres_apellidos) || empty($contrasena) || empty($confirmar_contrasena) || empty($area) || empty($cargo) || empty($rol)) {
        // Mostrar errores para campos vacíos
        if (empty($correo)) {
            echo "<font color='red'>Campo: correo está vacío.</font><br/>";
        }
        if (empty($nombres_apellidos)) {
            echo "<font color='red'>Campo: nombres_apellidos está vacío.</font><br/>";
        }
        if (empty($contrasena)) {
            echo "<font color='red'>Campo: contrasena está vacío.</font><br/>";
        }
        if (empty($confirmar_contrasena)) {
            echo "<font color='red'>Campo: confirmar_contrasena está vacío.</font><br/>";
        }
        if (empty($area)) {
            echo "<font color='red'>Campo: área está vacío.</font><br/>";
        }
        if (empty($cargo)) {
            echo "<font color='red'>Campo: cargo está vacío.</font><br/>";
        }
        if (empty($rol)) {
            echo "<font color='red'>Campo: rol está vacío.</font><br/>";
        }
        echo "<br/><a href='javascript:self.history.back();'>Volver</a>";
    } else {
        // Verificar si las contraseñas coinciden
        if ($contrasena !== $confirmar_contrasena) {
            echo "<font color='red'>Las contraseñas no coinciden.</font><br/>";
            echo "<br/><a href='javascript:self.history.back();'>Volver</a>";
            exit();
        }

        try {
            // Iniciar la transacción
            $dbConn->beginTransaction();
        
            // Verificar si el nombre_usuario ya existe en la base de datos
            $checkDocSql = "SELECT COUNT(*) FROM administradores WHERE nombre_usuario = :nombre_usuario";
            $checkDocQuery = $dbConn->prepare($checkDocSql);
            $checkDocQuery->bindparam(':nombre_usuario', $nombre_usuario);
            $checkDocQuery->execute();
            $count = $checkDocQuery->fetchColumn();
        
            // Verificar el campo cargo y definir la tabla correspondiente
            if ($rol === 'Administrador') {
                $sql = "INSERT INTO administradores (correo, nombres_apellidos, nombre_usuario, contrasena, area, cargo, rol) 
                        VALUES (:correo, :nombres_apellidos, :nombre_usuario, :contrasena, :area, :cargo, :rol)";
            } else {
                $sql = "INSERT INTO usuarios (correo, nombres_apellidos, nombre_usuario, contrasena, area, cargo, rol) 
                        VALUES (:correo, :nombres_apellidos, :nombre_usuario, :contrasena, :area, :cargo, :rol)";
            }
        
            $query = $dbConn->prepare($sql);
            $query->bindparam(':correo', $correo);
            $query->bindparam(':nombres_apellidos', $nombres_apellidos);
            $query->bindparam(':nombre_usuario', $nombre_usuario);
            $query->bindparam(':contrasena', password_hash($contrasena, PASSWORD_BCRYPT)); // Hash de la contraseña
            $query->bindparam(':area', $area);
            $query->bindparam(':cargo', $cargo);
            $query->bindparam(':rol', $rol);
            $query->execute();
        
            // Cometer la transacción
            $dbConn->commit();
        
            $ipAddress = $_SERVER['REMOTE_ADDR'];
            $movimientoSql = "INSERT INTO movimientos (nombre_usuario, accion, fecha) VALUES (:nombre_usuario, 'Registro exitoso', NOW())";
            $movimientoQuery = $dbConn->prepare($movimientoSql);
            $movimientoQuery->bindparam(':nombre_usuario', $nombre_usuario);
            $movimientoQuery->execute();

            if ($query->rowCount() > 0) {
                // Redirigir a la página deseada después del registro exitoso
                header("Location: http://localhost/GateGourmet/register/registro_exitoso/registro_exitoso.php");
                exit();
            } else {
                echo "<font color='red'>Error al registrar el usuario o administrador.</font><br/>";
            }
        } catch (Exception $e) {
            // Revertir los cambios si ocurre un error
            if ($dbConn->inTransaction()) {
                $dbConn->rollBack();
            }
            echo "<font color='black', font-size='40',>Error: El nombre de usuario ya está registrado.</font><br/>";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registro de Usuarios</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&family=Poppins:wght@400;600&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="style_register3.css">
</head>
<body>
    <header class="header">
        <img src="../Imagenes/Logo_oficial_B-N.png" alt="Gate Gourmet Logo" class="logo">
    </header>
    <main class="main-content">
        <div class="register-container">
            <div class="register-box">
                <h2>Registro de Usuarios</h2>
                <form method="post" action="">
                    <div class="input-group tooltip">
                        <label for="correo">Correo Electrónico</label>
                        <input type="email" id="correo" name="correo" required placeholder="example@gategroup.com">
                        <span class="tooltiptext">Recuerda, que para registrarte debes ingresar un correo con el dominio "@gategroup.com".</span>
                    </div>
                    <div class="input-group">
                        <label for="nombres_apellidos">Nombres y Apellidos</label>
                        <input type="text" id="nombres_apellidos" name="nombres_apellidos" required>
                    </div>
                    <div class="input-group tooltip">
                        <label for="contrasena">Contraseña</label>
                        <input type="password" id="contrasena" name="contrasena" required>
                        <span class="tooltiptext">Recuerda que la contraseña debe tener mínimo 12 caracteres, un carácter especial y una mayúscula.</span>
                    </div>
                    <div class="input-group tooltip">
                        <label for="confirmar_contrasena">Confirmar Contraseña</label>
                        <input type="password" id="confirmar_contrasena" name="confirmar_contrasena" required>
                        <span class="tooltiptext">Confirma tu contraseña.</span>
                    </div>
                    <div class="input-group">
                        <label for="area">Área</label>
                        <select name="area" id="area">
                            <option value="">Seleccione una opción</option>
                            <option value="Gestión corporativa">Gestión corporativa</option>
                            <option value="Compliance">Compliance</option>
                            <option value="Supply chain">Supply Chain</option>
                            <option value="Culinary">Culinary</option>
                            <option value="Assembly">Assembly</option>
                            <option value="Service delivery"  >Service Delivery</option>
                            <option value="Servicios institucionales">Servicios institucionales</option>
                            <option value="Financiera">Financiera</option>
                            <option value="Costos">Costos</option>
                            <option value="Comunicaciones">Comunicaciones</option>
                            <option value="Tecnologia de la información">Tecnologia de la información</option>
                            <option value="Security">Security</option>
                            <option value="Servicio al cliente">Servicio al cliente</option>
                            <option value="Facilty service">Facilty Service</option>
                            <option value="Talento humano">Talento Humano</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <label for="cargo">Cargo</label>
                        <select name="cargo" id="cargo">
                            <option value="">Seleccione una opción</option>
                            <option value="Auxiliar Contable">Auxiliar Contable</option>
                            <option value="Continuous Improvement Manager">Continuous Improvement Manager</option>
                            <option value="Coordinador de mejoramiento Continuo">Coordinador de mejoramiento Continuo</option>
                            <option value="Country Manager">Country Manager</option>
                            <option value="CPC Champion">CPC Champion</option>
                            <option value="DirectorComercial">Director Comercial</option>
                            <option value="EHS_Manager">EHS Manager</option>
                            <option value="Especialista de Seguridad en Rampa">Especialista de Seguridad en Rampa</option>
                            <option value="Especialista en Mantenimiento">Especialista en Mantenimiento</option>
                            <option value="Executive Sous Chef">Executive Sous Chef</option>
                            <option value="Jefe Control de Riesgos Fisicos">Jefe Control de Riesgos Fisicos</option>
                            <option value="Jefe de Costos">Jefe de Costos</option>
                            <option value="Junior Section Manager OP&D">Junior Section Manager OP&D</option>
                            <option value="Junior Key Account Officer">Junior Key Account Officer</option>
                            <option value="Manager HR">Manager HR</option>
                            <option value="Manager Ordering">Manager, Ordering</option>
                            <option value="Manager Transport">Manager Transport</option>
                            <option value="Manager New Operations">Manager New Operations</option>
                            <option value="Manager Finance">Manager Finance </option>
                            <option value="Process Owner Assembly">Process Owner Assembly</option>
                            <option value="Process Owner Planning & Supply_Chain">Process Owner Planning & Supply Chain</option>
                            <option value="Process Owner Service Delivery">Process Owner Service Delivery</option>
                            <option value="Section Manager Pick & Pack">Section_Manager_Pick_&_Pack</option>
                            <option value="Section Manager Wash & Pack">Section Manager Wash & Pack</option>
                            <option value="Section Manager Laundry">Section Manager Laundry</option>
                            <option value="Section Manager Make & Pack">Section Manager Make & Pack</option>
                            <option value="Section Manager IDS">Section Manager - IDS</option>
                            <option value="Sous Chef">Sous Chef</option>
                            <option value="Senior Manager Facility Services">Senior Manager Facility Services</option>
                            <option value="Superintendent HR">Superintendent HR</option>
                            <option value="Superintendent Development And Communications">Superintendent Development And Communications</option>
                            <option value="VIP Lounges Junior Section Manager">VIP Lounges Junior Section Manager</option>
                            <option value="Supervisor de Calidad y Gestion Ambiental">Supervisor de Calidad y Gestion Ambiental</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <label for="rol">Rol</label>
                        <select name="rol" id="rol">
                            <option value="">Seleccione una opción</option>
                            <option value="Administrador">Administrador</option>
                            <option value="Aprobador">Aprobador</option>
                            <option value="Digitador">Digitador</option>
                            <option value="Observador">Observador</option>
                         </select>  
                    </div>
                    <div class="buttons">
                        <input type="submit" name="Submit" value="Registrarse" class="Registrarse">
                        <a href="http://localhost/GateGourmet/login/login3.php" class="regresar">Regresar</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
    <footer class="footer">
        <p><a href="#">Ayuda</a> | <a href="#">Términos de servicio</a></p>
    </footer>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelector('form').addEventListener('submit', function(event) {
            var emailField = document.getElementById('correo');
            var emailValue = emailField.value;
            var passwordField = document.getElementById('contrasena');
            var confirmPasswordField = document.getElementById('confirmar_contrasena');
            var passwordValue = passwordField.value;
            var confirmPasswordValue = confirmPasswordField.value;

            // Verificar si el correo electrónico tiene el dominio específico
            if (!emailValue.endsWith('@gategroup.com')) {
                alert('El correo electrónico debe tener el dominio "@gategroup.com".');
                event.preventDefault(); // Evita el envío del formulario
            }

            // Verificar si las contraseñas coinciden
            if (passwordValue !== confirmPasswordValue) {
                alert('Las contraseñas no coinciden.');
                event.preventDefault(); // Evita el envío del formulario
            }
        });
    });
    </script>
</body>
</html>






