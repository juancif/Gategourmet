<?php
include_once("config_inactivos.php");
session_start(); // Asegúrate de iniciar sesión para poder usar variables de sesión

if (!isset($_SESSION['nombre_usuario'])) {
    die("Usuario no autenticado.");
}

$usuario_logueado = $_SESSION['nombre_usuario']; // El usuario que está realizando la acción

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
            $table = '';
            if ($user['rol'] === 'Administrador') {
                $table = 'administradores';
            } elseif (in_array($user['rol'], ['Aprobador', 'Digitador', 'Observador'])) {
                $table = 'usuarios';
            } else {
                throw new Exception("Rol desconocido: " . $user['rol']);
            }

            $sqlInsert = "INSERT INTO $table (correo, nombres_apellidos, nombre_usuario, contrasena, area, cargo, rol, estado) 
                          VALUES (:correo, :nombres_apellidos, :nombre_usuario, :contrasena, :area, :cargo, :rol, 'Activo')";

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

            // Registrar acción en la tabla de movimientos
            $accion = ($user['rol'] === 'Administrador') 
                      ? "Activación de administrador: $nombre_usuario" 
                      : "Activación de usuario con rol {$user['rol']}: $nombre_usuario";
            
            $sqlMovimiento = "INSERT INTO movimientos (nombre_usuario, accion, fecha) VALUES (:nombre_usuario, :accion, NOW())";
            $stmtMovimiento = $dbConn->prepare($sqlMovimiento);
            $stmtMovimiento->bindParam(':nombre_usuario', $usuario_logueado); // Nombre de usuario que realizó la acción
            $stmtMovimiento->bindParam(':accion', $accion);
            $stmtMovimiento->execute();

            // Cometer transacción
            $dbConn->commit();

            // Redirigir o mostrar un mensaje de éxito
            header("Location: http://10.24.217.62/GateGourmet/Gestor_usuarios/php/Inactivos/index_inactivos.php?msg=Usuario activado correctamente");
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
