<?php
include_once("/Gestor_usuarios/config/config_gestor.php");

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
            $dbConn->beginTransaction(); // Iniciar transacción

            if ($rol === 'Administrador') {
                $sql = "INSERT INTO administradores (nombre_usuario, contrasena, correo, nombres_apellidos, documento, area, cargo, rol) 
                        VALUES (:nombre_usuario, :contrasena, :correo, :nombres_apellidos, :documento, :area, :cargo, :rol)";
            } else {
                $sql = "INSERT INTO usuarios (nombre_usuario, contrasena, correo, nombres_apellidos, documento, area, cargo, rol) 
                        VALUES (:nombre_usuario, :contrasena, :correo, :nombres_apellidos, :documento, :area, :cargo, :rol)";
            }

            $query = $dbConn->prepare($sql);

            // Crear hash de la contraseña
            $contrasena_hash = password_hash($contrasena, PASSWORD_DEFAULT);

            $query->bindparam(':nombre_usuario', $nombre_usuario);
            $query->bindparam(':contrasena', $contrasena_hash);
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
                header("Location: http://localhost/GateGourmet/Gestor_usuarios/registro_exitoso.php");
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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registro de Usuarios</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&family=Poppins:wght@400;600&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="style_add_gestor.css">
</head>
<body>
    <header class="header">
        <img src="../Imagenes/logo_oficial_color.png" alt="Gate Gourmet Logo" class="logo">
    </header>
    <main class="main-content">
        <div class="register-container">
            <div class="register-box">
                <h2>Registro de Usuarios</h2>
                <form method="post" action="">
                    <div class="input-group">
                        <label for="nombre_usuario">Nombre de Usuario</label>
                        <input type="text" id="nombre_usuario" name="nombre_usuario" required>
                    </div>
                    <div class="input-group">
                        <label for="contrasena">Contraseña</label>
                        <input type="password" id="contrasena" name="contrasena" 
                        required onclick="mouseover('ejemplo')">
                    <div class="input-group">
                        <label for="correo">Correo Electrónico</label>
                        <input type="email" id="correo" name="correo" required>
                    </div>
                    <div class="input-group">
                        <label for="nombres_apellidos">Nombres y Apellidos</label>
                        <input type="text" id="nombres_apellidos" name="nombres_apellidos" required>
                    </div>
                    <div class="input-group">
                        <label for="documento">Documento</label>
                        <input type="number" id="documento" name="documento" required>
                    </div>
                    <div class="input-group">
                        <label for="area">Área</label>
                        <select name="area" id="area">
                            <option value="">Seleccione una opción</option>
                            <option value="Gestion_corporativa">Gestión corporativa</option>
                            <option value="Compliance">Compliance</option>
                            <option value="Supply_chain">Supply Chain</option>
                            <option value="Culinary_Excellence">Culinary Excellence</option>
                            <option value="Supervisor"  >Service Delivery</option>
                            <option value="Assembly">Assembly</option>
                            <option value="Servicios_institucionales">Servicios institucionales</option>
                            <option value="Financiera">Financiera</option>
                            <option value="Costos">Costos</option>
                            <option value="Comunicaciones">Comunicaciones</option>
                            <option value="Tecnologia_de_la_información">Tecnologia de la información</option>
                            <option value="Talento_humano">Talento Humano</option>
                            <option value="Mateninimiento">Mateninimiento</option>
                            <option value="Servicio_al_cliente">Servicio al cliente</option>
                            <option value="Security">Security</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <label for="cargo">Cargo</label>
                        <select name="cargo" id="cargo">
                            <option value="">Seleccione una opción</option>
                            <option value="Auxiliar_Contable">Auxiliar Contable</option>
                            <option value="Continuous_Improvement_Manager">Continuous Improvement Manager</option>
                            <option value="Coordinador_de_mejoramiento_Continuo">Coordinador de mejoramiento Continuo</option>
                            <option value="Country_Manager">Country Manager</option>
                            <option value="CPC_Champion">CPC Champion</option>
                            <option value="Director_Comercial">Director Comercial</option>
                            <option value="EHS_Manager">EHS Manager</option>
                            <option value="Especialista_de_Seguridad_en_Rampa">Especialista de Seguridad en Rampa</option>
                            <option value="Especialista_en_Mantenimiento">Especialista en Mantenimiento</option>
                            <option value="Executive_Sous_Chef">Executive Sous Chef</option>
                            <option value="Jefe_Control_de_Riesgos_Fisicos">Jefe Control de Riesgos Fisicos</option>
                            <option value="Jefe_de_Costos">Jefe de Costos</option>
                            <option value="Junior_Section_Manager_OP&D">Junior Section Manager OP&D</option>
                            <option value="Junior_Key_Account_Officer">Junior Key Account Officer</option>
                            <option value="Manager_HR">Manager HR</option>
                            <option value="Manager_Ordering">Manager, Ordering</option>
                            <option value="Manager_Transport">Manager Transport</option>
                            <option value="Manager_New_Operations">Manager New Operations</option>
                            <option value="Manager_Finance">Manager Finance </option>
                            <option value="Process_Owner_Assembly">Process Owner Assembly</option>
                            <option value="Process_Owner_Planning_&_Supply_Chain">Process Owner Planning & Supply Chain</option>
                            <option value="Process_Owner_Service_Delivery">Process Owner Service Delivery</option>
                            <option value="Section_Manager_Pick_&_Pack">Section_Manager_Pick_&_Pack</option>
                            <option value="Section_Manager_Wash_&_Pack">Section Manager Wash & Pack</option>
                            <option value="Section_Manager_Laundry">Section Manager Laundry</option>
                            <option value="Section_Manager_Make_&_Pack">Section Manager Make & Pack</option>
                            <option value="Section_Manager_IDS">Section Manager - IDS</option>
                            <option value="Sous_Chef">Sous Chef</option>
                            <option value="Senior_Manager_Facility_Services">Senior Manager Facility Services</option>
                            <option value="Superintendent_HR">Superintendent HR</option>
                            <option value="Superintendent_Development_And_Communications">Superintendent Development And Communications</option>
                            <option value="Supervisor_de_Calidad_y_Gestion_Ambiental">VIP Lounges Junior Section Manager</option>
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
                        <a href="http://localhost/GateGourmet/Gestor_usuarios/index_gestor.php" class="regresar">Regresar</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
    <footer class="footer">
        <p><a href="#">Ayuda</a> | <a href="#">Términos de servicio</a></p>
        <script src="script.js"></script>
    </footer>
</body>
</html>
