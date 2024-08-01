<?php
require 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

session_start();

$mysqli = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME']);

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
            color: black; /* Asegurar que el texto es negro */
            overflow: hidden;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 10px;
        }

        .panel img {
            width: 100%;
            margin-bottom: 10px;
        }

        .panel p {
            color: black; /* Asegurar que los párrafos sean negros */
        }

        .active + .panel {
            display: block;
        }

        .email-form {
            background-color: white;
            color: black;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            text-align: center;
        }

        .email-form input[type="email"] {
            padding: 10px;
            margin-top: 10px;
            width: 80%;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .email-form button {
            padding: 10px 20px;
            margin-top: 10px;
            background-color: #00d1a1;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .email-form button:hover {
            background-color: #00a97f;
        }

        .email-form p {
            color: black; /* Asegurar que el texto sea negro */
        }

        .email-form h2 {
            color: black; /* Asegurar que el texto sea negro */
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
        
        <button class="accordion" onclick="toggleAccordion(this)">El nivel actual de tu farmacia</button>
        <div class="panel">
            <img src="src/images/<?php echo strtoupper($nivel_actual); ?>.png" alt="Pirámide de Nivel">
            <p><?php echo $nivel_descripcion; ?></p>
        </div>

        <button class="accordion" onclick="toggleAccordion(this)">El área más importante</button>
        <div class="panel">
            <p><?php echo htmlspecialchars($necesidad_vital['necesidad']); ?></p>
            <p><?php echo htmlspecialchars($necesidad_vital['explicacion']); ?></p>
        </div>

        <button class="accordion" onclick="toggleAccordion(this)">Tips para mejorar desde ya</button>
        <div class="panel">
            <ul>
                <li><?php echo htmlspecialchars($necesidad_vital['tips']); ?></li>
            </ul>
        </div>

        <button class="accordion" onclick="toggleAccordion(this)">Otras recomendaciones</button>
        <div class="panel">
            <iframe src="https://farmacias.danielsegarra.com/masterclasspiramide" width="100%" height="400" style="border: none;"></iframe>
        </div>
        
        <div class="email-form">
            <h2>¿Quieres recibir este diagnóstico en tu correo electrónico?</h2>
            <p>Para obtener una copia de tu diagnóstico directamente en tu bandeja de entrada, simplemente introduce tu dirección de correo electrónico y haz clic en "Enviar".</p>
            <form action="enviar_diagnostico.php" method="post">
                <input type="hidden" name="session_id" value="<?php echo $session_id; ?>">
                <input type="email" name="email" placeholder="Tu correo electrónico" required>
                <button type="submit">Enviar</button>
            </form>
        </div>
    </div>
</body>
</html>
