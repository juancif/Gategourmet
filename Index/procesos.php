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
$sql = "SELECT PROCESO, CODIGO, TITULO_DOCUMENTO, TIPO, VERSION, ESTADO, FECHA_DE_APROBACION, AREAS, MOTIVO_DEL_CAMBIO, TIEMPO_DE_RETENCION, RESPONSABLE_DE_RETENCION, LUGAR_DE_ALMACENAMIENTO_FISICO, LUGAR_DE_ALMACENAMIENTO_MAGNETICO, CONSERVACION, DISPOSICION_FINAL, COPIAS_CONTROLADAS, FECHA_DE_VIGENCIA, DIAS, SENAL_ALERTA, OBSOLETO, ANULADO, EN_ACTUALIZACION FROM listado_maestro";
$result = $conn->query($sql);

// Comprobar si hay resultados
if ($result->num_rows > 0) {
    // Crear la tabla HTML
    echo "<table border='1'>";
    echo "<tr>
            <th>PROCESO</th>
            <th>CODIGO</th>
            <th>TITULO DOCUMENTO</th>
            <th>TIPO</th>
            <th>VERSION</th>
            <th>ESTADO</th>
            <th>FECHA APROBACION</th>
            <th>AREAS</th>
            <th>MOTIVO DEL CAMBIO</th>
            <th>TIEMPO DE RETENCION</th>
            <th>RESPONSABLE DE RETENCION</th>
            <th>LUGAR DE ALMACENAMIENTO FISICO</th>
            <th>LUGAR DE ALMACENAMIENTO MAGNETICO</th>
            <th>CONSERVACION</th>
            <th>DISPOSICION FINAL</th>
            <th>COPIAS CONTROLADAS</th>
            <th>FECHA DE VIGENCIA</th>
            <th>DIAS</th>
            <th>SENAL ALERTA</th>
            <th>OBSOLETO</th>
            <th>ANULADO</th>
            <th>EN ACTUALIZACION</th>
          </tr>";
    
    // Mostrar los datos en la tabla
    while($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>" . htmlspecialchars($row["PROCESO"]) . "</td>
                <td>" . htmlspecialchars($row["CODIGO"]) . "</td>
                <td>" . htmlspecialchars($row["TITULO_DOCUMENTO"]) . "</td>
                <td>" . htmlspecialchars($row["TIPO"]) . "</td>
                <td>" . htmlspecialchars($row["VERSION"]) . "</td>
                <td>" . htmlspecialchars($row["ESTADO"]) . "</td>
                <td>" . htmlspecialchars($row["FECHA_APROBACION"]) . "</td>
                <td>" . htmlspecialchars($row["AREAS"]) . "</td>
                <td>" . htmlspecialchars($row["MOTIVO_DEL_CAMBIO"]) . "</td>
                <td>" . htmlspecialchars($row["TIEMPO_DE_RETENCION"]) . "</td>
                <td>" . htmlspecialchars($row["RESPONSABLE_DE_RETENCION"]) . "</td>
                <td>" . htmlspecialchars($row["LUGAR_DE_ALMACENAMIENTO_FISICO"]) . "</td>
                <td>" . htmlspecialchars($row["LUGAR_DE_ALMACENAMIENTO_MAGNETICO"]) . "</td>
                <td>" . htmlspecialchars($row["CONSERVACION"]) . "</td>
                <td>" . htmlspecialchars($row["DISPOSICION_FINAL"]) . "</td>
                <td>" . htmlspecialchars($row["COPIAS_CONTROLADAS"]) . "</td>
                <td>" . htmlspecialchars($row["FECHA_DE_VIGENCIA"]) . "</td>
                <td>" . htmlspecialchars($row["DIAS"]) . "</td>
                <td>" . htmlspecialchars($row["SENAL_ALERTA"]) . "</td>
                <td>" . ($row["OBSOLETO"] ? 'Sí' : 'No') . "</td>
                <td>" . ($row["ANULADO"] ? 'Sí' : 'No') . "</td>
                <td>" . ($row["EN_ACTUALIZACION"] ? 'Sí' : 'No') . "</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "0 resultados";
}

// Cerrar la conexión
$conn->close();
?>
