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

// Obtener el término de búsqueda y el campo
$query = $_GET['query'] ?? '';
$filtro = $_GET['filtro'] ?? '';

// Validar el término de búsqueda
if (!empty($query) && !empty($filtro)) {
    // Preparar la consulta para buscar coincidencias
    $sql = "SELECT DISTINCT $filtro FROM listado_maestro WHERE $filtro LIKE ? LIMIT 10";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $searchTerm = "$query%";
        $stmt->bind_param('s', $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();

        // Recopilar sugerencias
        $suggestions = [];
        while ($row = $result->fetch_assoc()) {
            $suggestions[] = $row[$filtro];
        }

        // Devolver sugerencias en formato JSON
        echo json_encode($suggestions);
        $stmt->close();
    } else {
        echo json_encode([]);
    }
}

$conn->close();
?>
