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

// Inicializar variables para cada campo de filtro
$proceso = $_POST['proceso'] ?? '';
$codigo = $_POST['codigo'] ?? '';
$titulo_documento = $_POST['titulo_documento'] ?? '';
$tipo = $_POST['tipo'] ?? '';
$version = $_POST['version'] ?? '';
$estado = $_POST['estado'] ?? '';
$fecha_aprobacion = $_POST['fecha_aprobacion'] ?? '';
$areas = $_POST['areas'] ?? '';
$motivo_del_cambio = $_POST['motivo_del_cambio'] ?? '';
$tiempo_de_retencion = $_POST['tiempo_de_retencion'] ?? '';
$responsable_de_retencion = $_POST['responsable_de_retencion'] ?? '';
$lugar_de_almacenamiento_fisico = $_POST['lugar_de_almacenamiento_fisico'] ?? '';
$lugar_de_almacenamiento_magnetico = $_POST['lugar_de_almacenamiento_magnetico'] ?? '';
$conservacion = $_POST['conservacion'] ?? '';
$disposicion_final = $_POST['disposicion_final'] ?? '';
$copias_controladas = $_POST['copias_controladas'] ?? '';
$fecha_de_vigencia = $_POST['fecha_de_vigencia'] ?? '';
$dias = $_POST['dias'] ?? '';
$senal_alerta = $_POST['senal_alerta'] ?? '';
$obsoleto = $_POST['obsoleto'] ?? '';
$anulado = $_POST['anulado'] ?? '';
$en_actualizacion = $_POST['en_actualizacion'] ?? '';

// Construir la consulta SQL base
$sql = "SELECT 
            proceso, codigo, titulo_documento, tipo, version, estado, 
            fecha_aprobacion, areas, motivo_del_cambio, tiempo_de_retencion, 
            responsable_de_retencion, lugar_de_almacenamiento_fisico, 
            lugar_de_almacenamiento_magnetico, conservacion, disposicion_final, 
            copias_controladas, fecha_de_vigencia, dias, senal_alerta, 
            obsoleto, anulado, en_actualizacion 
        FROM listado_maestro 
        WHERE 1=1"; // Utilizar 1=1 para facilitar la concatenación de condiciones

