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

// Procesar la barra de búsqueda
$searchTerm = '';
if (isset($_POST['search'])) {
    $searchTerm = $_POST['search'];
}

// Consultar datos con filtro de búsqueda
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

if (!empty($searchTerm)) {
    $sql .= " WHERE 
                proceso LIKE ? OR 
                codigo LIKE ? OR 
                titulo_documento LIKE ? OR 
                tipo LIKE ? OR 
                version LIKE ? OR 
                estado LIKE ? OR 
                fecha_aprobacion LIKE ? OR 
                areas LIKE ? OR 
                motivo_del_cambio LIKE ? OR 
                tiempo_de_retencion LIKE ? OR 
                responsable_de_retencion LIKE ? OR 
                lugar_de_almacenamiento_fisico LIKE ? OR 
                lugar_de_almacenamiento_magnetico LIKE ? OR 
                conservacion LIKE ? OR 
                disposicion_final LIKE ? OR 
                copias_controladas LIKE ? OR 
                fecha_de_vigencia LIKE ? OR 
                dias LIKE ? OR 
                senal_alerta LIKE ? OR 
                obsoleto LIKE ? OR 
                anulado LIKE ? OR 
                en_actualizacion LIKE ?";
}

$stmt = $conn->prepare($sql);

if (!empty($searchTerm)) {
    $searchTerm = "%$searchTerm%";
    $stmt->bind_param(
        'ssssssssssssssssssssss', 
        $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, 
        $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, 
        $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, 
        $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, 
        $searchTerm, $searchTerm
    );
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
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
    </header>

    <div class="search-bar">
        <form method="post">
            <input type="text" name="search" placeholder="Buscar..." value="<?php echo htmlspecialchars($searchTerm); ?>">
            <button type="submit">Buscar</button>
        </form>
    </div>

    <?php if ($result->num_rows > 0): ?>
        <div class="container">
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
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <?php
                            // Convertir la fecha de vigencia a un objeto DateTime
                            $fecha_vigencia = new DateTime($row["fecha_de_vigencia"]);
                            $fecha_actual = new DateTime();

                            // Calcular la diferencia en días
                            $diferencia = $fecha_actual->diff($fecha_vigencia)->days;

                            // Asignar la clase CSS según la fecha de vigencia y el estado
                            if ($fecha_actual > $fecha_vigencia || strtolower($row["estado"]) == 'obsoleto') {
                                $clase = 'obsoleto'; // Documento obsoleto (gris)
                            } elseif ($diferencia <= 10) {
                                $clase = 'desactualizado'; // Documento desactualizado (rojo)
                            } else {
                                $clase = 'vigente'; // Documento vigente (azul)
                            }
                            ?>
                            <tr class="<?php echo $clase; ?>">
                                <td><?php echo htmlspecialchars($row["proceso"]); ?></td>
                                <td><?php echo htmlspecialchars($row["codigo"]); ?></td>
                                <td><?php echo htmlspecialchars($row["titulo_documento"]); ?></td>
                                <td><?php echo htmlspecialchars($row["tipo"]); ?></td>
                                <td><?php echo htmlspecialchars($row["version"]); ?></td>
                                <td><?php echo htmlspecialchars($row["estado"]); ?></td>
                                <td><?php echo htmlspecialchars($row["fecha_aprobacion"]); ?></td>
                                <td><?php echo htmlspecialchars($row["areas"]); ?></td>
                                <td><?php echo htmlspecialchars($row["motivo_del_cambio"]); ?></td>
                                <td><?php echo htmlspecialchars($row["tiempo_de_retencion"]); ?></td>
                                <td><?php echo htmlspecialchars($row["responsable_de_retencion"]); ?></td>
                                <td><?php echo htmlspecialchars($row["lugar_de_almacenamiento_fisico"]); ?></td>
                                <td><?php echo htmlspecialchars($row["lugar_de_almacenamiento_magnetico"]); ?></td>
                                <td><?php echo htmlspecialchars($row["conservacion"]); ?></td>
                                <td><?php echo htmlspecialchars($row["disposicion_final"]); ?></td>
                                <td><?php echo htmlspecialchars($row["copias_controladas"]); ?></td>
                                <td><?php echo htmlspecialchars($row["fecha_de_vigencia"]); ?></td>
                                <td><?php echo htmlspecialchars($row["dias"]); ?></td>
                                <td><?php echo htmlspecialchars($row["senal_alerta"]); ?></td>
                                <td><?php echo ($row["obsoleto"] ? "Sí" : "No"); ?></td>
                                <td><?php echo ($row["anulado"] ? "Sí" : "No"); ?></td>
                                <td><?php echo ($row["en_actualizacion"] ? "Sí" : "No"); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <div class="container"><p>No se encontraron resultados.</p></div>
    <?php endif; ?>

    <?php $conn->close(); ?>

</body>
</html>
