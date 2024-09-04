<?php
include_once("config_inactivos.php");

if (isset($_GET['nombre_usuario'])) {
    $nombre_usuario = $_GET['nombre_usuario'];

    try {
        // Iniciar transacción
        $dbConn->beginTransaction();

        // Obtener datos del usuario a activar
        $sqlSelect = "SELECT * FROM inactivos WHERE nombre_usuario = :nombre_usuario";
        $stmtSelect = $dbConn->prepare($sqlSelect);
        $stmtSelect->bindParam(':nombre_usuario', $nombre_usuario);
        $stmtSelect->execute();
        $user = $stmtSelect->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Dependiendo del rol, insertar en la tabla correspondiente
            if ($user['rol'] === 'Administrador') {
                $sqlInsert = "INSERT INTO administradores (correo, nombres_apellidos, nombre_usuario, contrasena, area, cargo, rol, estado) 
                              VALUES (:correo, :nombres_apellidos, :nombre_usuario, :contrasena, :area, :cargo, :rol, 'Activo')";
            } elseif (in_array($user['rol'], ['Aprobador', 'Digitador', 'Observador'])) {
                $sqlInsert = "INSERT INTO usuarios (correo, nombres_apellidos, nombre_usuario, contrasena, area, cargo, rol, estado) 
                              VALUES (:correo, :nombres_apellidos, :nombre_usuario, :contrasena, :area, :cargo, :rol, 'Activo')";
            } else {
                throw new Exception("Rol desconocido: " . $user['rol']);
            }

            $stmtInsert = $dbConn->prepare($sqlInsert);
            $stmtInsert->bindParam(':correo', $user['correo']);
            $stmtInsert->bindParam(':nombres_apellidos', $user['nombres_apellidos']);
            $stmtInsert->bindParam(':nombre_usuario', $user['nombre_usuario']);
            $stmtInsert->bindParam(':contrasena', $user['contrasena']);
            $stmtInsert->bindParam(':area', $user['area']);
            $stmtInsert->bindParam(':cargo', $user['cargo']);
            $stmtInsert->bindParam(':rol', $user['rol']);
            $stmtInsert->execute();

            // Eliminar de la tabla inactivos
            $sqlDelete = "DELETE FROM inactivos WHERE nombre_usuario = :nombre_usuario";
            $stmtDelete = $dbConn->prepare($sqlDelete);
            $stmtDelete->bindParam(':nombre_usuario', $nombre_usuario);
            $stmtDelete->execute();

            // Cometer transacción
            $dbConn->commit();

            // Redirigir o mostrar un mensaje de éxito
            header("Location: http://localhost/GateGourmet/Gestor_usuarios/php/Inactivos/index_inactivos.php?msg=Usuario activado correctamente");
            exit();
        } else {
            throw new Exception("Usuario no encontrado en inactivos");
        }
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        if ($dbConn->inTransaction()) {
            $dbConn->rollBack();
        }
        echo "<font color='red'>Error: " . $e->getMessage() . "</font><br/>";
    }
} else {
    echo "<font color='red'>No se ha especificado el nombre de usuario.</font><br/>";
}
?>
