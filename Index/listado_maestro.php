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
$sql = "SELECT * FROM listado_maestro";

$sql = "SELECT PROCESO, CODIGO, TITULO_DOCUMENTO, TIPO, VERSION, ESTADO, FECHA_DE_APROBACION, AREAS, MOTIVO_DEL_CAMBIO, TIEMPO_DE_RETENCION, RESPONSABLE_DE_RETENCION, LUGAR_DE_ALMACENAMIENTO_FISICO, LUGAR_DE_ALMACENAMIENTO_MAGNETICO, CONSERVACION, DISPOSICION_FINAL, COPIAS_CONTROLADAS, FECHA_DE_VIGENCIA, DIAS, SENAL_ALERTA, OBSOLETO, ANULADO, EN_ACTUALIZACION FROM listado_maestro";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr>
            <th>PROCESO</th>
            <th>CODIGO</th>
            <th>TITULO_DOCUMENTO</th>
            <th>TIPO</th>
            <th>VERSION</th>
            <th>ESTADO</th>
            <th>FECHA DE APROBACION</th>
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
    while($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>" . $row["PROCESO"]. "</td>
                <td>" . $row["CODIGO"]. "</td>
                <td>" . $row["TITULO_DOCUMENTO"]. "</td>
                <td>" . $row["TIPO"]. "</td>
                <td>" . $row["VERSION"]. "</td>
                <td>" . $row["ESTADO"]. "</td>
                <td>" . $row["FECHA_DE_APROBACION"]. "</td>
                <td>" . $row["AREAS"]. "</td>
                <td>" . $row["MOTIVO_DEL_CAMBIO"]. "</td>
                <td>" . $row["TIEMPO_DE_RETENCION"]. "</td>
                <td>" . $row["RESPONSABLE_DE_RETENCION"]. "</td>
                <td>" . $row["LUGAR_DE_ALMACENAMIENTO_FISICO"]. "</td>
                <td>" . $row["LUGAR_DE_ALMACENAMIENTO_MAGNETICO"]. "</td>
                <td>" . $row["CONSERVACION"]. "</td>
                <td>" . $row["DISPOSICION_FINAL"]. "</td>
                <td>" . $row["COPIAS_CONTROLADAS"]. "</td>
                <td>" . $row["FECHA_DE_VIGENCIA"]. "</td>
                <td>" . $row["DIAS"]. "</td>
                <td>" . $row["SENAL_ALERTA"]. "</td>
                <td>" . ($row["OBSOLETO"] ? 'Sí' : 'No'). "</td>
                <td>" . ($row["ANULADO"] ? 'Sí' : 'No'). "</td>
                <td>" . ($row["EN_ACTUALIZACION"] ? 'Sí' : 'No'). "</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "0 resultados";
}
$conn->close();
?>
