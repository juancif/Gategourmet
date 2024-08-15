<?php
// Conectar a la base de datos
$conn = new mysqli("localhost", "root", "", "gategourmet");

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Consultas a la base de datos
$sql_estado = "
    SELECT 
        SUM(CASE WHEN en_actualizacion = 1 THEN 1 ELSE 0 END) AS en_actualizacion,
        SUM(CASE WHEN obsoleto = 1 THEN 1 ELSE 0 END) AS obsoleto,
        SUM(CASE WHEN anulado = 1 THEN 1 ELSE 0 END) AS anulado,
        SUM(CASE WHEN en_actualizacion = 0 AND obsoleto = 0 AND anulado = 0 THEN 1 ELSE 0 END) AS desactualizado
    FROM listado_maestro";
$resultado_estado = $conn->query($sql_estado);
$estado = $resultado_estado->fetch_assoc();

$max_valor = max($estado);
$factor_escala = ($max_valor > 0) ? 200 / $max_valor : 1; // Evita división por cero

$sql_porcentaje = "
    SELECT 
        areas, 
        COUNT(*) AS total_documentos, 
        SUM(CASE WHEN en_actualizacion = 1 THEN 1 ELSE 0 END) / NULLIF(COUNT(*), 0) * 100 AS porcentaje_actualizacion 
    FROM listado_maestro 
    GROUP BY areas";
$resultado_porcentaje = $conn->query($sql_porcentaje);

$sql_desactualizada = "
    SELECT 
        areas, 
        COUNT(*) AS total_desactualizados 
    FROM listado_maestro 
    WHERE obsoleto = 1 OR anulado = 1
    GROUP BY areas";
$resultado_desactualizada = $conn->query($sql_desactualizada);

$sql_tiempos = "
    SELECT 
        SUM(CASE WHEN a_tiempo = 1 THEN 1 ELSE 0 END) / NULLIF(COUNT(*), 0) * 100 AS porcentaje_a_tiempo,
        SUM(CASE WHEN fuera_de_tiempo = 1 THEN 1 ELSE 0 END) / NULLIF(COUNT(*), 0) * 100 AS porcentaje_fuera_de_tiempo,
        SUM(CASE WHEN no_entregado = 1 THEN 1 ELSE 0 END) / NULLIF(COUNT(*), 0) * 100 AS porcentaje_no_entregado
    FROM listado_maestro";