// Condicionalmente añadir filtros a la consulta
$params = [];
$types = '';
if (!empty($proceso)) {
    $sql .= " AND proceso LIKE ?";
    $params[] = "%$proceso%";
    $types .= 's';
}
if (!empty($codigo)) {
    $sql .= " AND codigo LIKE ?";
    $params[] = "%$codigo%";
    $types .= 's';
}
if (!empty($titulo_documento)) {
    $sql .= " AND titulo_documento LIKE ?";
    $params[] = "%$titulo_documento%";
    $types .= 's';
}
if (!empty($tipo)) {
    $sql .= " AND tipo LIKE ?";
    $params[] = "%$tipo%";
    $types .= 's';
}
if (!empty($version)) {
    $sql .= " AND version LIKE ?";
    $params[] = "%$version%";
    $types .= 's';
}
if (!empty($estado)) {
    $sql .= " AND estado LIKE ?";
    $params[] = "%$estado%";
    $types .= 's';
}
if (!empty($fecha_aprobacion)) {
    $sql .= " AND fecha_aprobacion LIKE ?";
    $params[] = "%$fecha_aprobacion%";
    $types .= 's';
}
if (!empty($areas)) {
    $sql .= " AND areas LIKE ?";
    $params[] = "%$areas%";
    $types .= 's';
}
if (!empty($motivo_del_cambio)) {
    $sql .= " AND motivo_del_cambio LIKE ?";
    $params[] = "%$motivo_del_cambio%";
    $types .= 's';
}
if (!empty($tiempo_de_retencion)) {
    $sql .= " AND tiempo_de_retencion LIKE ?";
    $params[] = "%$tiempo_de_retencion%";
    $types .= 's';
}
if (!empty($responsable_de_retencion)) {
    $sql .= " AND responsable_de_retencion LIKE ?";
    $params[] = "%$responsable_de_retencion%";
    $types .= 's';
}
if (!empty($lugar_de_almacenamiento_fisico)) {
    $sql .= " AND lugar_de_almacenamiento_fisico LIKE ?";
    $params[] = "%$lugar_de_almacenamiento_fisico%";
    $types .= 's';
}
if (!empty($lugar_de_almacenamiento_magnetico)) {
    $sql .= " AND lugar_de_almacenamiento_magnetico LIKE ?";
    $params[] = "%$lugar_de_almacenamiento_magnetico%";
    $types .= 's';
}
if (!empty($conservacion)) {
    $sql .= " AND conservacion LIKE ?";
    $params[] = "%$conservacion%";
    $types .= 's';
}
if (!empty($disposicion_final)) {
    $sql .= " AND disposicion_final LIKE ?";
    $params[] = "%$disposicion_final%";
    $types .= 's';
}
if (!empty($copias_controladas)) {
    $sql .= " AND copias_controladas LIKE ?";
    $params[] = "%$copias_controladas%";
    $types .= 's';
}
if (!empty($fecha_de_vigencia)) {
    $sql .= " AND fecha_de_vigencia LIKE ?";
    $params[] = "%$fecha_de_vigencia%";
    $types .= 's';
}
if (!empty($dias)) {
    $sql .= " AND dias LIKE ?";
    $params[] = "%$dias%";
    $types .= 'i'; // Para enteros usamos 'i'
}
if (!empty($senal_alerta)) {
    $sql .= " AND senal_alerta LIKE ?";
    $params[] = "%$senal_alerta%";
    $types .= 's';
}
if (!empty($obsoleto)) {
    $sql .= " AND obsoleto LIKE ?";
    $params[] = "%$obsoleto%";
    $types .= 's';
}
if (!empty($anulado)) {
    $sql .= " AND anulado LIKE ?";
    $params[] = "%$anulado%";
    $types .= 's';
}
if (!empty($en_actualizacion)) {
    $sql .= " AND en_actualizacion LIKE ?";
    $params[] = "%$en_actualizacion%";
    $types .= 's';
}

// Preparar y ejecutar la consulta
$stmt = $conn->prepare($sql);

// Vincular los parámetros dinámicamente
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
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
    <link rel="stylesheet" href="listado_maestro.css">
