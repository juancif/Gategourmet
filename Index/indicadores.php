<?php
// Conectar a la base de datos
$conn = new mysqli("localhost", "root", "", "gategourmet");

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Consulta para estado de documentación por área
$sql_estado = "
    SELECT 
        SUM(CASE WHEN en_actualizacion = 1 THEN 1 ELSE 0 END) AS en_actualizacion,
        SUM(CASE WHEN obsoleto = 1 THEN 1 ELSE 0 END) AS obsoleto,
        SUM(CASE WHEN anulado = 1 THEN 1 ELSE 0 END) AS anulado,
        SUM(CASE WHEN en_actualizacion = 0 AND obsoleto = 0 AND anulado = 0 THEN 1 ELSE 0 END) AS desactualizado
    FROM listado_maestro";
$resultado_estado = $conn->query($sql_estado);
$estado = $resultado_estado->fetch_assoc();

// Consulta para porcentaje de actualización por área
$sql_porcentaje = "
    SELECT 
        areas, 
        COUNT(*) AS total_documentos, 
        SUM(CASE WHEN en_actualizacion = 1 THEN 1 ELSE 0 END) / COUNT(*) * 100 AS porcentaje_actualizacion 
    FROM listado_maestro 
    GROUP BY areas";
$resultado_porcentaje = $conn->query($sql_porcentaje);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Indicadores de Documentación</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: url('../Imagenes/fondogg3.webp') no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .header {
            background-color: #003c8f;
            color: #ffffff;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            margin-bottom: 30px;
            border-bottom: 5px solid #0056a1;
        }

        .header h1 {
            font-size: 2.5rem;
            margin: 0;
            letter-spacing: 1px;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            padding: 15px;
            transition: box-shadow 0.3s ease, transform 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            transform: translateY(-5px);
        }

        .card h2 {
            font-size: 1.8rem;
            color: #003c8f;
            margin-bottom: 15px;
            border-bottom: 4px solid #0056a1;
            padding-bottom: 10px;
            font-weight: 600;
        }

        .bar-container {
            display: flex;
            justify-content: space-around;
            align-items: flex-end;
            gap: 10px;
            flex-wrap: wrap;
        }

        .bar {
            flex: 1;
            text-align: center;
            position: relative;
            padding: 0 5px;
            min-width: 60px;
            max-width: 120px;
        }

        .bar div {
            background: #0056a1;
            width: 100%;
            border-radius: 8px 8px 0 0;
            position: relative;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            height: 100px; /* Ajusta la altura de las barras aquí */
            transition: background 0.3s ease;
        }

        .bar div:hover {
            background: #003d7a;
        }

        .bar-label {
            position: absolute;
            top: -25px;
            width: 100%;
            text-align: center;
            font-weight: 600;
            color: #003c8f;
        }

        .bar-value {
            font-weight: 600;
            color: #ffffff;
            margin-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }

        table th, table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        table th {
            background-color: #003c8f;
            color: #ffffff;
            font-weight: 600;
        }

        table td {
            text-align: center;
        }

        .progress {
            background-color: #f4f4f4;
            border-radius: 8px;
            height: -20px;
            width: 100%;
            position: relative;
            overflow: hidden;
        }

        .progress-bar {
            height: 20px;
            border-radius: 8px;
            text-align: right;
            padding-right: 5px;
            color: #ffffff;
            font-weight: 100;
            line-height: 20px;
            transition: width 0.3s ease, background 0.3s ease;
        }

        .progress-bar.green {
            background: linear-gradient(135deg, #28a745 0%, #7fff94 100%);
        }

        .progress-bar.red {
            background: linear-gradient(135deg, #dc3545 0%, #ff6b6b 100%);
        }

        .footer {
            text-align: center;
            padding: 15px;
            background-color: #003c8f;
            color: #ffffff;
            margin-top: 30px;
            border-radius: 8px 8px 0 0;
            box-shadow: 0 -4px 8px rgba(0, 0, 0, 0.2);
        }

        @media (max-width: 768px) {
            .bar-container {
                flex-direction: column;
                align-items: center;
                gap: 10px;
            }

            .bar {
                min-width: 50px;
                max-width: -100px;
            }

            .card {
                padding: 15px;
            }

            .bar div {
                height: -100px; /* Ajusta la altura de las barras para dispositivos móviles */
            }
        }
    </style>
</head>
<body>

<div class="header">
    <h1>Indicadores de Documentación</h1>
</div>

<div class="container">
    <div class="card">
        <h2>Estado de Documentación por Área</h2>
        <div class="bar-container">
            <div class="bar">
                <div style="height: <?php echo htmlspecialchars($estado['en_actualizacion']) * 1.5; ?>px;"></div>
                <div class="bar-value"><?php echo htmlspecialchars($estado['en_actualizacion']); ?></div>
                <div class="bar-label">En Actualización</div>
            </div>
            <div class="bar">
                <div style="height: <?php echo htmlspecialchars($estado['desactualizado']) * 1.5; ?>px;"></div>
                <div class="bar-value"><?php echo htmlspecialchars($estado['desactualizado']); ?></div>
                <div class="bar-label">Desactualizado</div>
            </div>
            <div class="bar">
                <div style="height: <?php echo htmlspecialchars($estado['obsoleto']) * 1.5; ?>px;"></div>
                <div class="bar-value"><?php echo htmlspecialchars($estado['obsoleto']); ?></div>
                <div class="bar-label">Obsoleto</div>
            </div>
            <div class="bar">
                <div style="height: <?php echo htmlspecialchars($estado['anulado']) * 1.5; ?>px;"></div>
                <div class="bar-value"><?php echo htmlspecialchars($estado['anulado']); ?></div>
                <div class="bar-label">Anulado</div>
            </div>
        </div>
    </div>

    <div class="card">
        <h2>Porcentaje de Actualización por Área</h2>
        <table>
            <thead>
            <tr>
                <th>Área</th>
                <th>Porcentaje de Actualización</th>
            </tr>
            </thead>
            <tbody>
            <?php while ($fila = $resultado_porcentaje->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($fila['areas']); ?></td>
                    <td>
                        <div class="progress">
                            <div class="progress-bar green"
                                 style="width: <?php echo htmlspecialchars(number_format($fila['porcentaje_actualizacion'], 2)); ?>%;">
                                <?php echo htmlspecialchars(number_format($fila['porcentaje_actualizacion'], 2)) . '%'; ?>
                            </div>
                        </div>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="footer">
    <p>&copy; 2024 Gategourmet. Todos los derechos reservados.</p>
</div>

</body>
</html>
