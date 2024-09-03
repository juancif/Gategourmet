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
        $sql_check_rol = "SELECT rol FROM administradores WHERE nombre_usuario = :nombre_usuario";
        $query_check = $dbConn->prepare($sql_check_rol);
        $query_check->execute([':nombre_usuario' => $nombre_usuario]);
        $row_check = $query_check->fetch(PDO::FETCH_ASSOC);
        $current_rol = $row_check['rol'];

        if ($current_rol != $rol) {
            if ($rol != 'Administrador') {
                // Mover el registro a la tabla 'usuarios'
                $sql_move = "INSERT INTO usuarios (correo, nombres_apellidos, nombre_usuario, contrasena, area, cargo, rol)
                             SELECT correo, nombres_apellidos, nombre_usuario, contrasena, area, cargo, :rol
                             FROM administradores
                             WHERE nombre_usuario = :nombre_usuario";
                $query_move = $dbConn->prepare($sql_move);
                $query_move->bindParam(':nombre_usuario', $nombre_usuario);
                $query_move->bindParam(':rol', $rol);
                $query_move->execute();
                
                // Eliminar el registro de la tabla 'administradores'
                $sql_delete = "DELETE FROM administradores WHERE nombre_usuario = :nombre_usuario";
                $query_delete = $dbConn->prepare($sql_delete);
                $query_delete->bindParam(':nombre_usuario', $nombre_usuario);
                $query_delete->execute();
            } else {
                // Actualizar el registro en la tabla 'administradores'
                $sql_update = "UPDATE administradores SET correo=:correo, nombres_apellidos=:nombres_apellidos, contrasena=:contrasena,  
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
        } else {
            // Si el rol no cambia, solo actualizar la tabla 'administradores'
            $sql_update = "UPDATE administradores SET correo=:correo, nombres_apellidos=:nombres_apellidos, contrasena=:contrasena,  
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

        header("Location: index_gestor_admin.php");
        exit();
    }
}

if (isset($_GET['nombre_usuario'])) {
    $nombre_usuario = $_GET['nombre_usuario'];
    $sql = "SELECT * FROM administradores WHERE nombre_usuario=:nombre_usuario";
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
<form name="form1" method="post" action="edit_gestor_admin.php">
    <header class="header">
        <img src="../../../Imagenes/Logo_oficial_B-N.png" alt="Gate Gourmet Logo" class="logo">
    </header>
    <main class="main-content">
        <div class="register-container">
            <div class="register-box">
                <h2>Registro de Usuarios</h2>
                <form method="post" action="index_gestor_admin.php">
                <div class="input-group">
                        <label for="correo">Correo Electrónico</label>
                        <input type="email" id="correo" name="correo" required value="<?php echo $correo;?>">
                    </div>
                    <div class="input-group">
                        <label for="nombres_apellidos">Nombres y Apellidos</label>
                        <input type="text" id="nombres_apellidos" name="nombres_apellidos" required value="<?php echo $nombres_apellidos;?>">
                    </div>
                    <div class="input-group">
                        <label for="nombre_usuario">Nombre de Usuario</label>
                        <input type="text" id="nombre_usuario" name="nombre_usuario" required value="<?php echo $nombre_usuario;?>">
                    </div>
                    <div class="input-group">
                        <label for="contrasena">Contraseña</label>
                        <input type="password" id="contrasena" name="contrasena" required value="<?php echo $contrasena;?>">
                    </div>
                    <div class="input-group">
    <label for="area">Área</label>
    <select name="area" id="area">
        <option value="">Seleccione una opción</option>
        <option value="Gestion_corporativa" <?php if($area == 'Gestion_corporativa') echo 'selected'; ?>>Gestión corporativa</option>
        <option value="Compliance" <?php if($area == 'Compliance') echo 'selected'; ?>>Compliance</option>
        <option value="Supply_chain" <?php if($area == 'Supply_chain') echo 'selected'; ?>>Supply Chain</option>
        <option value="Culinary_Excellence" <?php if($area == 'Culinary_Excellence') echo 'selected'; ?>>Culinary Excellence</option>
        <option value="Supervisor" <?php if($area == 'Supervisor') echo 'selected'; ?>>Service Delivery</option>
        <option value="Assembly" <?php if($area == 'Assembly') echo 'selected'; ?>>Assembly</option>
        <option value="Servicios_institucionales" <?php if($area == 'Servicios_institucionales') echo 'selected'; ?>>Servicios institucionales</option>
        <option value="Financiera" <?php if($area == 'Financiera') echo 'selected'; ?>>Financiera</option>
        <option value="Costos" <?php if($area == 'Costos') echo 'selected'; ?>>Costos</option>
        <option value="Comunicaciones" <?php if($area == 'Comunicaciones') echo 'selected'; ?>>Comunicaciones</option>
        <option value="Tecnologia_de_la_información" <?php if($area == 'Tecnologia_de_la_información') echo 'selected'; ?>>Tecnologia de la información</option>
        <option value="Talento_humano" <?php if($area == 'Talento_humano') echo 'selected'; ?>>Talento Humano</option>
        <option value="Mateninimiento" <?php if($area == 'Mateninimiento') echo 'selected'; ?>>Mateninimiento</option>
        <option value="Servicio_al_cliente" <?php if($area == 'Servicio_al_cliente') echo 'selected'; ?>>Servicio al cliente</option>
        <option value="Security" <?php if($area == 'Security') echo 'selected'; ?>>Security</option>
    </select>
</div>

<div class="input-group">
    <label for="cargo">Cargo</label>
    <select name="cargo" id="cargo">
        <option value="">Seleccione una opción</option>
        <option value="Auxiliar_Contable" <?php if($cargo == 'Auxiliar_Contable') echo 'selected'; ?>>Auxiliar Contable</option>
        <option value="Continuous_Improvement_Manager" <?php if($cargo == 'Continuous_Improvement_Manager') echo 'selected'; ?>>Continuous Improvement Manager</option>
        <!-- Agrega el resto de opciones de la misma manera -->
    </select>
</div>

<div class="input-group">
    <label for="rol">Rol</label>
    <select name="rol" id="rol">
        <option value="">Seleccione una opción</option>
        <option value="Administrador" <?php if($rol == 'Administrador') echo 'selected'; ?>>Administrador</option>
        <option value="Aprobador" <?php if($rol == 'Aprobador') echo 'selected'; ?>>Aprobador</option>
        <option value="Digitador" <?php if($rol == 'Digitador') echo 'selected'; ?>>Digitador</option>
        <option value="Observador" <?php if($rol == 'Observador') echo 'selected'; ?>>Observador</option>
    </select>
</div>

                    <div class="buttons">
                        <input type="Submit" name="update" value="Editar" class="Registrarse">
                        <a href="http://localhost/GateGourmet/Gestor_usuarios/php/admin/index_gestor_admin.php" class="regresar">Volver</a>
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
