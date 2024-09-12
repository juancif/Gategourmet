<?php
include_once("config_gestor.php");

if (isset($_POST['update'])) {
    $correo = $_POST['correo'];
    $nombres_apellidos = $_POST['nombres_apellidos'];
    $nombre_usuario = $_POST['nombre_usuario'];
    $contrasena = $_POST['contrasena'];
    $area = $_POST['area'];
    $cargo = $_POST['cargo'];
    $rol = $_POST['rol'];

    // Validación de campos
    $errors = [];
    if (empty($correo)) $errors[] = "Campo: correo está vacío.";
    if (empty($nombres_apellidos)) $errors[] = "Campo: nombres_apellidos está vacío.";
    if (empty($nombre_usuario)) $errors[] = "Campo: nombre_usuario está vacío.";
    if (empty($contrasena)) $errors[] = "Campo: contrasena está vacío.";
    if (empty($area)) $errors[] = "Campo: area está vacío.";
    if (empty($cargo)) $errors[] = "Campo: cargo está vacío.";
    if (empty($rol)) $errors[] = "Campo: rol está vacío.";
    
    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo "<font color='red'>{$error}</font><br/>";
        }
    } else {
        // Verificar el rol actual
        $sql_check_rol = "SELECT rol FROM usuarios WHERE nombre_usuario = :nombre_usuario";
        $query_check = $dbConn->prepare($sql_check_rol);
        $query_check->execute([':nombre_usuario' => $nombre_usuario]);
        $row_check = $query_check->fetch(PDO::FETCH_ASSOC);
        $current_rol = $row_check['rol'];

        // Si el rol ha cambiado a Administrador
        if ($rol === 'Administrador') {
            // Insertar en la tabla administradores
            $sql_insert_admin = "INSERT INTO administradores (correo, nombres_apellidos, nombre_usuario, contrasena, area, cargo, rol)
                                 VALUES (:correo, :nombres_apellidos, :nombre_usuario, :contrasena, :area, :cargo, :rol)";
            $query_insert = $dbConn->prepare($sql_insert_admin);
            $query_insert->bindParam(':correo', $correo);
            $query_insert->bindParam(':nombres_apellidos', $nombres_apellidos);
            $query_insert->bindParam(':nombre_usuario', $nombre_usuario);
            $query_insert->bindParam(':contrasena', $contrasena);
            $query_insert->bindParam(':area', $area);
            $query_insert->bindParam(':cargo', $cargo);
            $query_insert->bindParam(':rol', $rol);
            $query_insert->execute();

            // Eliminar del usuario
            $sql_delete_user = "DELETE FROM usuarios WHERE nombre_usuario = :nombre_usuario";
            $query_delete = $dbConn->prepare($sql_delete_user);
            $query_delete->execute([':nombre_usuario' => $nombre_usuario]);
        } else {
            // Actualizar el usuario sin mover entre tablas
            $sql_update = "UPDATE usuarios SET correo=:correo, nombres_apellidos=:nombres_apellidos, contrasena=:contrasena,  
                           area=:area, cargo=:cargo, rol=:rol
                           WHERE nombre_usuario=:nombre_usuario";
            $query_update = $dbConn->prepare($sql_update);
            $query_update->bindParam(':correo', $correo);
            $query_update->bindParam(':nombres_apellidos', $nombres_apellidos);
            $query_update->bindParam(':nombre_usuario', $nombre_usuario);
            $query_update->bindParam(':contrasena', $contrasena);
            $query_update->bindParam(':area', $area);
            $query_update->bindParam(':cargo', $cargo);
            $query_update->bindParam(':rol', $rol);
            $query_update->execute();
        }

        header("Location: index_gestor.php");
        exit();
    }
}

if (isset($_GET['nombre_usuario'])) {
    $nombre_usuario = $_GET['nombre_usuario'];
    $sql = "SELECT * FROM usuarios WHERE nombre_usuario=:nombre_usuario";
    $query = $dbConn->prepare($sql);
    $query->execute([':nombre_usuario' => $nombre_usuario]);
    $row = $query->fetch(PDO::FETCH_ASSOC);
    $correo = $row['correo'];
    $nombres_apellidos = $row['nombres_apellidos'];
    $contrasena = $row['contrasena'];
    $area = $row['area'];
    $cargo = $row['cargo'];
    $rol = $row['rol'];
}
?>


