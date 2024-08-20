<?php
require 'vendor/autoload.php'; // Asegúrate de tener Composer instalado y el autoload

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

// Conexión a la base de datos
$mysqli = new mysqli("localhost", "root", "", "gategourmet");

// Verificar la conexión
if ($mysqli->connect_error) {
    die("Conexión fallida: " . $mysqli->connect_error);
}

// Cargar el archivo Excel
$spreadsheet = IOFactory::load('datos.xlsx');
$sheet = $spreadsheet->getActiveSheet();
$data = $sheet->toArray();

// Limpiar la tabla antes de insertar nuevos datos
$mysqli->query("TRUNCATE TABLE listado_maestro");

// Insertar datos en la base de datos
$stmt = $mysqli->prepare("INSERT INTO listado_maestro (areas, estado, tipo, fecha_aprobacion) VALUES (?, ?, ?, ?)");
foreach ($data as $row) {
    // Omite la primera fila si es encabezado
    if ($row[0] === 'Área') {
        continue;
    }
    $stmt->bind_param('ssss', $row[0], $row[1], $row[2], $row[3]);
    $stmt->execute();
}

$stmt->close();
$mysqli->close();
?>
