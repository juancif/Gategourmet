<?php
require 'vendor/indicadores.php'; // Asegúrate de que la ruta sea correcta

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

// Ruta al archivo Excel
$excelFile = 'C:/xampp/htdocs/Gategourmet/documentos/Listado_maestro.csv';

// Leer el archivo Excel
$reader = new Xlsx();
$spreadsheet = $reader->load($excelFile);
$sheet = $spreadsheet->getActiveSheet();

// Obtener los datos de la hoja activa
$data = $sheet->toArray();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Indicadores de Documentación</title>
    <link rel="stylesheet" href="indicadores.css">
</head>
<body>
    <h1>Datos del Archivo Excel</h1>
    <table>
        <thead>
            <tr>
                <?php
                // Imprimir encabezados de columna
                foreach ($data[0] as $header) {
                    echo "<th>{$header}</th>";
                }
                ?>
            </tr>
        </thead>
        <tbody>
            <?php
            // Imprimir datos de las filas
            for ($i = 1; $i < count($data); $i++) {
                echo "<tr>";
                foreach ($data[$i] as $cell) {
                    echo "<td>{$cell}</td>";
                }
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>
