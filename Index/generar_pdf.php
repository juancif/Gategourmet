<?php
session_start();
require('fpdf.php');

// Verificar que el usuario esté autenticado y que el área esté definida
if (!isset($_SESSION['area'])) {
    die('No tiene acceso a esta sección.');
}

$area = $_SESSION['area'];

// Crear el PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);

// Título del documento
$pdf->Cell(40, 10, 'Indicadores del area: ' . $area);

// Simular datos de indicadores según el área
$indicadores = array();

switch ($area) {
    case 'Gestión corporativa':
        $indicadores = [
            'Meta 1: 85%',
            'Meta 2: 90%',
            'Meta 3: 75%'
        ];
        break;
    case 'Compliance':
        $indicadores = [
            'Meta 1: 95%',
            'Meta 2: 88%',
            'Meta 3: 80%'
        ];
        break;
    // Puedes agregar más áreas con sus respectivos indicadores
    default:
        $indicadores = ['No hay indicadores para esta área'];
        break;
}

// Agregar los indicadores al PDF
$pdf->Ln(10); // Salto de línea
foreach ($indicadores as $indicador) {
    $pdf->Cell(40, 10, $indicador);
    $pdf->Ln(10);
}

// Forzar descarga del archivo PDF
$pdf->Output('D', 'Indicadores_' . $area . '.pdf');
?>
