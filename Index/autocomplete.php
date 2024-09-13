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

// Obtener el término de búsqueda y el campo seleccionado
$query = $_GET['query'] ?? '';
$filtro = $_GET['filtro'] ?? '';

// Validar que se recibió una consulta
if (!empty($query) && !empty($filtro)) {
    // Preparar la consulta para buscar coincidencias en múltiples columnas
    $sql = "SELECT DISTINCT proceso, codigo, titulo_documento, tipo, version, estado 
            FROM listado_maestro 
            WHERE $filtro LIKE ? LIMIT 10";

    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $searchTerm = "$query%";
        $stmt->bind_param('s', $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();

        // Recopilar sugerencias
        $suggestions = [];
        while ($row = $result->fetch_assoc()) {
            $suggestions[] = [
                'proceso' => $row['proceso'],
                'codigo' => $row['codigo'],
                'titulo_documento' => $row['titulo_documento'],
                'tipo' => $row['tipo'],
                'version' => $row['version'],
                'estado' => $row['estado']
            ];
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
<script>
// Función para manejar la actualización de los filtros restantes
function updateFilters(filtro, query) {
    // Realizar una solicitud AJAX al servidor
    fetch(`buscar_documentos.php?filtro=${filtro}&query=${query}`)
        .then(response => response.json())
        .then(data => {
            // Limpiar los valores de los demás campos
            clearOtherFilters(filtro);

            // Procesar los datos y llenar los otros filtros
            data.forEach(item => {
                document.getElementById('proceso').value = item.proceso || '';
                document.getElementById('codigo').value = item.codigo || '';
                document.getElementById('titulo_documento').value = item.titulo_documento || '';
                document.getElementById('tipo').value = item.tipo || '';
                document.getElementById('version').value = item.version || '';
                document.getElementById('estado').value = item.estado || '';
            });
        });
}

// Función para limpiar los demás filtros cuando cambia uno
function clearOtherFilters(excludeFilter) {
    const filters = ['proceso', 'codigo', 'titulo_documento', 'tipo', 'version', 'estado'];
    filters.forEach(filter => {
        if (filter !== excludeFilter) {
            document.getElementById(filter).value = '';
        }
    });
}

// Agregar eventos a los campos de búsqueda
document.getElementById('areas').addEventListener('input', function() {
    const areaValue = this.value;
    if (areaValue) {
        updateFilters('areas', areaValue);  // Llamar a la función de actualización con el filtro "areas"
    }
});

document.getElementById('proceso').addEventListener('input', function() {
    const procesoValue = this.value;
    if (procesoValue) {
        updateFilters('proceso', procesoValue);  // Llamar a la función de actualización con el filtro "proceso"
    }
});

// Puedes agregar más listeners para otros campos de búsqueda de la misma manera.
</script>
