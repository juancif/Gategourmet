<?php
include_once("/config/config_gestor.php");

if(isset($_POST['update']))

    $nombre_usuario = $_POST['nombre_usuario'];
    $contrasena = $_POST['contrasena'];
    $correo = $_POST['correo'];
    $nombres_apellidos = $_POST['nombres_apellidos'];;
    $documento = $_POST['documento'];
    $area = $_POST['area'];
    $cargo = $_POST['cargo'];
    $rol = $_POST['rol'];

    if(empty($nombre_usuario) || empty($contrasena) || empty($correo) || empty($nombres_apellidos) || 
    empty($documento) || empty($area) || empty($cargo) || empty($rol)) {
        if(empty($nombre_usuario)) {
            echo "<font color='red'>Campo: nombre_usuario está vacío.</font><br/>";
        }
        if(empty($contrasena)) {
            echo "<font color='red'>Campo: contrasena está vacío.</font><br/>";
        if(empty($correo)) {
            echo "<font color='red'>Campo: correo está vacío.</font><br/>";
        }
        if(empty($nombres_apellidos)) {
            echo "<font color='red'>Campo: nombres_apellidos está vacío.</font><br/>";
        }
        if(empty($documento)) {
            echo "<font color='red'>Campo: documento está vacío.</font><br/>";
        }
        if(empty($area)) {
            echo "<font color='red'>Campo: area está vacío.</font><br/>";
        }
        if(empty($cargo)) {
            echo "<font color='red'>Campo: cargo está vacío.</font><br/>";
        }
        if(empty($rol)) {
            echo "<font color='red'>Campo: rol está vacío.</font><br/>";
        }
    } else {
        $sql = "UPDATE usuarios SET nombre_usuario=:nombre_usuario, contrasena=:contrasena, correo=:correo, nombres_apellidos=:nombres_apellidos, 
        documento=:documento, area=:area, cargo=:cargo, rol=:rol
        WHERE documento=:documento";
        $query = $dbConn->prepare($sql);
        $query->bindParam(':nombre_usuario', $nombre_usuario);
        $query->bindParam(':contrasena', $contrasena);
        $query->bindParam(':correo', $correo);
        $query->bindParam(':nombres_apellidos', $nombres_apellidos);
        $query->bindParam(':documento', $documento);
        $query->bindParam(':area', $area);
        $query->bindParam(':cargo', $cargo);
        $query->bindParam(':rol', $rol); 
        $query->execute();
        header("Location: index_gestor.php");
    }
}
?>

<?php
$documento = $_GET['documento'];
$sql = "SELECT * FROM usuarios WHERE documento=:documento";
$query = $dbConn->prepare($sql);
$query->execute(array(':documento' => $documento));
$row = $query->fetch(PDO::FETCH_ASSOC);
$nombre_usuario = $row['nombre_usuario'];
$contrasena = $row['contrasena'];
$correo = $row['correo'];
$nombres_apellidos = $row['nombres_apellidos'];
$documento = $row['documento'];
$area = $row['area'];
$cargo = $row['cargo'];
$rol = $row['rol'];
?>
<html>
<head>
    <title>Editar Datos</title>
    <link rel="stylesheet" href="/Gestor_usuarios/css/style_edit_gestor.css">
</head>
<body>
<form name="form1" method="post" action="edit_gestor.php">
    <header class="header">
        <img src="../Imagenes/Logo_oficial_B-N.png" alt="Gate Gourmet Logo" class="logo">
    </header>
    <main class="main-content">
        <div class="register-container">
            <div class="register-box">
                <h2>Registro de Usuarios</h2>
                <form method="post" action="index_gestor.php">
                    <div class="input-group">
                        <label for="nombre_usuario">Nombre de Usuario</label>
                        <input type="text" id="nombre_usuario" name="nombre_usuario" required value="<?php echo $nombre_usuario;?>">
                    </div>
                    <div class="input-group">
                        <label for="contrasena">Contraseña</label>
                        <input type="contrasena" id="contrasena" name="contrasena" required value="<?php echo $contrasena;?>">
                    </div>
                    <div class="input-group">
                        <label for="correo">Correo Electrónico</label>
                        <input type="email" id="correo" name="correo" required value="<?php echo $correo;?>">
                    </div>
                    <div class="input-group">
                        <label for="first_name">nombres_apellidos</label>
                        <input type="text" id="nombres_apellidos" name="nombres_apellidos" required value="<?php echo $nombres_apellidos;?>">
                    </div>
                    <div class="input-group">
                        <label for="document">Documento</label>
                        <input type="text" id="documento" name="documento" required value="<?php echo $documento;?>">
                    </div>
                    <div class="input-group">
                        <label for="area">Área</label>
                        <select name="area" id="area" value="<?php echo $area;?>">
                            <option value="">Seleccione una opción</option>
                            <option value="Gestion_corporativa">Gestión corporativa</option>
                            <option value="Compliance">Compliance</option>
                            <option value="Supply_chain">Supply Chain</option>
                            <option value="Culinary_Excellence">Culinary Excellence</option>
                            <option value="Supervisor">Service Delivery</option>
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
                        <select name="cargo" id="cargo" value="<?php echo $cargo;?>">
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
                        <select name="rol" id="rol" value="<?php echo $rol;?>">
                            <option value="">Seleccione una opción</option>
                            <option value="Administrador">Administrador</option>
                            <option value="Aprobador">Aprobador</option>
                            <option value="Digitador">Digitador</option>
                            <option value="Observador">Observador</option>
                        </select>                    
                    </div>
                    <div class="buttons">
                    <input type="Submit" name="update" value="Editar" class="Registrarse"></input>
                        <a href="http://localhost/GateGourmet/Gestor_usuarios/php/user/index_gestor.php" class="regresar">Volver</a>
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
