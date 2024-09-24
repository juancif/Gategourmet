<?php
// Incluye la librería FPDF
require('fpdf/fpdf.php');

// Conexión a la base de datos (ajusta los datos de conexión según tu configuración)
$conn = new mysqli('localhost', 'usuario', 'contraseña', 'nombre_base_datos');

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Consulta para obtener los primeros 8 registros
$sql = "SELECT proceso, codigo, titulo_documento, tipo, version, estado, fecha_aprobacion, areas FROM tu_tabla LIMIT 8";
$result = $conn->query($sql);

// Verificar si hay resultados
if ($result->num_rows > 0) {
    // Crear un nuevo documento PDF
    $pdf = new FPDF();
    $pdf->AddPage();

    // Configurar el título
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(190, 10, 'Listado de los primeros 8 documentos', 1, 1, 'C');

    // Añadir encabezados de la tabla
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(20, 10, 'Proceso', 1);
    $pdf->Cell(20, 10, 'Codigo', 1);
    $pdf->Cell(50, 10, 'Titulo Documento', 1);
    $pdf->Cell(10, 10, 'Tipo', 1);
    $pdf->Cell(10, 10, 'Version', 1);
    $pdf->Cell(20, 10, 'Estado', 1);
    $pdf->Cell(30, 10, 'Fecha Aprobacion', 1);
    $pdf->Cell(30, 10, 'Areas', 1);
    $pdf->Ln();

    // Rellenar la tabla con los datos
    $pdf->SetFont('Arial', '', 10);
    while($row = $result->fetch_assoc()) {
        $pdf->Cell(20, 10, $row['proceso'], 1);
        $pdf->Cell(20, 10, $row['codigo'], 1);
        $pdf->Cell(50, 10, $row['titulo_documento'], 1);
        $pdf->Cell(10, 10, $row['tipo'], 1);
        $pdf->Cell(10, 10, $row['version'], 1);
        $pdf->Cell(20, 10, $row['estado'], 1);
        $pdf->Cell(30, 10, $row['fecha_aprobacion'], 1);
        $pdf->Cell(30, 10, $row['areas'], 1);
        $pdf->Ln();
    }

    // Salida del PDF
    $pdf->Output('D', 'documentos.pdf'); // Descargar el PDF

} else {
    echo "No se encontraron resultados.";
}

$conn->close();
?>
