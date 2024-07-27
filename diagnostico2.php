<?php
session_start();

$mysqli = new mysqli('localhost', 'carlos_farmacheck', 'AZS12olp..', 'carlos_farmacheck');

if ($mysqli->connect_error) {
    die("Error de conexión: " . $mysqli->connect_error);
}

$session_id = isset($_GET['session_id']) ? $_GET['session_id'] : $_SESSION['session_id'];

// Obtener respuestas del cuestionario
$result = $mysqli->query("SELECT p.nivel, p.nivel_descripcion, p.necesidad, p.descripcion, p.explicacion, p.tips, r.respuesta 
                          FROM respuestas r 
                          JOIN preguntas p ON r.pregunta_id = p.id 
                          WHERE r.session_id = '$session_id'");
$respuestas = $result->fetch_all(MYSQLI_ASSOC);

// Verificar si se obtuvieron respuestas
if (empty($respuestas)) {
    die("No se encontraron respuestas para la sesión proporcionada.");
}

// Obtener el nivel actual basado en las respuestas "No"
$niveles = ['FUNDAMENTAL', 'BASICO', 'AVANZADO', 'DIFERENCIAL'];
$nivel_actual = 'FUNDAMENTAL'; // Nivel por defecto
$nivel_descripcion = '';

foreach ($niveles as $nivel) {
    $count = 0;
    foreach ($respuestas as $respuesta) {
        if ($respuesta['nivel'] == $nivel && $respuesta['respuesta'] == 'No') {
            $count++;
        }
        if ($count >= 2) {
            $nivel_actual = $nivel;
            $nivel_descripcion = $respuesta['nivel_descripcion'];
            break 2;
        }
    }
}

// Obtener la descripción del nivel actual
foreach ($respuestas as $respuesta) {
    if ($respuesta['nivel'] == $nivel_actual) {
        $nivel_descripcion = $respuesta['nivel_descripcion'];
        break;
    }
}

// Encontrar la necesidad vital (la primera necesidad marcada como "No")
$necesidad_vital = null;
foreach ($respuestas as $respuesta) {
    if ($respuesta['respuesta'] == 'No') {
        $necesidad_vital = $respuesta;
        break;
    }
}

if (!$necesidad_vital) {
    die("No se encontró ninguna necesidad vital en las respuestas.");
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico FarmaCheck</title>
    <link rel="stylesheet" href="src/css/styles.css">
    <style>
        .diagnostico-container {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .header {
            background-color: #00d1a1;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px;
        }
        .panel {
            background-color: white;
            color: black;
            padding: 10px;
            border: 1px solid #ddd;
            margin-bottom: 10px;
        }
        .panel img {
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="diagnostico-container">
        <div class="header">
            <h1>Diagnóstico FarmaCheck</h1>
            <p>Fecha <?php echo date('j/n/Y'); ?></p>
        </div>
        
        <div class="panel">
            <h2>El nivel actual de tu farmacia</h2>
            <img src="src/images/<?php echo strtoupper($nivel_actual); ?>.png" alt="Pirámide de Nivel">
            <p><?php echo $nivel_descripcion; ?></p>
        </div>

        <div class="panel">
            <h2>El área más importante</h2>
            <p><?php echo $necesidad_vital['necesidad']; ?></p>
            <p><?php echo $necesidad_vital['explicacion']; ?></p>
        </div>

        <div class="panel">
            <h2>Tips para mejorar desde ya</h2>
            <ul>
                <li><?php echo $necesidad_vital['tips']; ?></li>
            </ul>
        </div>

        <div class="panel">
            <h2>Otras recomendaciones</h2>
            <iframe src="https://farmacias.danielsegarra.com/masterclasspiramide" width="100%" height="400" style="border: none;"></iframe>
        </div>
    </div>
</body>
</html>
