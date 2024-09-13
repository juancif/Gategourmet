<?php
include_once("config_gestor.php");

if (isset($_GET['nombre_usuario'])) {
    $nombre_usuario = $_GET['nombre_usuario'];

    try {
        // Iniciar transacción
        $dbConn->beginTransaction();

        // Obtener datos del usuario a desactivar
        $sqlSelect = "SELECT * FROM administradores WHERE nombre_usuario = :nombre_usuario";
        $stmtSelect = $dbConn->prepare($sqlSelect);
        $stmtSelect->bindParam(':nombre_usuario', $nombre_usuario);
        $stmtSelect->execute();
        $user = $stmtSelect->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Insertar en la tabla inactivos
            $sqlInsert = "INSERT INTO inactivos (correo, nombres_apellidos, nombre_usuario, contrasena, area, cargo, rol, estado) 
                          VALUES (:correo, :nombres_apellidos, :nombre_usuario, :contrasena, :area, :cargo, :rol, 'Inactivo')";
            $stmtInsert = $dbConn->prepare($sqlInsert);
            $stmtInsert->bindParam(':correo', $user['correo']);
            $stmtInsert->bindParam(':nombres_apellidos', $user['nombres_apellidos']);
            $stmtInsert->bindParam(':nombre_usuario', $user['nombre_usuario']);
            $stmtInsert->bindParam(':contrasena', $user['contrasena']);
            $stmtInsert->bindParam(':area', $user['area']);
            $stmtInsert->bindParam(':cargo', $user['cargo']);
            $stmtInsert->bindParam(':rol', $user['rol']);  // Asegúrate de que se está pasando el rol
            $stmtInsert->execute();

            // Eliminar de la tabla administradores
            $sqlDelete = "DELETE FROM administradores WHERE nombre_usuario = :nombre_usuario";
            $stmtDelete = $dbConn->prepare($sqlDelete);
            $stmtDelete->bindParam(':nombre_usuario', $nombre_usuario);
            $stmtDelete->execute();

            $accion = ($user['rol'] === 'Administrador') 
            ? "Desactivación de administrador: $nombre_usuario"
            : "Desactivación de usuario con rol {$user['rol']}: $nombre_usuario";
            
            $sql_movimiento = "INSERT INTO movimientos (nombre_usuario, accion) VALUES (:nombre_usuario, :accion)";
            $stmt_movimiento = $dbConn->prepare($sql_movimiento);
            $stmt_movimiento->bindParam(':nombre_usuario', $nombre_usuario);
            $stmt_movimiento->bindParam(':accion', $accion);
            $stmt_movimiento->execute();
            // Cometer transacción
            $dbConn->commit();

            // Redirigir o mostrar un mensaje de éxito
            header("Location: http://localhost/GateGourmet/Gestor_usuarios/php/admin/index_gestor_admin.php?msg=Usuario desactivado correctamente ");
            exit();
        } else {
            throw new Exception("Usuario no encontrado");
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