$resultado_tiempos = $conn->query($sql_tiempos);
$tiempos = $resultado_tiempos->fetch_assoc();
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
            background-color: #004d40;
            color: #ffffff;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            margin-bottom: 30px;
            border-bottom: 5px solid #00332c;
            border-radius: 12px;
        }

        .header img {
            max-width: 100px;
            margin-bottom: 15px;
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
            padding: 20px;
            transition: box-shadow 0.3s ease, transform 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            transform: translateY(-5px);
        }

        .card h2 {
            font-size: 1.8rem;
            color: #004d40;
            margin-bottom: 20px;
            border-bottom: 4px solid #00332c;
            padding-bottom: 10px;
            font-weight: 600;
        }

        .bar-container {
            display: flex;
            justify-content: space-around;
            align-items: flex-end;
            gap: 15px;
            flex-wrap: wrap;
        }

        .bar {
            flex: 1;
            text-align: center;
            position: relative;
            padding: 0 10px;
            min-width: 120px;
            max-width: 180px;
        }

        .bar div {
            background: #00796b;
            width: 100%;
            border-radius: 8px 8px 0 0;
            position: relative;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            transition: background 0.3s ease;
        }

        .bar div:hover {
            background: #004d40;
        }

        .bar-label {
            position: absolute;
            top: -30px;
            width: 100%;
            text-align: center;
            font-weight: 600;
            color: #004d40;
            font-size: 1rem;
        }

        .bar-value {
            font-weight: 600;
            color: #ffffff;
            margin-bottom: 10px;
            font-size: 1.2rem;
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
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        table th {
            background-color: #004d40;
            color: #ffffff;
            font-weight: 600;
            text-align: center;
        }

        table td {
            text-align: center;
        }

        .progress {
            background-color: #f4f4f4;
            border-radius: 8px;
            height: 20px;
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
            background: linear-gradient(135deg, #004d40 0%, #00796b 100%);
        }

        .progress-bar.red {
            background: linear-gradient(135deg, #d32f2f 0%, #ff6f6f 100%);
        }

        .footer {
            text-align: center;
            padding: 20px;
            background-color: #004d40;
            color: #ffffff;
            margin-top: 30px;
            border-radius: 12px;
            box-shadow: 0 -4px 8px rgba(0, 0, 0, 0.2);
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .bar-container {
                flex-direction: column;
                align-items: center;
                gap: 10px;
            }

            .bar {
                min-width: 100px;
                max-width: 150px;
            }

            .card {
                padding: 15px;
            }

            .bar div {
                height: 150px;
            }
        }
    </style>
</head>
<body>

<div class="header">
    <img src="../Imagenes/logo.png" alt="Logo de Gategourmet">
    <h1>Indicadores de Documentación</h1>
</div>

<div class="container">
    <div class="card">
        <h2>Estado de Documentación</h2>
        <div class="bar-container">
            <div class="bar">
                <div style="height: <?php echo $estado['en_actualizacion'] * $factor_escala; ?>px;"></div>
                <div class="bar-value"><?php echo htmlspecialchars($estado['en_actualizacion']); ?></div>
                <div class="bar-label">En Actualización</div>
            </div>
            <div class="bar">
                <div style="height: <?php echo $estado['desactualizado'] * $factor_escala; ?>px;"></div>
                <div class="bar-value"><?php echo htmlspecialchars($estado['desactualizado']); ?></div>
                <div class="bar-label">Desactualizado</div>
            </div>
            <div class="bar">
                <div style="height: <?php echo $estado['obsoleto'] * $factor_escala; ?>px;"></div>
                <div class="bar-value"><?php echo htmlspecialchars($estado['obsoleto']); ?></div>
                <div class="bar-label">Obsoleto</div>
            </div>
            <div class="bar">
                <div style="height: <?php echo $estado['anulado'] * $factor_escala; ?>px;"></div>
                <div class="bar-value"><?php echo htmlspecialchars($estado['anulado']); ?></div>
                <div class="bar-label">Anulado</div>
            </div>
        </div>
    </div>

    <div class="card">
        <h2>Porcentaje de Documentación en Actualización por Área</h2>
        <table>
            <thead>
                <tr>
                    <th>Área</th>
                    <th>Porcentaje en Actualización</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($fila = $resultado_porcentaje->fetch_assoc()) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($fila['areas']); ?></td>
                        <td>
                            <div class="progress">
                                <div class="progress-bar green" style="width: <?php echo htmlspecialchars($fila['porcentaje_actualizacion']); ?>%;">
                                    <?php echo number_format($fila['porcentaje_actualizacion'], 2); ?>%
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div class="card">
        <h2>Cantidad de Documentación Desactualizada por Área</h2>
        <table>
            <thead>
                <tr>
                    <th>Área</th>
                    <th>Documentos Desactualizados</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($fila = $resultado_desactualizada->fetch_assoc()) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($fila['areas']); ?></td>
                        <td><?php echo htmlspecialchars($fila['total_desactualizados']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div class="card">
        <h2>Indicadores de Tiempo de Entrega</h2>
        <div class="bar-container">
            <div class="bar">
                <div style="height: <?php echo $tiempos['porcentaje_a_tiempo']; ?>%;"></div>
                <div class="bar-value"><?php echo number_format($tiempos['porcentaje_a_tiempo'], 2); ?>%</div>
                <div class="bar-label">A Tiempo</div>
            </div>
            <div class="bar">
                <div style="height: <?php echo $tiempos['porcentaje_fuera_de_tiempo']; ?>%;"></div>
                <div class="bar-value"><?php echo number_format($tiempos['porcentaje_fuera_de_tiempo'], 2); ?>%</div>
                <div class="bar-label">Fuera de Tiempo</div>
            </div>
            <div class="bar">
                <div style="height: <?php echo $tiempos['porcentaje_no_entregado']; ?>%;"></div>
                <div class="bar-value"><?php echo number_format($tiempos['porcentaje_no_entregado'], 2); ?>%</div>
                <div class="bar-label">No Entregado</div>
            </div>
        </div>
    </div>
</div>

<div class="footer">
    &copy; <?php echo date('Y'); ?> Gategourmet. Todos los derechos reservados.
</div>

</body>
</html>