<html>
<head>
    <title>Editar Datos</title>
    <link rel="stylesheet" href="../../css/style_edit_gestor.css">
</head>
<body>
<form name="form1" method="post" action="edit_gestor.php">
    <header class="header">
        <img src="../../../Imagenes/Logo_oficial_B-N.png" alt="Gate Gourmet Logo" class="logo">
    </header>
    <main class="main-content">
        <div class="register-container">
            <div class="register-box">
                <h2>Edición de Usuarios</h2>
                <form method="post" action="edit_gestor.php">
                <div class="input-group tooltip">
                        <label for="correo">Correo Electrónico</label>
                        <input type="email" id="correo" name="correo" required placeholder="example@gategroup.com" value="<?php echo htmlspecialchars($correo, ENT_QUOTES); ?>">
                        <span class="tooltiptext">Recuerda, que para registrarte debes ingresar un correo con el dominio "@gategroup.com".</span>
                    </div>
                    <div class="input-group">
                        <label for="nombres_apellidos">Nombres y Apellidos</label>
                        <input type="text" id="nombres_apellidos" name="nombres_apellidos" required value="<?php echo htmlspecialchars($nombres_apellidos, ENT_QUOTES); ?>">
                    </div>
                    <div class="input-group">
                        <label for="nombre_usuario">Nombre de Usuario</label>
                        <input type="text" id="nombre_usuario" name="nombre_usuario" required value="<?php echo htmlspecialchars($nombre_usuario, ENT_QUOTES); ?>">
                    </div>
                    <div class="input-group tooltip">
                    <label for="contrasena">Contraseña</label>
                    <div class="input-wrapper">
                        <input type="password" id="contrasena" name="contrasena" required>
                        <span class="toggle-password" onclick="togglePassword('contrasena', 'eye_contrasena')">
                            <img src="../../../Imagenes/ojo_invisible.png" id="eye_contrasena" alt="Mostrar contraseña" />
                        </span>
                    </div>
                    <span class="tooltiptext">Recuerda que la contraseña debe tener mínimo 12 caracteres, un carácter especial y una mayúscula.</span>
                </div>

                <div class="input-group tooltip">
                    <label for="confirmar_contrasena">Confirmar Contraseña</label>
                    <div class="input-wrapper">
                        <input type="password" id="confirmar_contrasena" name="confirmar_contrasena" required>
                        <span class="toggle-password" onclick="togglePassword('confirmar_contrasena', 'eye_confirmar_contrasena')">
                            <img src="../../../Imagenes/ojo_invisible.png" id="eye_confirmar_contrasena" alt="Mostrar contraseña" />
                        </span>
                    </div>
                    <span class="tooltiptext">Confirma tu contraseña.</span>
                </div>
                    <div class="input-group">
                    <label for="area">Área</label>
                <select name="area" id="area">
                    <option value="">Seleccione una opción</option>
                    <option value="Gestión corporativa" <?php if ($area == 'Gestión corporativa') echo 'selected'; ?>>Gestión corporativa</option>
                    <option value="Compliance" <?php if ($area == 'Compliance') echo 'selected'; ?>>Compliance</option>
                    <option value="Supply chain" <?php if ($area == 'Supply chain') echo 'selected'; ?>>Supply Chain</option>
                    <option value="Culinary Excellence" <?php if ($area == 'Culinary Excellence') echo 'selected'; ?>>Culinary Excellence</option>
                    <option value="Supervisor" <?php if ($area == 'Supervisor') echo 'selected'; ?>>Service Delivery</option>
                    <option value="Assembly" <?php if ($area == 'Assembly') echo 'selected'; ?>>Assembly</option>
                    <option value="Servicios institucionales" <?php if ($area == 'Servicios institucionales') echo 'selected'; ?>>Servicios institucionales</option>
                    <option value="Financiera" <?php if ($area == 'Financiera') echo 'selected'; ?>>Financiera</option>
                    <option value="Costos" <?php if ($area == 'Costos') echo 'selected'; ?>>Costos</option>
                    <option value="Comunicaciones" <?php if ($area == 'Comunicaciones') echo 'selected'; ?>>Comunicaciones</option>
                    <option value="Tecnologia de la información" <?php if ($area == 'Tecnologia de la información') echo 'selected'; ?>>Tecnologia de la información</option>
                    <option value="Talento humano" <?php if ($area == 'Talento humano') echo 'selected'; ?>>Talento Humano</option>
                    <option value="Facility service" <?php if ($area == 'Facility service') echo 'selected'; ?>>Facility service</option>
                    <option value="Servicio al cliente" <?php if ($area == 'Servicio al cliente') echo 'selected'; ?>>Servicio al cliente</option>
                    <option value="Security" <?php if ($area == 'Security') echo 'selected'; ?>>Security</option>
                </select>
                </div>
                <div class="input-group">
                    <label for="cargo">Cargo</label>
                    <select name="cargo" id="cargo">
                        <option value="">Seleccione una opción</option>
                        <option value="Auxiliar Contable" <?php if ($cargo == 'Auxiliar Contable') echo 'selected'; ?>>Auxiliar Contable</option>
                        <option value="Continuous Improvement Manager" <?php if ($cargo == 'Continuous Improvement Manager') echo 'selected'; ?>>Continuous Improvement Manager</option>
                        <option value="Coordinador de mejoramiento Continuo" <?php if ($cargo == 'Coordinador de mejoramiento Continuo') echo 'selected'; ?>>Coordinador de mejoramiento Continuo</option>
                        <option value="Country Manager" <?php if ($cargo == 'Country Manager') echo 'selected'; ?>>Country Manager</option>
                        <option value="CPC Champion" <?php if ($cargo == 'CPC Champion') echo 'selected'; ?>>CPC Champion</option>
                        <option value="Director Comercial" <?php if ($cargo == 'Director Comercial') echo 'selected'; ?>>Director Comercial</option>
                        <option value="EHS Manager" <?php if ($cargo == 'EHS Manager') echo 'selected'; ?>>EHS Manager</option>
                        <option value="Especialista de Seguridad en Rampa" <?php if ($cargo == 'Especialista de Seguridad en Rampa') echo 'selected'; ?>>Especialista de Seguridad en Rampa</option>
                        <option value="Especialista en Mantenimiento" <?php if ($cargo == 'Especialista en Mantenimiento') echo 'selected'; ?>>Especialista en Mantenimiento</option>
                        <option value="Executive Sous Chef" <?php if ($cargo == 'Executive Sous Chef') echo 'selected'; ?>>Executive Sous Chef</option>
                        <option value="Jefe Control de Riesgos Fisicos" <?php if ($cargo == 'Jefe Control de Riesgos Fisicos') echo 'selected'; ?>>Jefe Control de Riesgos Fisicos</option>
                        <option value="Jefe de Costos" <?php if ($cargo == 'Jefe de Costos') echo 'selected'; ?>>Jefe de Costos</option>
                        <option value="Junior Section Manager OP&D" <?php if ($cargo == 'Junior Section Manager OP&D') echo 'selected'; ?>>Junior Section Manager OP&D</option>
                        <option value="Junior Key Account Officer" <?php if ($cargo == 'Junior Key Account Officer') echo 'selected'; ?>>Junior Key Account Officer</option>
                        <option value="Manager HR" <?php if ($cargo == 'Manager HR') echo 'selected'; ?>>Manager HR</option>
                        <option value="Manager Ordering" <?php if ($cargo == 'Manager Ordering') echo 'selected'; ?>>Manager, Ordering</option>
                        <option value="Manager Transport" <?php if ($cargo == 'Manager Transport') echo 'selected'; ?>>Manager Transport</option>
                        <option value="Manager New Operations" <?php if ($cargo == 'Manager New Operations') echo 'selected'; ?>>Manager New Operations</option>
                        <option value="Manager Finance" <?php if ($cargo == 'Manager Finance') echo 'selected'; ?>>Manager Finance</option>
                        <option value="Process Owner Assembly" <?php if ($cargo == 'Process Owner Assembly') echo 'selected'; ?>>Process Owner Assembly</option>
                        <option value="Process Owner Planning & Supply Chain" <?php if ($cargo == 'Process Owner Planning & Supply Chain') echo 'selected'; ?>>Process Owner Planning & Supply Chain</option>
                        <option value="Process Owner Service Delivery" <?php if ($cargo == 'Process Owner Service Delivery') echo 'selected'; ?>>Process Owner Service Delivery</option>
                        <option value="Section Manager Pick & Pack" <?php if ($cargo == 'Section Manager Pick & Pack') echo 'selected'; ?>>Section Manager Pick & Pack</option>
                        <option value="Section Manager Wash & Pack" <?php if ($cargo == 'Section Manager Wash & Pack') echo 'selected'; ?>>Section Manager Wash & Pack</option>
                        <option value="Section Manager Laundry" <?php if ($cargo == 'Section Manager Laundry') echo 'selected'; ?>>Section Manager Laundry</option>
                        <option value="Section Manager Make & Pack" <?php if ($cargo == 'Section Manager Make & Pack') echo 'selected'; ?>>Section Manager Make & Pack</option>
                        <option value="Section Manager IDS" <?php if ($cargo == 'Section Manager IDS') echo 'selected'; ?>>Section Manager - IDS</option>
                        <option value="Sous Chef" <?php if ($cargo == 'Sous Chef') echo 'selected'; ?>>Sous Chef</option>
                        <option value="Senior Manager Facility Services" <?php if ($cargo == 'Senior Manager Facility Services') echo 'selected'; ?>>Senior Manager Facility Services</option>
                        <option value="Superintendent HR" <?php if ($cargo == 'Superintendent HR') echo 'selected'; ?>>Superintendent HR</option>
                        <option value="Superintendent Development And Communications" <?php if ($cargo == 'Superintendent Development And Communications') echo 'selected'; ?>>Superintendent Development And Communications</option>
                        <option value="Supervisor de Calidad y Gestion Ambiental" <?php if ($cargo == 'Supervisor de Calidad y Gestion Ambiental') echo 'selected'; ?>>VIP Lounges Junior Section Manager</option>
                    </select>
                    </div>

                    <div class="input-group">
                        <label for="rol">Rol</label>
                        <select name="rol" id="rol">
                            <option value="">Seleccione una opción</option>
                            <option value="Administrador" <?php if ($rol == 'Administrador') echo 'selected'; ?>>Administrador</option>
                            <option value="Aprobador" <?php if ($rol == 'Aprobador') echo 'selected'; ?>>Aprobador</option>
                            <option value="Digitador" <?php if ($rol == 'Digitador') echo 'selected'; ?>>Digitador</option>
                            <option value="Observador" <?php if ($rol == 'Observador') echo 'selected'; ?>>Observador</option>
                        </select>                    
                    </div>
                    <div class="buttons">
                        <input type="submit" name="update" value="Editar" class="Registrarse">
                        <a href="index_gestor.php" class="regresar">Volver</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
    <footer class="footer">
        <p><a href="#">Ayuda</a> | <a href="#">Términos de servicio</a></p>
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
    <script>
        function togglePassword(fieldId, eyeId) {
        var passwordField = document.getElementById(fieldId);
        var eyeIcon = document.getElementById(eyeId);
                                                                      
    // Alternar el tipo de input entre 'password' y 'text'
        if (passwordField.type === 'password') {
        passwordField.type = 'text';
        eyeIcon.src = '../../../Imagenes/ojo_visible.png'; // Cambia el ícono a "ocultar"
        } else {
        passwordField.type = 'password';
        eyeIcon.src = '../../../Imagenes/ojo_invisible.png'; // Cambia el ícono a "mostrar"
        }
            }
    </script>
    </footer>
</body>
</html>
