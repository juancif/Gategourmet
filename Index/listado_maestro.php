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
            motivo_del_cambio,  -- Verifica este nombre de columna
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

if ($result->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr>
            <th>proceso</th>
            <th>codigo</th>
            <th>titulo documento</th>
            <th>tipo</th>
            <th>version</th>
            <th>estado</th>
            <th>fecha aprobacion</th>
            <th>areas</th>
            <th>motivo del cambio</th>  <!-- Verifica este nombre de columna -->
            <th>tiempo de retencion</th>
            <th>responsable de retencion</th>
            <th>lugar de almacenamiento fisico</th>
            <th>lugar de almacenamiento magnetico</th>
            <th>conservacion</th>
            <th>disposicion final</th>
            <th>copias controladas</th>
            <th>fecha de vigencia</th>
            <th>dias</th>
            <th>senal alerta</th>
            <th>obsoleto</th>
            <th>anulado</th>
            <th>en actualizacion</th>
          </tr>";

    while($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>" . htmlspecialchars($row["proceso"]). "</td>
                <td>" . htmlspecialchars($row["codigo"]). "</td>
                <td>" . htmlspecialchars($row["titulo_documento"]). "</td>
                <td>" . htmlspecialchars($row["tipo"]). "</td>
                <td>" . htmlspecialchars($row["version"]). "</td>
                <td>" . htmlspecialchars($row["estado"]). "</td>
                <td>" . htmlspecialchars($row["fecha_aprobacion"]). "</td>
                <td>" . htmlspecialchars($row["areas"]). "</td>
                <td>" . htmlspecialchars($row["motivo_del_cambio"]). "</td> <!-- Verifica este nombre de columna -->
                <td>" . htmlspecialchars($row["tiempo_de_retencion"]). "</td>
                <td>" . htmlspecialchars($row["responsable_de_retencion"]). "</td>
                <td>" . htmlspecialchars($row["lugar_de_almacenamiento_fisico"]). "</td>
                <td>" . htmlspecialchars($row["lugar_de_almacenamiento_magnetico"]). "</td>
                <td>" . htmlspecialchars($row["conservacion"]). "</td>
                <td>" . htmlspecialchars($row["disposicion_final"]). "</td>
                <td>" . htmlspecialchars($row["copias_controladas"]). "</td>
                <td>" . htmlspecialchars($row["fecha_de_vigencia"]). "</td>
                <td>" . htmlspecialchars($row["dias"]). "</td>
                <td>" . htmlspecialchars($row["senal_alerta"]). "</td>
                <td>" . ($row["obsoleto"] ? 'sí' : 'no'). "</td>
                <td>" . ($row["anulado"] ? 'sí' : 'no'). "</td>
                <td>" . ($row["en_actualizacion"] ? 'sí' : 'no'). "</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "0 resultados";
}

$conn->close();
?>

