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
                        <input type="password" id="contrasena" name="contrasena" required value="<?php echo htmlspecialchars($contrasena, ENT_QUOTES); ?>">
                        <span class="tooltiptext">Recuerda que la contraseña debe tener minimo 12 caracteres, un caracter especial y una mayuscula.</span>
                    </div>
                    <div class="input-group">
                        <label for="area">Área</label>
                        <select name="area" id="area">
                            <option value="">Seleccione una opción</option>
                            <option value="Gestion_corporativa" <?php if ($area == 'Gestion_corporativa') echo 'selected'; ?>>Gestión corporativa</option>
                            <option value="Compliance" <?php if ($area == 'Compliance') echo 'selected'; ?>>Compliance</option>
                            <option value="Supply_chain" <?php if ($area == 'Supply_chain') echo 'selected'; ?>>Supply Chain</option>
                            <option value="Culinary_Excellence" <?php if ($area == 'Culinary_Excellence') echo 'selected'; ?>>Culinary Excellence</option>
                            <option value="Supervisor" <?php if ($area == 'Supervisor') echo 'selected'; ?>>Service Delivery</option>
                            <option value="Assembly" <?php if ($area == 'Assembly') echo 'selected'; ?>>Assembly</option>
                            <option value="Servicios_institucionales" <?php if ($area == 'Servicios_institucionales') echo 'selected'; ?>>Servicios institucionales</option>
                            <option value="Financiera" <?php if ($area == 'Financiera') echo 'selected'; ?>>Financiera</option>
                            <option value="Costos" <?php if ($area == 'Costos') echo 'selected'; ?>>Costos</option>
                            <option value="Comunicaciones" <?php if ($area == 'Comunicaciones') echo 'selected'; ?>>Comunicaciones</option>
                            <option value="Tecnologia_de_la_información" <?php if ($area == 'Tecnologia_de_la_información') echo 'selected'; ?>>Tecnologia de la información</option>
                            <option value="Talento_humano" <?php if ($area == 'Talento_humano') echo 'selected'; ?>>Talento Humano</option>
                            <option value="Mateninimiento" <?php if ($area == 'Mateninimiento') echo 'selected'; ?>>Mateninimiento</option>
                            <option value="Servicio_al_cliente" <?php if ($area == 'Servicio_al_cliente') echo 'selected'; ?>>Servicio al cliente</option>
                            <option value="Security" <?php if ($area == 'Security') echo 'selected'; ?>>Security</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <label for="cargo">Cargo</label>
                        <select name="cargo" id="cargo">
                            <option value="">Seleccione una opción</option>
                            <option value="Auxiliar_Contable" <?php if ($cargo == 'Auxiliar_Contable') echo 'selected'; ?>>Auxiliar Contable</option>
                            <option value="Continuous_Improvement_Manager" <?php if ($cargo == 'Continuous_Improvement_Manager') echo 'selected'; ?>>Continuous Improvement Manager</option>
                            <option value="Coordinador_de_mejoramiento_Continuo" <?php if ($cargo == 'Coordinador_de_mejoramiento_Continuo') echo 'selected'; ?>>Coordinador de mejoramiento Continuo</option>
                            <option value="Country_Manager" <?php if ($cargo == 'Country_Manager') echo 'selected'; ?>>Country Manager</option>
                            <option value="CPC_Champion" <?php if ($cargo == 'CPC_Champion') echo 'selected'; ?>>CPC Champion</option>
                            <option value="Director_Comercial" <?php if ($cargo == 'Director_Comercial') echo 'selected'; ?>>Director Comercial</option>
                            <option value="EHS_Manager" <?php if ($cargo == 'EHS_Manager') echo 'selected'; ?>>EHS Manager</option>
                            <option value="Especialista_de_Seguridad_en_Rampa" <?php if ($cargo == 'Especialista_de_Seguridad_en_Rampa') echo 'selected'; ?>>Especialista de Seguridad en Rampa</option>
                            <option value="Especialista_en_Mantenimiento" <?php if ($cargo == 'Especialista_en_Mantenimiento') echo 'selected'; ?>>Especialista en Mantenimiento</option>
                            <option value="Executive_Sous_Chef" <?php if ($cargo == 'Executive_Sous_Chef') echo 'selected'; ?>>Executive Sous Chef</option>
                            <option value="Jefe_Control_de_Riesgos_Fisicos" <?php if ($cargo == 'Jefe_Control_de_Riesgos_Fisicos') echo 'selected'; ?>>Jefe Control de Riesgos Fisicos</option>
                            <option value="Jefe_de_Costos" <?php if ($cargo == 'Jefe_de_Costos') echo 'selected'; ?>>Jefe de Costos</option>
                            <option value="Junior_Section_Manager_OP&D" <?php if ($cargo == 'Junior_Section_Manager_OP&D') echo 'selected'; ?>>Junior Section Manager OP&D</option>
                            <option value="Junior_Key_Account_Officer" <?php if ($cargo == 'Junior_Key_Account_Officer') echo 'selected'; ?>>Junior Key Account Officer</option>
                            <option value="Manager_HR" <?php if ($cargo == 'Manager_HR') echo 'selected'; ?>>Manager HR</option>
                            <option value="Manager_Ordering" <?php if ($cargo == 'Manager_Ordering') echo 'selected'; ?>>Manager, Ordering</option>
                            <option value="Manager_Transport" <?php if ($cargo == 'Manager_Transport') echo 'selected'; ?>>Manager Transport</option>
                            <option value="Manager_New_Operations" <?php if ($cargo == 'Manager_New_Operations') echo 'selected'; ?>>Manager New Operations</option>
                            <option value="Manager_Finance" <?php if ($cargo == 'Manager_Finance') echo 'selected'; ?>>Manager Finance</option>
                            <option value="Process_Owner_Assembly" <?php if ($cargo == 'Process_Owner_Assembly') echo 'selected'; ?>>Process Owner Assembly</option>
                            <option value="Process_Owner_Planning_&_Supply_Chain" <?php if ($cargo == 'Process_Owner_Planning_&_Supply_Chain') echo 'selected'; ?>>Process Owner Planning & Supply Chain</option>
                            <option value="Process_Owner_Service_Delivery" <?php if ($cargo == 'Process_Owner_Service_Delivery') echo 'selected'; ?>>Process Owner Service Delivery</option>
                            <option value="Section_Manager_Pick_&_Pack" <?php if ($cargo == 'Section_Manager_Pick_&_Pack') echo 'selected'; ?>>Section Manager Pick & Pack</option>
                            <option value="Section_Manager_Wash_&_Pack" <?php if ($cargo == 'Section_Manager_Wash_&_Pack') echo 'selected'; ?>>Section Manager Wash & Pack</option>
                            <option value="Section_Manager_Laundry" <?php if ($cargo == 'Section_Manager_Laundry') echo 'selected'; ?>>Section Manager Laundry</option>
                            <option value="Section_Manager_Make_&_Pack" <?php if ($cargo == 'Section_Manager_Make_&_Pack') echo 'selected'; ?>>Section Manager Make & Pack</option>
                            <option value="Section_Manager_IDS" <?php if ($cargo == 'Section_Manager_IDS') echo 'selected'; ?>>Section Manager - IDS</option>
                            <option value="Sous_Chef" <?php if ($cargo == 'Sous_Chef') echo 'selected'; ?>>Sous Chef</option>
                            <option value="Senior_Manager_Facility_Services" <?php if ($cargo == 'Senior_Manager_Facility_Services') echo 'selected'; ?>>Senior Manager Facility Services</option>
                            <option value="Superintendent_HR" <?php if ($cargo == 'Superintendent_HR') echo 'selected'; ?>>Superintendent HR</option>
                            <option value="Superintendent_Development_And_Communications" <?php if ($cargo == 'Superintendent_Development_And_Communications') echo 'selected'; ?>>Superintendent Development And Communications</option>
                            <option value="Supervisor_de_Calidad_y_Gestion_Ambiental" <?php if ($cargo == 'Supervisor_de_Calidad_y_Gestion_Ambiental') echo 'selected'; ?>>VIP Lounges Junior Section Manager</option>
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
            document.querySelector('form').addEventListener('submit', function(event) {
                var emailField = document.getElementById('correo');
                var emailValue = emailField.value;

                // Verificar si el correo electrónico tiene el dominio específico
                if (!emailValue.endsWith('@gategroup.com')) {
                    alert('El correo electrónico debe tener el dominio "@gategroup.com".');
                    event.preventDefault(); // Evita el envío del formulario
                }
            });
        </script>
    </footer>
</body>
</html>
