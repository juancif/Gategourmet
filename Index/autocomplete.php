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

$query = $_GET['query'] ?? '';
$filtro = $_GET['filtro'] ?? '';

if (empty($query) || empty($filtro)) {
    echo json_encode([]);
    exit;
}

// Validar el filtro contra una lista de campos permitidos
$allowedFilters = ['proceso', 'codigo', 'titulo_documento', 'tipo', 'version', 'estado', 'fecha_aprobacion', 'areas'];
if (!in_array($filtro, $allowedFilters)) {
    echo json_encode([]); // Devolver una respuesta vacía si el filtro no es válido
    exit;
}

// Consultar la base de datos
$sql = "SELECT DISTINCT $filtro FROM listado_maestro WHERE $filtro LIKE ? LIMIT 10";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Error al preparar la consulta: " . $conn->error);
}

$param = "%$query%";
$stmt->bind_param('s', $param);

// Ejecutar la consulta
if (!$stmt->execute()) {
    die("Error al ejecutar la consulta: " . $stmt->error);
}

$result = $stmt->get_result();
$suggestions = [];

while ($row = $result->fetch_assoc()) {
    $suggestions[] = $row[$filtro];
}

// Cerrar la conexión
$stmt->close();
$conn->close();

// Configurar el encabezado y devolver los resultados en formato JSON
header('Content-Type: application/json');
echo json_encode($suggestions);
?>
