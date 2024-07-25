<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$inputFileName = './necesidades.xlsx'; // Asegúrate de tener la ruta correcta al archivo Excel
$spreadsheet = IOFactory::load($inputFileName);
$sheet = $spreadsheet->getActiveSheet();

$questions = [];
$firstRow = true; // Flag to skip the first row

foreach ($sheet->getRowIterator() as $row) {
    if ($firstRow) {
        $firstRow = false;
        continue; // Skip the first row
    }

    $cellIterator = $row->getCellIterator();
    $cellIterator->setIterateOnlyExistingCells(FALSE);

    $rowData = [];
    foreach ($cellIterator as $cell) {
        $rowData[] = $cell->getValue();
    }
    // Asegurarse de que NECESIDAD y DESCRIPCIÓN no estén vacíos
    if (isset($rowData[1]) && isset($rowData[4])) {
        $questions[] = [
            'necesidad' => $rowData[1], // Columna B
            'descripcion' => $rowData[4] // Columna E
        ];
    }
}

$mysqli = new mysqli('localhost', 'root', '', 'farmacia');

foreach ($questions as $question) {
    $stmt = $mysqli->prepare("INSERT INTO preguntas (necesidad, descripcion) VALUES (?, ?)");
    $stmt->bind_param('ss', $question['necesidad'], $question['descripcion']);
    $stmt->execute();
    $stmt->close();
}

$mysqli->close();
echo "Preguntas insertadas con éxito";
?>
