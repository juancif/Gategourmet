<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gategourmet";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$campo = $_GET['campo'];
$query = $_GET['query'];

$sql = "SELECT DISTINCT $campo FROM listado_maestro WHERE $campo LIKE ? LIMIT 10";
$stmt = $conn->prepare($sql);
$searchTerm = '%' . $query . '%';
$stmt->bind_param('s', $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

$suggestions = [];
while ($row = $result->fetch_assoc()) {
    $suggestions[] = $row[$campo];
}

echo json_encode($suggestions);

$stmt->close();
$conn->close();
?>
