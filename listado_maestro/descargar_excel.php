<?php
require 'vendor/autoload.php'; // Cargar PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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

// Consulta para obtener los primeros 8 registros
$sql = "SELECT proceso, codigo, titulo_documento, tipo, version, estado, fecha_aprobacion, areas 
        FROM listado_maestro LIMIT 8";
$result = $conn->query($sql);

// Crear nuevo documento de Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Encabezados de las columnas
$sheet->setCellValue('A1', 'Proceso');
$sheet->setCellValue('B1', 'Código');
$sheet->setCellValue('C1', 'Título del Documento');
$sheet->setCellValue('D1', 'Tipo');
$sheet->setCellValue('E1', 'Versión');
$sheet->setCellValue('F1', 'Estado');
$sheet->setCellValue('G1', 'Fecha de Aprobación');
$sheet->setCellValue('H1', 'Áreas');

// Insertar datos en las filas
if ($result->num_rows > 0) {
    $rowNum = 2; // Iniciar en la fila 2, después de los encabezados
    while ($row = $result->fetch_assoc()) {
        $sheet->setCellValue('A' . $rowNum, $row['proceso']);
        $sheet->setCellValue('B' . $rowNum, $row['codigo']);
        $sheet->setCellValue('C' . $rowNum, $row['titulo_documento']);
        $sheet->setCellValue('D' . $rowNum, $row['tipo']);
        $sheet->setCellValue('E' . $rowNum, $row['version']);
        $sheet->setCellValue('F' . $rowNum, $row['estado']);
        $sheet->setCellValue('G' . $rowNum, $row['fecha_aprobacion']);
        $sheet->setCellValue('H' . $rowNum, $row['areas']);
        $rowNum++;
    }
}

// Cerrar conexión
$conn->close();

// Establecer cabeceras para la descarga
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="listado_maestro.xlsx"');
header('Cache-Control: max-age=0');

// Crear y enviar el archivo Excel al navegador
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>
