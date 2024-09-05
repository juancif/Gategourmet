<?php
// Configurar conexión IMAP
$inbox = imap_open("{imap.gmail.com:993/imap/ssl}INBOX", "ngestordocumental@gmail.com", "tu_contraseña") or die('No se pudo conectar al servidor de correo: ' . imap_last_error());

// Obtener todos los correos
$emails = imap_search($inbox, 'ALL');

if ($emails) {
    // Ordenar correos del más nuevo al más antiguo
    rsort($emails);

    // Conectarse a la base de datos
    $conexion = new mysqli('localhost', 'root', '', 'gategourmet');

    if ($conexion->connect_error) {
        die("Error de conexión a la base de datos: " . $conexion->connect_error);
    }

    foreach ($emails as $email_number) {
        // Obtener información del correo
        $overview = imap_fetch_overview($inbox, $email_number, 0);
        $message = imap_fetchbody($inbox, $email_number, 1.1);
        
        // Datos del correo a almacenar
        $asunto = $overview[0]->subject;
        $remitente = $overview[0]->from;
        $fecha = $overview[0]->date;
        $cuerpo = imap_qprint($message); // Decodificar el cuerpo si es necesario

        // Insertar datos en la base de datos
        $sql = "INSERT INTO correos (asunto, remitente, fecha, cuerpo) VALUES (?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ssss", $asunto, $remitente, $fecha, $cuerpo);

        if ($stmt->execute()) {
            echo "Correo almacenado con éxito<br>";
        } else {
            echo "Error al almacenar el correo: " . $stmt->error . "<br>";
        }

        $stmt->close();
    }

    // Cerrar la conexión a la base de datos
    $conexion->close();
} else {
    echo "No hay correos para procesar.";
}

// Cerrar la conexión IMAP
imap_close($inbox);
?>
