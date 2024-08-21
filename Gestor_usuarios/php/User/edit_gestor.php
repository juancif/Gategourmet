<?php
include_once("config_register.php");

if (isset($_POST['Submit'])) {
    $nombre_usuario = $_POST['nombre_usuario'];
    $contrasena = $_POST['contrasena'];
    $correo = $_POST['correo'];
    $nombres_apellidos = $_POST['nombres_apellidos'];
    $documento = $_POST['documento'];
    $area = $_POST['area'];
    $cargo = $_POST['cargo'];
    $rol = $_POST['rol'];

    // Verificar si algún campo está vacío
    if (empty($nombre_usuario) || empty($contrasena) || empty($correo) || empty($nombres_apellidos) || empty($documento) || empty($area) || empty($cargo) || empty($rol)) {
        if (empty($nombre_usuario)) {
            echo "<font color='red'>Campo: nombre_usuario está vacío.</font><br/>";
        }
        if (empty($contrasena)) {
            echo "<font color='red'>Campo: contrasena está vacío.</font><br/>";
        }
        if (empty($correo)) {
            echo "<font color='red'>Campo: correo está vacío.</font><br/>";
        }
        if (empty($nombres_apellidos)) {
            echo "<font color='red'>Campo: nombres_apellidos está vacío.</font><br/>";
        }
        if (empty($documento)) {
            echo "<font color='red'>Campo: documento está vacío.</font><br/>";
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
        try {
            $dbConn->beginTransaction();
        
            // Verificar si el documento ya existe en la base de datos
            $checkDocSql = "SELECT COUNT(*) FROM administradores WHERE documento = :documento";
            $checkDocQuery = $dbConn->prepare($checkDocSql);
            $checkDocQuery->bindparam(':documento', $documento);
            $checkDocQuery->execute();
            $count = $checkDocQuery->fetchColumn();

        
            // Verificar el campo cargo y definir la tabla correspondiente
            if ($rol === 'Administrador') {
                $sql = "INSERT INTO administradores (nombre_usuario, contrasena, correo, nombres_apellidos, documento, area, cargo, rol) 
                        VALUES (:nombre_usuario, :contrasena, :correo, :nombres_apellidos, :documento, :area, :cargo, :rol)";
            } else {
                $sql = "INSERT INTO usuarios (nombre_usuario, contrasena, correo, nombres_apellidos, documento, area, cargo, rol) 
                        VALUES (:nombre_usuario, :contrasena, :correo, :nombres_apellidos, :documento, :area, :cargo, :rol)";
            }
        
            $query = $dbConn->prepare($sql);
            $query->bindparam(':nombre_usuario', $nombre_usuario);
            $query->bindparam(':contrasena', $contrasena); // Hash de la contraseña
            $query->bindparam(':correo', $correo);
            $query->bindparam(':nombres_apellidos', $nombres_apellidos);
            $query->bindparam(':documento', $documento);
            $query->bindparam(':area', $area);
            $query->bindparam(':cargo', $cargo);
            $query->bindparam(':rol', $rol);
            $query->execute();
        
            $dbConn->commit();
        
            if ($query->rowCount() > 0) {
                // Redirigir a la página deseada después del registro exitoso
                header("Location: http://localhost/GateGourmet/register/registro_exitoso/registro_exitoso.php");
                exit();
            } else {
                echo "<font color='red'>Error al registrar el usuario o administrador.</font><br/>";
            }
        } catch (Exception $e) {
            $dbConn->rollBack();
            echo "<font color='red'>Error: " . $e->getMessage() . "</font><br/>";
        }
        
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                    <h2>Registro de Usuarios</h2>
                    <form method="post" action="edit_gestor.php">
                        <div class="input-group">
                            <label for="nombre_usuario">Nombre de Usuario</label>
                            <input type="text" id="nombre_usuario" name="nombre_usuario" required value="<?php echo htmlspecialchars($nombre_usuario); ?>">
                        </div>
                        <div class="input-group">
                            <label for="contrasena">Contraseña</label>
                            <input type="password" id="contrasena" name="contrasena" required value="<?php echo htmlspecialchars($contrasena); ?>">
                        </div>
                        <div class="input-group">
                            <label for="correo">Correo Electrónico</label>
                            <input type="email" id="correo" name="correo" required value="<?php echo htmlspecialchars($correo); ?>">
                        </div>
                        <div class="input-group">
                            <label for="nombres_apellidos">Nombres y Apellidos</label>
                            <input type="text" id="nombres_apellidos" name="nombres_apellidos" required value="<?php echo htmlspecialchars($nombres_apellidos); ?>">
                        </div>
                        <div class="input-group">
                            <label for="documento">Documento</label>
                            <input type="text" id="documento" name="documento" required value="<?php echo htmlspecialchars($documento); ?>">
                        </div>
                        <div class="input-group">
                            <label for="area">Área</label>
                            <select name="area" id="area" required>
                                <option value="">Seleccione una opción</option>
                                <option value="Gestion_corporativa" <?php echo ($area == 'Gestion_corporativa') ? 'selected' : ''; ?>>Gestión corporativa</option>
                                <option value="Compliance" <?php echo ($area == 'Compliance') ? 'selected' : ''; ?>>Compliance</option>
                                <option value="Supply_chain" <?php echo ($area == 'Supply_chain') ? 'selected' : ''; ?>>Supply Chain</option>
                                <option value="Culinary_Excellence" <?php echo ($area == 'Culinary_Excellence') ? 'selected' : ''; ?>>Culinary Excellence</option>
                                <option value="Supervisor" <?php echo ($area == 'Supervisor') ? 'selected' : ''; ?>>Service Delivery</option>
                                <option value="Assembly" <?php echo ($area == 'Assembly') ? 'selected' : ''; ?>>Assembly</option>
                                <option value="Servicios_institucionales" <?php echo ($area == 'Servicios_institucionales') ? 'selected' : ''; ?>>Servicios institucionales</option>
                                <option value="Financiera" <?php echo ($area == 'Financiera') ? 'selected' : ''; ?>>Financiera</option>
                                <option value="Costos" <?php echo ($area == 'Costos') ? 'selected' : ''; ?>>Costos</option>
                                <option value="Comunicaciones" <?php echo ($area == 'Comunicaciones') ? 'selected' : ''; ?>>Comunicaciones</option>
                                <option value="Tecnologia_de_la_información" <?php echo ($area == 'Tecnologia_de_la_información') ? 'selected' : ''; ?>>Tecnología de la información</option>
                                <option value="Talento_humano" <?php echo ($area == 'Talento_humano') ? 'selected' : ''; ?>>Talento Humano</option>
                                <option value="Mateninimiento" <?php echo ($area == 'Mateninimiento') ? 'selected' : ''; ?>>Mantenimiento</option>
                                <option value="Servicio_al_cliente" <?php echo ($area == 'Servicio_al_cliente') ? 'selected' : ''; ?>>Servicio al cliente</option>
                                <option value="Security" <?php echo ($area == 'Security') ? 'selected' : ''; ?>>Security</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label for="cargo">Cargo</label>
                            <select name="cargo" id="cargo" required>
                                <option value="">Seleccione una opción</option>
                                <option value="Auxiliar_Contable" <?php echo ($cargo == 'Auxiliar_Contable') ? 'selected' : ''; ?>>Auxiliar Contable</option>
                                <option value="Continuous_Improvement_Manager" <?php echo ($cargo == 'Continuous_Improvement_Manager') ? 'selected' : ''; ?>>Continuous Improvement Manager</option>
                                <option value="Coordinador_de_mejoramiento_Continuo" <?php echo ($cargo == 'Coordinador_de_mejoramiento_Continuo') ? 'selected' : ''; ?>>Coordinador de mejoramiento Continuo</option>
                                <option value="Country_Manager" <?php echo ($cargo == 'Country_Manager') ? 'selected' : ''; ?>>Country Manager</option>
                                <option value="CPC_Champion" <?php echo ($cargo == 'CPC_Champion') ? 'selected' : ''; ?>>CPC Champion</option>
                                <option value="Director_Comercial" <?php echo ($cargo == 'Director_Comercial') ? 'selected' : ''; ?>>Director Comercial</option>
                                <option value="EHS_Manager" <?php echo ($cargo == 'EHS_Manager') ? 'selected' : ''; ?>>EHS Manager</option>
                                <option value="Especialista_de_Seguridad_en_Rampa" <?php echo ($cargo == 'Especialista_de_Seguridad_en_Rampa') ? 'selected' : ''; ?>>Especialista de Seguridad en Rampa</option>
                                <option value="Especialista_en_Mantenimiento" <?php echo ($cargo == 'Especialista_en_Mantenimiento') ? 'selected' : ''; ?>>Especialista en Mantenimiento</option>
                                <option value="Executive_Sous_Chef" <?php echo ($cargo == 'Executive_Sous_Chef') ? 'selected' : ''; ?>>Executive Sous Chef</option>
                                <option value="Jefe_Control_de_Riesgos_Fisicos" <?php echo ($cargo == 'Jefe_Control_de_Riesgos_Fisicos') ? 'selected' : ''; ?>>Jefe Control de Riesgos Físicos</option>
                                <option value="Jefe_de_Costos" <?php echo ($cargo == 'Jefe_de_Costos') ? 'selected' : ''; ?>>Jefe de Costos</option>
                                <option value="Junior_Section_Manager_OP&D" <?php echo ($cargo == 'Junior_Section_Manager_OP&D') ? 'selected' : ''; ?>>Junior Section Manager OP&D</option>
                                <option value="Junior_Key_Account_Officer" <?php echo ($cargo == 'Junior_Key_Account_Officer') ? 'selected' : ''; ?>>Junior Key Account Officer</option>
                                <option value="Manager_HR" <?php echo ($cargo == 'Manager_HR') ? 'selected' : ''; ?>>Manager HR</option>
                                <option value="Manager_Ordering" <?php echo ($cargo == 'Manager_Ordering') ? 'selected' : ''; ?>>Manager, Ordering</option>
                                <option value="Manager_Transport" <?php echo ($cargo == 'Manager_Transport') ? 'selected' : ''; ?>>Manager Transport</option>
                                <option value="Manager_New_Operations" <?php echo ($cargo == 'Manager_New_Operations') ? 'selected' : ''; ?>>Manager New Operations</option>
                                <option value="Manager_Finance" <?php echo ($cargo == 'Manager_Finance') ? 'selected' : ''; ?>>Manager Finance</option>
                                <option value="Process_Owner_Assembly" <?php echo ($cargo == 'Process_Owner_Assembly') ? 'selected' : ''; ?>>Process Owner Assembly</option>
                                <option value="Process_Owner_Planning_&_Supply_Chain" <?php echo ($cargo == 'Process_Owner_Planning_&_Supply_Chain') ? 'selected' : ''; ?>>Process Owner Planning & Supply Chain</option>
                                <option value="Process_Owner_Service_Delivery" <?php echo ($cargo == 'Process_Owner_Service_Delivery') ? 'selected' : ''; ?>>Process Owner Service Delivery</option>
                                <option value="Section_Manager_Pick_&_Pack" <?php echo ($cargo == 'Section_Manager_Pick_&_Pack') ? 'selected' : ''; ?>>Section Manager Pick & Pack</option>
                                <option value="Section_Manager_Wash_&_Pack" <?php echo ($cargo == 'Section_Manager_Wash_&_Pack') ? 'selected' : ''; ?>>Section Manager Wash & Pack</option>
                                <option value="Section_Manager_Laundry" <?php echo ($cargo == 'Section_Manager_Laundry') ? 'selected' : ''; ?>>Section Manager Laundry</option>
                                <option value="Section_Manager_Make_&_Pack" <?php echo ($cargo == 'Section_Manager_Make_&_Pack') ? 'selected' : ''; ?>>Section Manager Make & Pack</option>
                                <option value="Section_Manager_IDS" <?php echo ($cargo == 'Section_Manager_IDS') ? 'selected' : ''; ?>>Section Manager IDS</option>
                                <option value="Sous_Chef" <?php echo ($cargo == 'Sous_Chef') ? 'selected' : ''; ?>>Sous Chef</option>
                                <option value="Senior_Manager_Facility_Services" <?php echo ($cargo == 'Senior_Manager_Facility_Services') ? 'selected' : ''; ?>>Senior Manager Facility Services</option>
                                <option value="Superintendent_HR" <?php echo ($cargo == 'Superintendent_HR') ? 'selected' : ''; ?>>Superintendent HR</option>
                                <option value="Superintendent_Development_And_Communications" <?php echo ($cargo == 'Superintendent_Development_And_Communications') ? 'selected' : ''; ?>>Superintendent Development And Communications</option>
                                <option value="Supervisor_de_Calidad_y_Gestion_Ambiental" <?php echo ($cargo == 'Supervisor_de_Calidad_y_Gestion_Ambiental') ? 'selected' : ''; ?>>VIP Lounges Junior Section Manager</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label for="rol">Rol</label>
                            <select name="rol" id="rol" required>
                                <option value="">Seleccione una opción</option>
                                <option value="Administrador" <?php echo ($rol == 'Administrador') ? 'selected' : ''; ?>>Administrador</option>
                                <option value="Aprobador" <?php echo ($rol == 'Aprobador') ? 'selected' : ''; ?>>Aprobador</option>
                                <option value="Digitador" <?php echo ($rol == 'Digitador') ? 'selected' : ''; ?>>Digitador</option>
                                <option value="Observador" <?php echo ($rol == 'Observador') ? 'selected' : ''; ?>>Observador</option>
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
        </footer>
    </body>
</html>

