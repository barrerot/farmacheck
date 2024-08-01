<?php
require 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Conexión a la base de datos
$mysqli = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME']);

if ($mysqli->connect_error) {
    die("Error de conexión: " . $mysqli->connect_error);
}

// Código para generar diagnóstico
// ...
