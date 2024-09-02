<?php
require 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$mysqli = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME']);

if ($mysqli->connect_error) {
    die("Error de conexión: " . $mysqli->connect_error);
}

// Hash de la contraseña "admin"
$hashed_password = password_hash('admin', PASSWORD_DEFAULT);

// Inserta el usuario "admin" con la contraseña "admin"
$stmt = $mysqli->prepare("INSERT INTO usuarios (username, password) VALUES (?, ?)");
if ($stmt) {
    $username = 'admin';
    $stmt->bind_param('ss', $username, $hashed_password);

    if ($stmt->execute()) {
        echo "Usuario 'admin' creado con éxito.";
    } else {
        echo "Error al crear el usuario: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Error en la preparación de la declaración: " . $mysqli->error;
}

$mysqli->close();
?>
