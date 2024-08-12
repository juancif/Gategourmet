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
                proceso LIKE '%$searchTerm%' OR 
                codigo LIKE '%$searchTerm%' OR 
                titulo_documento LIKE '%$searchTerm%' OR 
                tipo LIKE '%$searchTerm%' OR 
                version LIKE '%$searchTerm%' OR 
                estado LIKE '%$searchTerm%' OR 
                fecha_aprobacion LIKE '%$searchTerm%' OR 
                areas LIKE '%$searchTerm%' OR 
                motivo_del_cambio LIKE '%$searchTerm%' OR 
                tiempo_de_retencion LIKE '%$searchTerm%' OR 
                responsable_de_retencion LIKE '%$searchTerm%' OR 
                lugar_de_almacenamiento_fisico LIKE '%$searchTerm%' OR 
                lugar_de_almacenamiento_magnetico LIKE '%$searchTerm%' OR 
                conservacion LIKE '%$searchTerm%' OR 
                disposicion_final LIKE '%$searchTerm%' OR 
                copias_controladas LIKE '%$searchTerm%' OR 
                fecha_de_vigencia LIKE '%$searchTerm%' OR 
                dias LIKE '%$searchTerm%' OR 
                senal_alerta LIKE '%$searchTerm%' OR 
                obsoleto LIKE '%$searchTerm%' OR 
                anulado LIKE '%$searchTerm%' OR 
                en_actualizacion LIKE '%$searchTerm%'";
}

$result = $conn->query($sql);

// Iniciar salida de HTML
echo '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado Maestro</title>
    <link rel="stylesheet" href="listado_maestro.css"> <!-- Enlaza un archivo CSS externo -->
    <style>
        /* Estilos para los diferentes estados */
        .vigente { background-color: #32db5a; color: white; } /* Verde claro */
        .proximo-desactualizar { background-color: #e7bd32; color: black; } /* Amarillo claro */
        .desactualizado { background-color: #d13a3a; color: white; } /* Rojo claro */
        .search-bar {
            margin: 20px auto;
            max-width: 600px;
            display: flex;
            justify-content: center;

        }
        .search-bar input[type="text"] {
            width: 80%;
            padding: 10px;
            font-size: 16px;
            border: 2px solid #ccc;
            border-radius: 4px;
        }
        .search-bar input[type="submit"] {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #0b8b0f;
            color: white;
            border: none;
            border-radius: 4px;
            margin-left: 10px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <header class="header">
        <h1>LISTADO MAESTRO</h1>
    </header>

    <div class="search-bar">
        <form method="post" action="">
            <input type="text" name="search" placeholder="Buscar..." value="' . htmlspecialchars($searchTerm) . '">
            <input type="submit" value="Buscar">
        </form>
    </div>';

if ($result->num_rows > 0) {
    echo '<div class="container">
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
                    <tbody>';

    while($row = $result->fetch_assoc()) {
        // Convertir la fecha de vigencia a un objeto DateTime
        $fecha_vigencia = new DateTime($row["fecha_de_vigencia"]);
        $fecha_actual = new DateTime();

        // Calcular la diferencia en días
        $diferencia = $fecha_actual->diff($fecha_vigencia)->days;

        // Determinar la clase CSS basada en la comparación de fechas y estado del documento
        if ($fecha_actual > $fecha_vigencia || strtolower($row["estado"]) == 'obsoleto') {
            $clase = 'desactualizado'; // Documento desactualizado (rojo)
        } elseif ($diferencia <= 10) {
            $clase = 'proximo-desactualizar'; // Próximo a ser desactualizado (amarillo)
        } else {
            $clase = 'vigente'; // Documento vigente (verde)
        }

        // Imprimir cada fila de la tabla con su clase CSS correspondiente
        echo '<tr class="' . $clase . '">
                <td>' . htmlspecialchars($row["proceso"]) . '</td>
                <td>' . htmlspecialchars($row["codigo"]) . '</td>
                <td>' . htmlspecialchars($row["titulo_documento"]) . '</td>
                <td>' . htmlspecialchars($row["tipo"]) . '</td>
                <td>' . htmlspecialchars($row["version"]) . '</td>
                <td>' . htmlspecialchars($row["estado"]) . '</td>
                <td>' . htmlspecialchars($row["fecha_aprobacion"]) . '</td>
                <td>' . htmlspecialchars($row["areas"]) . '</td>
                <td>' . htmlspecialchars($row["motivo_del_cambio"]) . '</td>
                <td>' . htmlspecialchars($row["tiempo_de_retencion"]) . '</td>
                <td>' . htmlspecialchars($row["responsable_de_retencion"]) . '</td>
                <td>' . htmlspecialchars($row["lugar_de_almacenamiento_fisico"]) . '</td>
                <td>' . htmlspecialchars($row["lugar_de_almacenamiento_magnetico"]) . '</td>
                <td>' . htmlspecialchars($row["conservacion"]) . '</td>
                <td>' . htmlspecialchars($row["disposicion_final"]) . '</td>
                <td>' . htmlspecialchars($row["copias_controladas"]) . '</td>
                <td>' . htmlspecialchars($row["fecha_de_vigencia"]) . '</td>
                <td>' . htmlspecialchars($row["dias"]) . '</td>
                <td>' . htmlspecialchars($row["senal_alerta"]) . '</td>
                <td>' . ($row["obsoleto"] ? "Sí" : "No") . '</td>
                <td>' . ($row["anulado"] ? "Sí" : "No") . '</td>
                <td>' . ($row["en_actualizacion"] ? "Sí" : "No") . '</td>
              </tr>';
    }

    echo '</tbody>
        </table>
    </div>
</div>';
} else {
    echo '<div class="container"><p>No se encontraron resultados.</p></div>';
}

// Cerrar conexión
$conn->close();

echo '</body>
</html>';
?>
