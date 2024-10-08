<?php
include_once("config_gestor.php");

// Iniciar sesión
session_start();
$usuario_sesion = $_SESSION['nombre_usuario']; // Cambia 'nombre_usuario' según cómo guardas el nombre de usuario en la sesión

// Función para generar un nombre de usuario único basado en el nombre y apellido
function generarNombreUsuario($nombre, $apellido, $dbConn) {
    // Usa la primera letra del nombre y el primer apellido
    $nombre_usuario = strtolower(substr($nombre, 0, 1) . $apellido);
    $i = 1; // Inicializar el contador para la generación de nombres únicos

    // Verifica si el nombre de usuario ya existe en administradores y usuarios
    do {
        $checkDocSql = "SELECT COUNT(*) FROM administradores WHERE nombre_usuario = :nombre_usuario 
                         UNION ALL 
                         SELECT COUNT(*) FROM usuarios WHERE nombre_usuario = :nombre_usuario";
        $checkDocQuery = $dbConn->prepare($checkDocSql);
        $checkDocQuery->bindParam(':nombre_usuario', $nombre_usuario);
        $checkDocQuery->execute();
        $count = array_sum($checkDocQuery->fetchAll(PDO::FETCH_COLUMN));

        if ($count > 0) {
            // Si el nombre de usuario ya existe, genera uno nuevo con las dos primeras letras del nombre y el apellido
            $nombre_usuario = strtolower(substr($nombre, 0, 2) . $apellido . $i);
            $i++;
        }
    } while ($count > 0);

    return $nombre_usuario;
}

// Función para verificar si el correo ya está en alguna de las tablas (usuarios, inactivos)
function verificarCorreoExistente($correo, $dbConn) {
    // Consultar si el correo existe en las tablas 'usuarios' o 'inactivos'
    $sql = "
        SELECT 'usuarios' AS tabla FROM usuarios WHERE correo = :correo
        UNION 
        SELECT 'inactivos' AS tabla FROM inactivos WHERE correo = :correo
    ";

    $query = $dbConn->prepare($sql);
    $query->bindParam(':correo', $correo);
    $query->execute();

    // Retorna el nombre de la tabla si el correo está registrado, o false si no lo está
    return $query->fetch(PDO::FETCH_ASSOC);
}

if (isset($_POST['Submit'])) {
    $correo = $_POST['correo'];
    $nombres_apellidos = $_POST['nombres_apellidos'];
    $nombre_usuario = !empty($_POST['nombre_usuario']) ? $_POST['nombre_usuario'] : null;
    $contrasena = $_POST['contrasena'];
    $area = $_POST['area'];
    $cargo = $_POST['cargo'];
    $rol = $_POST['rol'];

    // Verificar si algún campo está vacío (excepto nombre_usuario)
    if (empty($correo) || empty($nombres_apellidos) || empty($contrasena) || empty($area) || empty($cargo) || empty($rol)) {
        echo "<font color='red'>Por favor, completa todos los campos.</font><br/>";
        if (empty($correo)) echo "<font color='red'>Campo: correo está vacío.</font><br/>";
        if (empty($nombres_apellidos)) echo "<font color='red'>Campo: nombres_apellidos está vacío.</font><br/>";
        if (empty($contrasena)) echo "<font color='red'>Campo: contrasena está vacío.</font><br/>";
        if (empty($area)) echo "<font color='red'>Campo: área está vacío.</font><br/>";
        if (empty($cargo)) echo "<font color='red'>Campo: cargo está vacío.</font><br/>";
        if (empty($rol)) echo "<font color='red'>Campo: rol está vacío.</font><br/>";
        echo "<br/><a href='javascript:self.history.back();'>Volver</a>";
    } else {
        try {
            // Verificar si el correo ya está registrado en las tablas usuarios o inactivos
            $resultado = verificarCorreoExistente($correo, $dbConn);

            if ($resultado) {
                // El correo ya está registrado en una de las tablas
                $tabla = $resultado['tabla'];
                echo "<div style='color: red; border: 2px solid red; padding: 10px; background-color: #f8d7da; font-family: Arial, sans-serif;'>
                        <strong>Error:</strong> El correo electrónico <em>$correo</em> ya está registrado en la tabla <strong>$tabla</strong>.
                      </div>";
                echo "<br/><a href='javascript:self.history.back();'>Volver</a>";
            } else {
                // Iniciar la transacción
                $dbConn->beginTransaction();

                // Generar un nombre de usuario si no se proporcionó
                if (is_null($nombre_usuario)) {
                    list($nombre, $apellido) = explode(' ', $nombres_apellidos, 2);
                    $nombre_usuario = generarNombreUsuario($nombre, $apellido, $dbConn);
                }

                // Verificar el campo rol y definir la tabla correspondiente
                $sql = ($rol === 'Administrador')
                    ? "INSERT INTO administradores (correo, nombres_apellidos, nombre_usuario, contrasena, area, cargo, rol) 
                       VALUES (:correo, :nombres_apellidos, :nombre_usuario, :contrasena, :area, :cargo, :rol)"
                    : "INSERT INTO usuarios (correo, nombres_apellidos, nombre_usuario, contrasena, area, cargo, rol) 
                       VALUES (:correo, :nombres_apellidos, :nombre_usuario, :contrasena, :area, :cargo, :rol)";

                $query = $dbConn->prepare($sql);
                $query->bindParam(':correo', $correo);
                $query->bindParam(':nombres_apellidos', $nombres_apellidos);
                $query->bindParam(':nombre_usuario', $nombre_usuario);
                $query->bindParam(':contrasena', password_hash($contrasena, PASSWORD_BCRYPT)); // Hash de la contraseña
                $query->bindParam(':area', $area);
                $query->bindParam(':cargo', $cargo);
                $query->bindParam(':rol', $rol);
                $query->execute();

                $accion = ($rol === 'Administrador') 
                    ? "Adición de administrador: $nombre_usuario"
                    : "Adición de usuario con rol $rol: $nombre_usuario";

                // Registrar el movimiento en la tabla de movimientos
                $sql_movimiento = "INSERT INTO movimientos (nombre_usuario, accion) VALUES (:nombre_usuario, :accion)";
                $stmt_movimiento = $dbConn->prepare($sql_movimiento);
                $stmt_movimiento->bindParam(':nombre_usuario', $usuario_sesion); // Nombre de usuario que realizó el cambio
                $stmt_movimiento->bindParam(':accion', $accion);
                $stmt_movimiento->execute();

                // Cometer la transacción
                $dbConn->commit();

                // Redirigir a la página deseada después del registro exitoso
                header("Location: http://localhost/GateGourmet/Gestor_usuarios/php/admin/registro_exitoso_admin.php");
                exit();
            }
        } catch (Exception $e) {
            // Revertir los cambios si ocurre un error
            if ($dbConn->inTransaction()) {
                $dbConn->rollBack();
            }
            echo "<font color='red'>Error: " . $e->getMessage() . "</font><br/>";
        }
    }
}
?>
