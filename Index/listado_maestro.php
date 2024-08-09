<?php
// Configuración de la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gategourmet";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("La conexión falló: " . $conn->connect_error);
}

// Consultar datos
$sql = "SELECT 
            proceso, 
            codigo, 
            titulo_documento, 
            tipo, 
            version, 
            estado, 
            fecha_aprobacion, 
            areas, 
            motivo_del_cambio, 
            tiempo_de_retencion, 
            responsable_de_retencion, 
            lugar_de_almacenamiento_fisico, 
            lugar_de_almacenamiento_magnetico, 
            conservacion, 
            disposicion_final, 
            copias_controladas, 
            fecha_de_vigencia, 
            dias, 
            senal_alerta, 
            obsoleto, 
            anulado, 
            en_actualizacion 
        FROM listado_maestro";

$result = $conn->query($sql);

// Iniciar salida de HTML
echo '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado Maestro</title>
    <link rel="stylesheet" href="listado_maestro.css"> <!-- Enlaza un archivo CSS externo -->
</head>
<body>
    <header class="header">
        <h1>LISTADO MAESTRO</h1>
    </header>';

if ($result->num_rows > 0) {
    echo '<div class="container">
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Proceso</th>
                            <th>Código</th>
                            <th>Título Documento</th>
                            <th>Tipo</th>
                            <th>Versión</th>
                            <th>Estado</th>
                            <th>Fecha Aprobación</th>
                            <th>Áreas</th>
                            <th>Motivo del Cambio</th>
                            <th>Tiempo de Retención</th>
                            <th>Responsable de Retención</th>
                            <th>Lugar de Almacenamiento Físico</th>
                            <th>Lugar de Almacenamiento Magnético</th>
                            <th>Conservación</th>
                            <th>Disposición Final</th>
                            <th>Copias Controladas</th>
                            <th>Fecha de Vigencia</th>
                            <th>Días</th>
                            <th>Señal de Alerta</th>
                            <th>Obsoleto</th>
                            <th>Anulado</th>
                            <th>En Actualización</th>
                        </tr>
                    </thead>
                    <tbody>';

    while($row = $result->fetch_assoc()) {
        echo '<tr>
                <td>' . htmlspecialchars($row["proceso"]) . '</td>
                <td>' . htmlspecialchars($row["codigo"]) . '</td>
                <td>' . htmlspecialchars($row["titulo_documento"]) . '</td>
                <td>' . htmlspecialchars($row["tipo"]) . '</td>
                <td>' . htmlspecialchars($row["version"]) . '</td>
                <td>' . htmlspecialchars($row["estado"]) . '</td>
                <td>' . htmlspecialchars($row["fecha_aprobacion"]) . '</td>
                <td>' . htmlspecialchars($row["areas"]) . '</td>
                <td>' . htmlspecialchars($row["motivo_del_cambio"]) . '</td>
                <td>' . htmlspecialchars($row["tiempo_de_retencion"]) . '</td>
                <td>' . htmlspecialchars($row["responsable_de_retencion"]) . '</td>
                <td>' . htmlspecialchars($row["lugar_de_almacenamiento_fisico"]) . '</td>
                <td>' . htmlspecialchars($row["lugar_de_almacenamiento_magnetico"]) . '</td>
                <td>' . htmlspecialchars($row["conservacion"]) . '</td>
                <td>' . htmlspecialchars($row["disposicion_final"]) . '</td>
                <td>' . htmlspecialchars($row["copias_controladas"]) . '</td>
                <td>' . htmlspecialchars($row["fecha_de_vigencia"]) . '</td>
                <td>' . htmlspecialchars($row["dias"]) . '</td>
                <td>' . htmlspecialchars($row["senal_alerta"]) . '</td>
                <td>' . ($row["obsoleto"] ? 'Sí' : 'No') . '</td>
                <td>' . ($row["anulado"] ? 'Sí' : 'No') . '</td>
                <td>' . ($row["en_actualizacion"] ? 'Sí' : 'No') . '</td>
              </tr>';
    }

    echo '</tbody>
        </table>
    </div>
</div>';
} else {
    echo '<div class="container"><p>No se encontraron resultados.</p></div>';
}

// Cerrar conexión
$conn->close();

echo '</body>
</html>';
?>
