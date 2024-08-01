<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$mysqli = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME']);

if ($mysqli->connect_error) {
    die("Error de conexión: " . $mysqli->connect_error);
}

// Deshabilitar las restricciones de clave foránea
$mysqli->query("SET FOREIGN_KEY_CHECKS=0");

// Eliminar todas las preguntas existentes
$mysqli->query("TRUNCATE TABLE preguntas");

// Habilitar las restricciones de clave foránea
$mysqli->query("SET FOREIGN_KEY_CHECKS=1");

// Cargar el archivo Excel
$filePath = __DIR__ . '/necesidades.xlsx'; // Asegúrate de que el archivo Excel está en el mismo nivel que este script
$spreadsheet = IOFactory::load($filePath);
$worksheet = $spreadsheet->getActiveSheet();

$highestRow = $worksheet->getHighestRow();
$highestColumn = $worksheet->getHighestColumn();

// Insertar los datos en la base de datos
for ($row = 2; $row <= $highestRow; $row++) {
    $nivel = $mysqli->real_escape_string($worksheet->getCell('B' . $row)->getValue());
    $nivel_descripcion = $mysqli->real_escape_string($worksheet->getCell('C' . $row)->getValue());
    $necesidad = $mysqli->real_escape_string($worksheet->getCell('D' . $row)->getValue());
    $descripcion = $mysqli->real_escape_string($worksheet->getCell('E' . $row)->getValue());
    $explicacion = $mysqli->real_escape_string($worksheet->getCell('F' . $row)->getValue());
    $tips = $mysqli->real_escape_string($worksheet->getCell('G' . $row)->getValue());

    if ($nivel && $nivel_descripcion && $necesidad && $descripcion && $explicacion && $tips) {
        $query = "INSERT INTO preguntas (nivel, nivel_descripcion, necesidad, descripcion, explicacion, tips) VALUES ('$nivel', '$nivel_descripcion', '$necesidad', '$descripcion', '$explicacion', '$tips')";
        $mysqli->query($query);
    }
}

echo "Preguntas insertadas con éxito.";

$mysqli->close();
?>
