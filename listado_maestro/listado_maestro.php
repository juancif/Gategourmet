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

// Inicializar variables para los filtros
$campos = ['proceso', 'codigo', 'titulo_documento', 'tipo', 'version', 'estado', 'fecha_aprobacion', 'areas'];
$params = [];
$types = '';
$searchValues = [];

// Recopilar los valores de los filtros enviados por POST
foreach ($campos as $campo) {
    $$campo = $_POST[$campo] ?? '';
    if (!empty($$campo)) {
        $params[] = "%" . $$campo . "%";
        $types .= 's';
        $searchValues[$campo] = $$campo;
    }
}

// Construir la consulta SQL
$sql = "SELECT proceso, codigo, titulo_documento, tipo, version, estado, fecha_aprobacion, areas FROM listado_maestro WHERE 1=1";

// Añadir filtros a la consulta de manera dinámica
foreach ($searchValues as $campo => $valor) {
    $sql .= " AND $campo LIKE ?";
}

// Preparar la consulta
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Error al preparar la consulta: " . $conn->error);
}

// Vincular los parámetros si hay filtros
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

// Ejecutar la consulta
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
    <style>
        .btn-excel {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .btn-excel:hover {
            background-color: #218838;
        }
        .container {
            padding-bottom: 100px;
        }
        .table-wrapper {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table th, table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .search-dropdown {
            display: none;
            position: absolute;
            background-color: white;
            border: 1px solid #ccc;
            max-height: 200px;
            overflow-y: auto;
            width: 100%;
            z-index: 10;
        }
        .suggestion-item {
            padding: 5px;
            cursor: pointer;
        }
        .suggestion-item:hover {
            background-color: #007bff;
            color: white;
        }
    </style>
</head>
<body>
    <header class="header">
        <img src="../Imagenes/Logo_oficial_B-N.png" alt="Logo Gate Gourmet" class="logo">
    </header>

    <div class="container">
        <!-- Barra de búsqueda -->
        <div class="search-bar">
            <form method="post">
                <div class="search-fields">
                    <!-- Generación dinámica de los campos de búsqueda con autocompletado -->
                    <?php foreach ($campos as $campo): ?>
                        <div class="search-field" style="position: relative;">
                            <label for="<?php echo htmlspecialchars($campo); ?>"><?php echo ucwords(str_replace('_', ' ', $campo)); ?></label>
                            <input type="text" class="search-input" id="<?php echo htmlspecialchars($campo); ?>" 
                                   name="<?php echo htmlspecialchars($campo); ?>" autocomplete="off"
                                   onkeyup="fetchSuggestions('<?php echo $campo; ?>')">
                            <div class="search-dropdown" id="<?php echo htmlspecialchars($campo); ?>-options"></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="submit">Buscar</button>
            </form>
        </div>

        <!-- Botón de descarga Excel -->
        <form method="post" action="descargar_excel.php">
            <button type="submit" name="descargar_8" class="btn-excel">Descargar primeros 8 registros en Excel</button>
        </form>

        <!-- Resultados de la búsqueda -->
        <?php if ($result && $result->num_rows > 0): ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <?php foreach ($campos as $campo): ?>
                                <th><?php echo ucwords(str_replace('_', ' ', $campo)); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <?php foreach ($campos as $campo): ?>
                                <td><?php echo htmlspecialchars($row[$campo]); ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>No se encontraron resultados.</p>
        <?php endif; ?>
    </div>

    <footer class="footer">
        <p>&copy; 2023 Gate Gourmet</p>
    </footer>

    <!-- Script de autocompletado con AJAX -->
    <script>
        function fetchSuggestions(campo) {
            let input = document.getElementById(campo);
            let dropdown = document.getElementById(campo + '-options');
            let query = input.value;

            if (query.length < 2) {
                dropdown.style.display = 'none';
                return;
            }

            let xhr = new XMLHttpRequest();
            xhr.open('GET', 'autocomplete.php?campo=' + campo + '&query=' + query, true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    let suggestions = JSON.parse(xhr.responseText);
                    dropdown.innerHTML = '';

                    if (suggestions.length > 0) {
                        suggestions.forEach(function(suggestion) {
                            let div = document.createElement('div');
                            div.classList.add('suggestion-item');
                            div.textContent = suggestion;
                            div.onclick = function() {
                                input.value = suggestion;
                                dropdown.style.display = 'none';
                            };
                            dropdown.appendChild(div);
                        });
                        dropdown.style.display = 'block';
                    } else {
                        dropdown.style.display = 'none';
                    }
                }
            };
            xhr.send();
        }
    </script>
</body>
</html>
