<?php
session_start();

$mysqli = new mysqli('localhost', 'root', '', 'farmacia');

if ($mysqli->connect_error) {
    die("Error de conexión: " . $mysqli->connect_error);
}

$session_id = isset($_GET['session_id']) ? $_GET['session_id'] : $_SESSION['session_id'];

// Obtener respuestas del cuestionario
$result = $mysqli->query("SELECT p.nivel, p.necesidad, p.descripcion, r.respuesta FROM respuestas r JOIN preguntas p ON r.pregunta_id = p.id WHERE r.session_id = '$session_id'");
$respuestas = $result->fetch_all(MYSQLI_ASSOC);

// Verificar si se obtuvieron respuestas
if (empty($respuestas)) {
    die("No se encontraron respuestas para la sesión proporcionada.");
}

// Determinar el nivel actual y el área más importante
$niveles = ['Fundamental', 'Básico', 'Avanzado', 'Diferencial'];
$nivel_actual = 'Fundamental';
$areas_importantes = [];
$contador_nos = 0;

foreach ($respuestas as $respuesta) {
    if ($respuesta['respuesta'] === 'No') {
        $areas_importantes[] = $respuesta;
        $contador_nos++;
    }
    if ($respuesta['respuesta'] === 'No' && in_array($respuesta['nivel'], $niveles)) {
        $nivel_actual = $respuesta['nivel'];
    }
}

// Asegurarse de que hay al menos dos respuestas con "No" para mostrar el nivel
if ($contador_nos < 2) {
    $nivel_actual = 'Nivel insuficiente para determinar';
}

// Encontrar el área más importante (el item del cuestionario más alto marcado como "No")
$area_mas_importante = !empty($areas_importantes) ? $areas_importantes[0] : ['necesidad' => 'No se encontraron áreas importantes', 'descripcion' => 'No hay áreas identificadas como más importantes en este momento.'];

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
        .accordion {
            background-color: #00d1a1;
            color: white;
            cursor: pointer;
            padding: 15px;
            width: 100%;
            text-align: left;
            border: none;
            outline: none;
            transition: background-color 0.3s, transform 0.3s;
            border-radius: 5px;
            margin-bottom: 10px;
            position: relative;
        }

        .accordion:hover,
        .accordion.active {
            background-color: #00a97f;
        }

        .accordion::after {
            content: '\25BC';
            font-size: 16px;
            position: absolute;
            right: 20px;
            transition: transform 0.3s;
        }

        .accordion.active::after {
            transform: rotate(180deg);
        }

        .panel {
            display: none;
            background-color: white;
            color: black;
            overflow: hidden;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 10px;
        }

        .panel img {
            width: 100%;
            margin-bottom: 10px;
        }

        .active + .panel {
            display: block;
        }
    </style>
    <script>
        function toggleAccordion(element) {
            element.classList.toggle('active');
            var panel = element.nextElementSibling;
            if (panel.style.display === "block") {
                panel.style.display = "none";
            } else {
                panel.style.display = "block";
            }
        }
    </script>
</head>
<body>
    <div class="diagnostico-container">
        <h1>Diagnóstico FarmaCheck</h1>
        <p>Fecha <?php echo date('j/n/Y'); ?></p>
        <p>Nisi et laborum sint enim dolor culpa culpa nulla in aute ea aliqua velit. Elit ad ut reprehenderit ad do occaecat labore eu laboris pariatur eu laborum amet minim. Cupidatat enim laboris ex eiusmod ut ipsum irure e</p>
        
        <button class="accordion" onclick="toggleAccordion(this)">El nivel actual de tu farmacia</button>
        <div class="panel">
            <?php if ($nivel_actual !== 'Nivel insuficiente para determinar'): ?>
                <img src="src/images/<?php echo strtolower($nivel_actual); ?>.png" alt="Pirámide de Nivel">
                <p>Tu farmacia está en nivel de lo <?php echo $nivel_actual; ?>.</p>
            <?php else: ?>
                <p><?php echo $nivel_actual; ?></p>
            <?php endif; ?>
        </div>

        <button class="accordion" onclick="toggleAccordion(this)">El área más importante</button>
        <div class="panel">
            <p><?php echo htmlspecialchars($area_mas_importante['necesidad']); ?></p>
            <p><?php echo htmlspecialchars($area_mas_importante['descripcion']); ?></p>
        </div>

        <button class="accordion" onclick="toggleAccordion(this)">Tips para mejorar desde ya</button>
        <div class="panel">
            <ul>
                <?php foreach ($areas_importantes as $area): ?>
                    <li><?php echo htmlspecialchars($area['necesidad']) . ': ' . htmlspecialchars($area['descripcion']); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <button class="accordion" onclick="toggleAccordion(this)">Otras recomendaciones</button>
        <div class="panel">
            <iframe src="https://farmacias.danielsegarra.com/masterclasspiramide" width="100%" height="400" style="border: none;"></iframe>
        </div>
    </div>
</body>
</html>
