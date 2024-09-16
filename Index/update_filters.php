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

// Obtener el área
$area = $_GET['area'] ?? '';

if (!empty($area)) {
    // Preparar la consulta para buscar documentos con el área especificada
    $sql = "SELECT DISTINCT proceso, codigo, titulo_documento, tipo, version, estado, fecha_aprobacion
            FROM listado_maestro WHERE areas = ?";

    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param('s', $area);
        $stmt->execute();
        $result = $stmt->get_result();

        // Recopilar los valores de los filtros
        $filters = [
            'proceso' => '', 'codigo' => '', 'titulo_documento' => '', 'tipo' => '', 'version' => '',
            'estado' => '', 'fecha_aprobacion' => ''
        ];

        while ($row = $result->fetch_assoc()) {
            foreach ($filters as $key => &$value) {
                if (!empty($row[$key])) {
                    $value = $row[$key];
                }
            }
        }

        // Devolver los valores de los filtros en formato JSON
        echo json_encode($filters);
        $stmt->close();
    } else {
        echo json_encode([]);
    }
}

$conn->close();
?>
