<?php
include_once("config_gestor.php");

if (isset($_POST['update'])) {
    $nombre_usuario = $_POST['nombre_usuario'];
    $contrasena = $_POST['contrasena'];
    $correo = $_POST['correo'];
    $nombres_apellidos = $_POST['nombres_apellidos'];
    $documento = $_POST['documento'];
    $area = $_POST['area'];
    $cargo = $_POST['cargo'];
    $rol = $_POST['rol'];

    // Validar campos vacíos
    $errors = [];
    if (empty($nombre_usuario)) $errors[] = "Campo: nombre_usuario está vacío.";
    if (empty($contrasena)) $errors[] = "Campo: contrasena está vacío.";
    if (empty($correo)) $errors[] = "Campo: correo está vacío.";
    if (empty($nombres_apellidos)) $errors[] = "Campo: nombres_apellidos está vacío.";
    if (empty($documento)) $errors[] = "Campo: documento está vacío.";
    if (empty($area)) $errors[] = "Campo: area está vacío.";
    if (empty($cargo)) $errors[] = "Campo: cargo está vacío.";
    if (empty($rol)) $errors[] = "Campo: rol está vacío.";

    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo "<font color='red'>$error</font><br/>";
        }
    } else {
        try {
            // Verificar el rol actual
            $sql = "SELECT rol FROM administradores WHERE documento=:documento";
            $query = $dbConn->prepare($sql);
            $query->execute([':documento' => $documento]);
            $currentRole = $query->fetchColumn();

            // Copiar a usuarios y eliminar de administradores si el rol cambia
            if ($currentRole == 'Administrador' && $rol != 'Administrador') {
                // Insertar en la tabla usuarios
                $sqlInsert = "INSERT INTO usuarios (nombre_usuario, contrasena, correo, nombres_apellidos, documento, area, cargo, rol)
                              VALUES (:nombre_usuario, :contrasena, :correo, :nombres_apellidos, :documento, :area, :cargo, :rol)";
                $queryInsert = $dbConn->prepare($sqlInsert);
                $queryInsert->bindParam(':nombre_usuario', $nombre_usuario);
                $queryInsert->bindParam(':contrasena', $contrasena);
                $queryInsert->bindParam(':correo', $correo);
                $queryInsert->bindParam(':nombres_apellidos', $nombres_apellidos);
                $queryInsert->bindParam(':documento', $documento);
                $queryInsert->bindParam(':area', $area);
                $queryInsert->bindParam(':cargo', $cargo);
                $queryInsert->bindParam(':rol', $rol);
                $queryInsert->execute();

                // Eliminar de la tabla administradores
                $sqlDelete = "DELETE FROM administradores WHERE documento=:documento";
                $queryDelete = $dbConn->prepare($sqlDelete);
                $queryDelete->execute([':documento' => $documento]);
            } else {
                // Solo actualizar en administradores si el rol no cambia
                $sqlUpdate = "UPDATE administradores SET nombre_usuario=:nombre_usuario, contrasena=:contrasena, correo=:correo, nombres_apellidos=:nombres_apellidos, 
                              area=:area, cargo=:cargo, rol=:rol WHERE documento=:documento";
                $queryUpdate = $dbConn->prepare($sqlUpdate);
                $queryUpdate->bindParam(':nombre_usuario', $nombre_usuario);
                $queryUpdate->bindParam(':contrasena', $contrasena);
                $queryUpdate->bindParam(':correo', $correo);
                $queryUpdate->bindParam(':nombres_apellidos', $nombres_apellidos);
                $queryUpdate->bindParam(':documento', $documento);
                $queryUpdate->bindParam(':area', $area);
                $queryUpdate->bindParam(':cargo', $cargo);
                $queryUpdate->bindParam(':rol', $rol);
                $queryUpdate->execute();
            }

            header("Location: index_gestor_admin.php");
            exit();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}
?>

<?php
// Obtener datos para el formulario
$documento = $_GET['documento'];
$sql = "SELECT * FROM administradores WHERE documento=:documento";
$query = $dbConn->prepare($sql);
$query->execute([':documento' => $documento]);
$row = $query->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Editar Datos</title>
    <link rel="stylesheet" href="../../css/style_edit_gestor.css">
</head>
<body>
<header class="header">
    <img src="../../../Imagenes/Logo_oficial_B-N.png" alt="Gate Gourmet Logo" class="logo">
</header>
<main class="main-content">
    <div class="register-container">
        <div class="register-box">
            <h2>Registro de Administradores</h2>
            <form name="form1" method="post" action="edit_gestor_admin.php">
                <div class="input-group">
                    <label for="nombre_usuario">Nombre de Usuario</label>
                    <input type="text" id="nombre_usuario" name="nombre_usuario" required value="<?php echo htmlspecialchars($row['nombre_usuario']);?>">
                </div>
                <div class="input-group">
                    <label for="contrasena">Contraseña</label>
                    <input type="password" id="contrasena" name="contrasena" required value="<?php echo htmlspecialchars($row['contrasena']);?>">
                </div>
                <div class="input-group">
                    <label for="correo">Correo Electrónico</label>
                    <input type="email" id="correo" name="correo" required value="<?php echo htmlspecialchars($row['correo']);?>">
                </div>
                <div class="input-group">
                    <label for="nombres_apellidos">Nombres y Apellidos</label>
                    <input type="text" id="nombres_apellidos" name="nombres_apellidos" required value="<?php echo htmlspecialchars($row['nombres_apellidos']);?>">
                </div>
                <div class="input-group">
                    <label for="documento">Documento</label>
                    <input type="text" id="documento" name="documento" required value="<?php echo htmlspecialchars($row['documento']);?>" readonly>
                </div>
                <div class="input-group">
                    <label for="area">Área</label>
                    <select name="area" id="area" required>
                        <option value="">Seleccione una opción</option>
                        <option value="Gestion_corporativa" <?php echo ($row['area'] == 'Gestion_corporativa') ? 'selected' : '';?>>Gestión corporativa</option>
                        <option value="Compliance" <?php echo ($row['area'] == 'Compliance') ? 'selected' : '';?>>Compliance</option>
                        <option value="Supply_chain" <?php echo ($row['area'] == 'Supply_chain') ? 'selected' : '';?>>Supply Chain</option>
                        <option value="Culinary_Excellence" <?php echo ($row['area'] == 'Culinary_Excellence') ? 'selected' : '';?>>Culinary Excellence</option>
                        <option value="Supervisor" <?php echo ($row['area'] == 'Supervisor') ? 'selected' : '';?>>Service Delivery</option>
                        <option value="Assembly" <?php echo ($row['area'] == 'Assembly') ? 'selected' : '';?>>Assembly</option>
                        <option value="Servicios_institucionales" <?php echo ($row['area'] == 'Servicios_institucionales') ? 'selected' : '';?>>Servicios institucionales</option>
                        <option value="Financiera" <?php echo ($row['area'] == 'Financiera') ? 'selected' : '';?>>Financiera</option>
                        <option value="Costos" <?php echo ($row['area'] == 'Costos') ? 'selected' : '';?>>Costos</option>
                        <option value="Comunicaciones" <?php echo ($row['area'] == 'Comunicaciones') ? 'selected' : '';?>>Comunicaciones</option>
                        <option value="Tecnologia_de_la_información" <?php echo ($row['area'] == 'Tecnologia_de_la_información') ? 'selected' : '';?>>Tecnologia de la información</option>
                        <option value="Talento_humano" <?php echo ($row['area'] == 'Talento_humano') ? 'selected' : '';?>>Talento Humano</option>
                        <option value="Mateninimiento" <?php echo ($row['area'] == 'Mateninimiento') ? 'selected' : '';?>>Mateninimiento</option>
                        <option value="Servicio_al_cliente" <?php echo ($row['area'] == 'Servicio_al_cliente') ? 'selected' : '';?>>Servicio al cliente</option>
                        <option value="Security" <?php echo ($row['area'] == 'Security') ? 'selected' : '';?>>Security</option>
                    </select>
                </div>
                <div class="input-group">
                    <label for="cargo">Cargo</label>
                    <select name="cargo" id="cargo" required>
                        <option value="">Seleccione una opción</option>
                        <option value="Auxiliar_Contable" <?php echo ($row['cargo'] == 'Auxiliar_Contable') ? 'selected' : '';?>>Auxiliar Contable</option>
                        <option value="Continuous_Improvement_Manager" <?php echo ($row['cargo'] == 'Continuous_Improvement_Manager') ? 'selected' : '';?>>Continuous Improvement Manager</option>
                        <option value="Coordinador_de_mejoramiento_Continuo" <?php echo ($row['cargo'] == 'Coordinador_de_mejoramiento_Continuo') ? 'selected' : '';?>>Coordinador de mejoramiento Continuo</option>
                        <option value="Country_Manager" <?php echo ($row['cargo'] == 'Country_Manager') ? 'selected' : '';?>>Country Manager</option>
                        <option value="CPC_Champion" <?php echo ($row['cargo'] == 'CPC_Champion') ? 'selected' : '';?>>CPC Champion</option>
                        <option value="Director_Comercial" <?php echo ($row['cargo'] == 'Director_Comercial') ? 'selected' : '';?>>Director Comercial</option>
                        <option value="EHS_Manager" <?php echo ($row['cargo'] == 'EHS_Manager') ? 'selected' : '';?>>EHS Manager</option>
                        <option value="Especialista_de_Seguridad_en_Rampa" <?php echo ($row['cargo'] == 'Especialista_de_Seguridad_en_Rampa') ? 'selected' : '';?>>Especialista de Seguridad en Rampa</option>
                        <option value="Especialista_en_Mantenimiento" <?php echo ($row['cargo'] == 'Especialista_en_Mantenimiento') ? 'selected' : '';?>>Especialista en Mantenimiento</option>
                        <option value="Executive_Sous_Chef" <?php echo ($row['cargo'] == 'Executive_Sous_Chef') ? 'selected' : '';?>>Executive Sous Chef</option>
                        <option value="Jefe_Control_de_Riesgos_Fisicos" <?php echo ($row['cargo'] == 'Jefe_Control_de_Riesgos_Fisicos') ? 'selected' : '';?>>Jefe Control de Riesgos Fisicos</option>
                        <option value="Jefe_de_Costos" <?php echo ($row['cargo'] == 'Jefe_de_Costos') ? 'selected' : '';?>>Jefe de Costos</option>
                        <option value="Junior_Section_Manager_OP&D" <?php echo ($row['cargo'] == 'Junior_Section_Manager_OP&D') ? 'selected' : '';?>>Junior Section Manager OP&D</option>
                        <option value="Junior_Key_Account_Officer" <?php echo ($row['cargo'] == 'Junior_Key_Account_Officer') ? 'selected' : '';?>>Junior Key Account Officer</option>
                        <option value="Manager_HR" <?php echo ($row['cargo'] == 'Manager_HR') ? 'selected' : '';?>>Manager HR</option>
                        <option value="Manager_Ordering" <?php echo ($row['cargo'] == 'Manager_Ordering') ? 'selected' : '';?>>Manager, Ordering</option>
                        <option value="Manager_Transport" <?php echo ($row['cargo'] == 'Manager_Transport') ? 'selected' : '';?>>Manager Transport</option>
                        <option value="Manager_New_Operations" <?php echo ($row['cargo'] == 'Manager_New_Operations') ? 'selected' : '';?>>Manager New Operations</option>
                        <option value="Manager_Finance" <?php echo ($row['cargo'] == 'Manager_Finance') ? 'selected' : '';?>>Manager Finance</option>
                        <option value="Process_Owner_Assembly" <?php echo ($row['cargo'] == 'Process_Owner_Assembly') ? 'selected' : '';?>>Process Owner Assembly</option>
                        <option value="Process_Owner_Planning_&_Supply_Chain" <?php echo ($row['cargo'] == 'Process_Owner_Planning_&_Supply_Chain') ? 'selected' : '';?>>Process Owner Planning & Supply Chain</option>
                        <option value="Process_Owner_Service_Delivery" <?php echo ($row['cargo'] == 'Process_Owner_Service_Delivery') ? 'selected' : '';?>>Process Owner Service Delivery</option>
                        <option value="Section_Manager_Pick_&_Pack" <?php echo ($row['cargo'] == 'Section_Manager_Pick_&_Pack') ? 'selected' : '';?>>Section Manager Pick & Pack</option>
                        <option value="Section_Manager_Wash_&_Pack" <?php echo ($row['cargo'] == 'Section_Manager_Wash_&_Pack') ? 'selected' : '';?>>Section Manager Wash & Pack</option>
                        <option value="Section_Manager_Laundry" <?php echo ($row['cargo'] == 'Section_Manager_Laundry') ? 'selected' : '';?>>Section Manager Laundry</option>
                        <option value="Section_Manager_Make_&_Pack" <?php echo ($row['cargo'] == 'Section_Manager_Make_&_Pack') ? 'selected' : '';?>>Section Manager Make & Pack</option>
                        <option value="Section_Manager_IDS" <?php echo ($row['cargo'] == 'Section_Manager_IDS') ? 'selected' : '';?>>Section Manager - IDS</option>
                        <option value="Sous_Chef" <?php echo ($row['cargo'] == 'Sous_Chef') ? 'selected' : '';?>>Sous Chef</option>
                        <option value="Senior_Manager_Facility_Services" <?php echo ($row['cargo'] == 'Senior_Manager_Facility_Services') ? 'selected' : '';?>>Senior Manager Facility Services</option>
                        <option value="Superintendent_HR" <?php echo ($row['cargo'] == 'Superintendent_HR') ? 'selected' : '';?>>Superintendent HR</option>
                        <option value="Superintendent_Development_And_Communications" <?php echo ($row['cargo'] == 'Superintendent_Development_And_Communications') ? 'selected' : '';?>>Superintendent Development And Communications</option>
                        <option value="Supervisor_de_Calidad_y_Gestion_Ambiental" <?php echo ($row['cargo'] == 'Supervisor_de_Calidad_y_Gestion_Ambiental') ? 'selected' : '';?>>Supervisor de Calidad y Gestión Ambiental</option>
                    </select>
                </div>
                <div class="input-group">
                    <label for="rol">Rol</label>
                    <select name="rol" id="rol" required>
                        <option value="">Seleccione una opción</option>
                        <option value="Administrador" <?php echo ($row['rol'] == 'Administrador') ? 'selected' : '';?>>Administrador</option>
                        <option value="Aprobador" <?php echo ($row['rol'] == 'Aprobador') ? 'selected' : '';?>>Aprobador</option>
                        <option value="Digitador" <?php echo ($row['rol'] == 'Digitador') ? 'selected' : '';?>>Digitador</option>
                        <option value="Observador" <?php echo ($row['rol'] == 'Observador') ? 'selected' : '';?>>Observador</option>
                    </select>
                </div>
                <div class="buttons">
                    <input type="submit" name="update" value="Editar" class="Registrarse">
                    <a href="index_gestor_admin.php" class="regresar">Volver</a>
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