</head>
<body>
    <header class="header">
        <h1>LISTADO MAESTRO</h1>
    </header>

    <div class="search-bar">
        <form method="post">
            <input type="text" name="proceso" placeholder="Proceso" value="<?php echo htmlspecialchars($proceso); ?>">
            <input type="text" name="codigo" placeholder="Código" value="<?php echo htmlspecialchars($codigo); ?>">
            <input type="text" name="titulo_documento" placeholder="Título Documento" value="<?php echo htmlspecialchars($titulo_documento); ?>">
            <input type="text" name="tipo" placeholder="Tipo" value="<?php echo htmlspecialchars($tipo); ?>">
            <input type="text" name="version" placeholder="Versión" value="<?php echo htmlspecialchars($version); ?>">
            <input type="text" name="estado" placeholder="Estado" value="<?php echo htmlspecialchars($estado); ?>">
            <input type="text" name="fecha_aprobacion" placeholder="Fecha Aprobación" value="<?php echo htmlspecialchars($fecha_aprobacion); ?>">
            <input type="text" name="areas" placeholder="Áreas" value="<?php echo htmlspecialchars($areas); ?>">
            <input type="text" name="motivo_del_cambio" placeholder="Motivo del Cambio" value="<?php echo htmlspecialchars($motivo_del_cambio); ?>">
            <input type="text" name="tiempo_de_retencion" placeholder="Tiempo de Retención" value="<?php echo htmlspecialchars($tiempo_de_retencion); ?>">
            <input type="text" name="responsable_de_retencion" placeholder="Responsable de Retención" value="<?php echo htmlspecialchars($responsable_de_retencion); ?>">
            <input type="text" name="lugar_de_almacenamiento_fisico" placeholder="Lugar de Almacenamiento Físico" value="<?php echo htmlspecialchars($lugar_de_almacenamiento_fisico); ?>">
            <input type="text" name="lugar_de_almacenamiento_magnetico" placeholder="Lugar de Almacenamiento Magnético" value="<?php echo htmlspecialchars($lugar_de_almacenamiento_magnetico); ?>">
            <input type="text" name="conservacion" placeholder="Conservación" value="<?php echo htmlspecialchars($conservacion); ?>">
            <input type="text" name="disposicion_final" placeholder="Disposición Final" value="<?php echo htmlspecialchars($disposicion_final); ?>">
            <input type="text" name="copias_controladas" placeholder="Copias Controladas" value="<?php echo htmlspecialchars($copias_controladas); ?>">
            <input type="text" name="fecha_de_vigencia" placeholder="Fecha de Vigencia" value="<?php echo htmlspecialchars($fecha_de_vigencia); ?>">
            <input type="text" name="dias" placeholder="Días" value="<?php echo htmlspecialchars($dias); ?>">
            <input type="text" name="senal_alerta" placeholder="Señal Alerta" value="<?php echo htmlspecialchars($senal_alerta); ?>">
            <input type="text" name="obsoleto" placeholder="Obsoleto" value="<?php echo htmlspecialchars($obsoleto); ?>">
            <input type="text" name="anulado" placeholder="Anulado" value="<?php echo htmlspecialchars($anulado); ?>">
            <input type="text" name="en_actualizacion" placeholder="En Actualización" value="<?php echo htmlspecialchars($en_actualizacion); ?>">
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
                            <th>Señal Alerta</th>
                            <th>Obsoleto</th>
                            <th>Anulado</th>
                            <th>En Actualización</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): 
                            $rowClass = '';

                            // Definir la clase de la fila basada en el estado
                            if (strtolower($row['estado']) == 'vigente') {
                                $rowClass = 'vigente';
                            } elseif (strtolower($row['estado']) == 'desactualizado') {
                                $rowClass = 'desactualizado';
                            } elseif (strtolower($row['estado']) == 'obsoleto') {
                                $rowClass = 'obsoleto';
                            }
                        ?>
                        <tr class="<?php echo $rowClass; ?>">
                            <td><?php echo htmlspecialchars($row['proceso']); ?></td>
                            <td><?php echo htmlspecialchars($row['codigo']); ?></td>
                            <td><?php echo htmlspecialchars($row['titulo_documento']); ?></td>
                            <td><?php echo htmlspecialchars($row['tipo']); ?></td>
                            <td><?php echo htmlspecialchars($row['version']); ?></td>
                            <td><?php echo htmlspecialchars($row['estado']); ?></td>
                            <td><?php echo htmlspecialchars($row['fecha_aprobacion'] ?? 'No disponible'); ?></td>
                            <td><?php echo htmlspecialchars($row['areas']); ?></td>
                            <td><?php echo htmlspecialchars($row['motivo_del_cambio']); ?></td>
                            <td><?php echo htmlspecialchars($row['tiempo_de_retencion']); ?></td>
                            <td><?php echo htmlspecialchars($row['responsable_de_retencion']); ?></td>
                            <td><?php echo htmlspecialchars($row['lugar_de_almacenamiento_fisico']); ?></td>
                            <td><?php echo htmlspecialchars($row['lugar_de_almacenamiento_magnetico']); ?></td>
                            <td><?php echo htmlspecialchars($row['conservacion']); ?></td>
                            <td><?php echo htmlspecialchars($row['disposicion_final']); ?></td>
                            <td><?php echo htmlspecialchars($row['copias_controladas']); ?></td>
                            <td><?php echo htmlspecialchars($row['fecha_de_vigencia'] ?? 'No disponible'); ?></td>
                            <td><?php echo htmlspecialchars($row['dias']); ?></td>
                            <td><?php echo htmlspecialchars($row['senal_alerta']); ?></td>
                            <td><?php echo htmlspecialchars($row['obsoleto']); ?></td>
                            <td><?php echo htmlspecialchars($row['anulado']); ?></td>
                            <td><?php echo htmlspecialchars($row['en_actualizacion']); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <p>No se encontraron documentos.</p>
    <?php endif; ?>

    <?php
    // Cerrar conexión
    $stmt->close();
    $conn->close();
    ?>
</body>
</html>
