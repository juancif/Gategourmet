<?php
include_once("config_inactivos.php");

if (isset($_GET['nombre_usuario'])) {
    $nombre_usuario = $_GET['nombre_usuario'];

    try {
        // Iniciar transacción
        $dbConn->beginTransaction();

        // Obtener datos del usuario a eliminar
        $sqlSelect = "SELECT * FROM inactivos WHERE nombre_usuario = :nombre_usuario";
        $stmtSelect = $dbConn->prepare($sqlSelect);
        $stmtSelect->bindParam(':nombre_usuario', $nombre_usuario);
        $stmtSelect->execute();
        $user = $stmtSelect->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Eliminar de la tabla administradores
            $sqlDelete = "DELETE FROM inactivos WHERE nombre_usuario = :nombre_usuario";
            $stmtDelete = $dbConn->prepare($sqlDelete);
            $stmtDelete->bindParam(':nombre_usuario', $nombre_usuario);
            $stmtDelete->execute();

            $accion = '';
            if ($user['rol'] === 'Administrador') {
                $accion = "Eliminación de administrador: $nombre_usuario";
            } elseif (in_array($user['rol'], ['Aprobador', 'Digitador', 'Observador'])) {
                $accion = "Eliminación de usuario con rol {$user['rol']}: $nombre_usuario";
            } else {
                $accion = "Eliminación de usuario con rol desconocido: $nombre_usuario";
            }
            
            $sqlMovimiento = "INSERT INTO movimientos (nombre_usuario, accion) VALUES (:nombre_usuario, :accion)";
            $stmtMovimiento = $dbConn->prepare($sqlMovimiento);
            $stmtMovimiento->bindParam(':nombre_usuario', $nombre_usuario);
            $stmtMovimiento->bindParam(':accion', $accion);
            $stmtMovimiento->execute();

            // Cometer transacción
            $dbConn->commit();

            // Redirigir o mostrar un mensaje de éxito
            header("Location: http://localhost/GateGourmet/Gestor_usuarios/php/Inactivos/index_inactivos.php?msg=Usuario eliminado correctamente");
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
