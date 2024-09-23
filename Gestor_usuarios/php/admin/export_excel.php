<?php
require '../../../vendor/autoload.php'; // Asegúrate de que el autoload está correctamente enlazado
include_once("config_gestor.php");

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

// Consulta para obtener usuarios y administradores activos
$query = $dbConn->query("
    SELECT correo, nombres_apellidos, nombre_usuario, area, cargo, rol FROM usuarios WHERE estado = 'activo'
    UNION ALL
    SELECT correo, nombres_apellidos, nombre_usuario, area, cargo, rol FROM administradores WHERE estado = 'activo'
");

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Inserción de la imagen de la empresa
$drawing = new Drawing();
$drawing->setName('Logo');
$drawing->setDescription('Logo de la Empresa');
$drawing->setPath('../../../Imagenes/logo_oficial_color.png'); // Ruta a la imagen
$drawing->setHeight(35); // Ajustar el tamaño de la imagen
$drawing->setCoordinates('A1'); // Ubicar la imagen en la celda A1
$drawing->setOffsetX(10); // Ajustar el desplazamiento hacia la derecha
$drawing->setWorksheet($sheet);

// Ajustar el rango de celdas para la imagen y fusionarlas
$sheet->mergeCells('A1:F2');
$sheet->getStyle('A1:F2')->applyFromArray([
    'alignment' => [
        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['argb' => 'FFEFEFEF'], // Gris claro para la celda de la imagen
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_MEDIUM, // Bordes más gruesos
            'color' => ['argb' => 'FF000000'], // Negro
        ],
    ],
]);

// Encabezados de columnas a partir de la fila 3
$encabezados = ['Correo Electrónico', 'Nombres y Apellidos', 'Nombre de Usuario', 'Área', 'Cargo', 'Rol'];
$columnas = ['A', 'B', 'C', 'D', 'E', 'F'];

// Aplicar estilo a los encabezados (verde más oscuro y bordes gruesos)
foreach ($columnas as $index => $columna) {
    $sheet->setCellValue($columna . '3', $encabezados[$index]);
    // Estilo de encabezado: Negrita, centrado, fondo verde oscuro, bordes gruesos
    $sheet->getStyle($columna . '3')->applyFromArray([
        'font' => ['bold' => true],
        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['argb' => 'FF228B22'], // Verde oscuro
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_MEDIUM, // Bordes más gruesos
                'color' => ['argb' => 'FF000000'], // Negro
            ],
        ],
    ]);
    // Ajustar el tamaño de las columnas automáticamente
    $sheet->getColumnDimension($columna)->setAutoSize(true);
}

// Agregar datos a partir de la fila 4
$row = 4;
while ($usuario = $query->fetch(PDO::FETCH_ASSOC)) {
    $sheet->setCellValue('A' . $row, $usuario['correo']);
    $sheet->setCellValue('B' . $row, $usuario['nombres_apellidos']);
    $sheet->setCellValue('C' . $row, $usuario['nombre_usuario']);
    $sheet->setCellValue('D' . $row, $usuario['area']);
    $sheet->setCellValue('E' . $row, $usuario['cargo']);
    $sheet->setCellValue('F' . $row, $usuario['rol']);
    
    // Aplicar estilo a las celdas de datos (fondo gris más oscuro y bordes gruesos)
    foreach ($columnas as $columna) {
        $sheet->getStyle($columna . $row)->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFB0B0B0'], // Gris más oscuro para las celdas de datos
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_MEDIUM, // Bordes más gruesos
                    'color' => ['argb' => 'FF000000'], // Negro
                ],
            ],
        ]);
    }
    
    $row++;
}

// Aplicar estilo a todo el rango de datos
$sheet->getStyle('A3:F' . ($row - 1))->applyFromArray([
    'alignment' => ['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
    'font' => ['size' => 12],
]);

// Configurar encabezados de respuesta para descargar el archivo
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="usuarios_y_administradores_activos.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>
