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

// Obtener el término de búsqueda y el campo de filtro
$query = $_GET['query'] ?? '';
$filtro = $_GET['filtro'] ?? '';

// Verificar si el campo de filtro es válido
$valid_fields = ['proceso', 'codigo', 'titulo_documento', 'tipo', 'version', 'estado', 'fecha_aprobacion', 'areas'];
if (!in_array($filtro, $valid_fields)) {
    die("Campo de filtro no válido.");
}

// Preparar la consulta
$sql = "SELECT DISTINCT $filtro FROM listado_maestro WHERE $filtro LIKE ? LIMIT 10";
$stmt = $conn->prepare($sql);
$like_query = '%' . $query . '%';
$stmt->bind_param('s', $like_query);
$stmt->execute();
$result = $stmt->get_result();

$suggestions = [];
while ($row = $result->fetch_assoc()) {
    $suggestions[] = $row[$filtro];
}

// Devolver las sugerencias en formato JSON
header('Content-Type: application/json');
echo json_encode($suggestions);

$conn->close();
