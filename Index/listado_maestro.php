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
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            background: url('../Imagenes/fondogg3.webp') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .header {
            text-align: center;
            margin: 2rem auto;
            position: relative;
            width: 90%;
            max-width: 1800px;
        }

        .header h1 {
            font-size: 2.5rem;
            color: #0b8b0f;
            background: rgba(255, 249, 249, 0.9);
            display: inline-block;
            padding: 1rem 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            margin-top: -30px;
        }

        .container {
            width: 90%;
            max-width: 1800px;
            margin: 1rem auto;
            background: rgba(255, 249, 249, 0.9);
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            position: relative;
            margin-top: -30px;
        }

        .table-wrapper {
            overflow-x: auto;
            overflow-y: auto;
            max-height: calc(100vh - 8rem);
            border: 2px solid #000;
            padding-bottom: 1rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }

        table, th, td {
            border: 2px solid #000;
            background: rgba(255, 249, 249, 0.9);
        }

        th, td {
            padding: 12px;
            text-align: left;
            font-size: 14px;
            color: #000;
        }

        th {
            background-color: #0b8b0f;
            color: white;
        }

        tr:nth-child(even) {
            background: rgba(255, 249, 249, 0.9);
        }

        tr:nth-child(odd) {
            background: rgba(255, 249, 249, 0.95);
        }

        tr:hover td {
            background-color: rgba(0, 0, 0, 0.1);
        }

        /* Estilos para filas con clases especiales */
        /* Estilos para filas con clases especiales */
        tr.vigente td {
    background-color: rgba(51, 187, 255, 0.4); /* Azul claro con transparencia */
    color: #000; /* Texto negro para contraste */
}

tr.desactualizado td {
    background-color: rgba(247, 86, 86, 0.4); /* Rojo claro con transparencia */
    color: #000; /* Texto negro para contraste */
}

tr.obsoleto td {
    background-color: rgba(151, 151, 151, 0.4); /* Gris claro con transparencia */
    color: #000; /* Texto negro para contraste */
}


        .search-bar {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }

        .search-bar form {
            display: flex;
            justify-content: center;
            align-items: center;
            background: rgba(255, 255, 255, 0.8);
            padding: 0.5rem 1rem;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-top: -50px;
        }

        .search-bar input[type="text"] {
            width: 300px;
            padding: 0.5rem;
            border: 2px solid #0b8b0f;
            border-radius: 4px;
            font-size: 1rem;
            margin-right: 0.5rem;
        }

        .search-bar button {
            padding: 0.5rem 1rem;
            background-color: #0b8b0f;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            table, th, td {
                display: block;
                width: 100%;
            }
            th {
                display: none;
            }
            td {
                display: flex;
                justify-content: space-between;
                padding-left: 50%;
                position: relative;
                text-align: left;
            }
            td::before {
                content: attr(data-label);
                position: absolute;
                left: 0;
                width: 45%;
                padding-right: 10px;
                font-weight: bold;
                white-space: nowrap;
                color: #333;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <h1>LISTADO MAESTRO</h1>
    </header>

    <div class="search-bar">
        <form method="post">
            <input type="text" name="search" placeholder="Buscar documentos..." value="<?php echo htmlspecialchars($searchTerm); ?>">
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
                            // Determinar la clase CSS basada en el estado del documento
                            $rowClass = '';
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
                            <td><?php echo htmlspecialchars($row['fecha_aprobacion']); ?></td>
                            <td><?php echo htmlspecialchars($row['areas']); ?></td>
                            <td><?php echo htmlspecialchars($row['motivo_del_cambio']); ?></td>
                            <td><?php echo htmlspecialchars($row['tiempo_de_retencion']); ?></td>
                            <td><?php echo htmlspecialchars($row['responsable_de_retencion']); ?></td>
                            <td><?php echo htmlspecialchars($row['lugar_de_almacenamiento_fisico']); ?></td>
                            <td><?php echo htmlspecialchars($row['lugar_de_almacenamiento_magnetico']); ?></td>
                            <td><?php echo htmlspecialchars($row['conservacion']); ?></td>
                            <td><?php echo htmlspecialchars($row['disposicion_final']); ?></td>
                            <td><?php echo htmlspecialchars($row['copias_controladas']); ?></td>
                            <td><?php echo htmlspecialchars($row['fecha_de_vigencia']); ?></td>
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
