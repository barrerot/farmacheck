<?php
session_start();

$mysqli = new mysqli('localhost', 'root', '', 'farmacia');

if ($mysqli->connect_error) {
    die("Error de conexión: " . $mysqli->connect_error);
}

$session_id = $_SESSION['session_id'];

// Obtener respuestas del cuestionario
$result = $mysqli->query("SELECT p.necesidad, p.descripcion, r.respuesta FROM respuestas r JOIN preguntas p ON r.pregunta_id = p.id WHERE r.session_id = '$session_id'");
$respuestas = $result->fetch_all(MYSQLI_ASSOC);

// Determinar el nivel actual y el área más importante
$nivel_actual = 'Fundamental';
$areas_importantes = [];
foreach ($respuestas as $respuesta) {
    if ($respuesta['respuesta'] === 'No') {
        $areas_importantes[] = $respuesta;
    }
}

// Encontrar el área más importante (el item del cuestionario más alto marcado como "No")
$area_mas_importante = !empty($areas_importantes) ? $areas_importantes[0] : ['necesidad' => 'No se encontraron áreas importantes', 'descripcion' => ''];

// Obtener tips para mejorar (esto puede ser estático o basado en las respuestas)
$tips_mejora = [
    "Implementa un sistema de gestión para mejorar la eficiencia.",
    "Capacita a tu personal en las mejores prácticas de atención al cliente.",
    "Revisa tus inventarios y optimiza el stock para evitar pérdidas."
];

$mysqli->close();
session_destroy();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico FarmaCheck</title>
    <link rel="stylesheet" href="src/css/styles.css">
    <script>
        function toggleAccordion(id) {
            var element = document.getElementById(id);
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
        <button class="accordion" onclick="toggleAccordion('nivelActual')">El nivel actual de tu farmacia</button>
        <div class="panel" id="nivelActual">
            <img src="src/images/piramide.png" alt="Pirámide de Nivel">
            <p>Tu farmacia está en nivel de lo <?php echo $nivel_actual; ?>.</p>
            <p>Incididunt consequat eu ut incididunt officia pariatur commodo eiusmod ad culpa dolore qui.</p>
        </div>

        <button class="accordion" onclick="toggleAccordion('areaImportante')">El área más importante</button>
        <div class="panel" id="areaImportante">
            <p><?php echo htmlspecialchars($area_mas_importante['necesidad']); ?></p>
            <p><?php echo htmlspecialchars($area_mas_importante['descripcion']); ?></p>
        </div>

        <button class="accordion" onclick="toggleAccordion('tipsMejora')">Tips para mejorar desde ya</button>
        <div class="panel" id="tipsMejora">
            <ul>
                <?php foreach ($tips_mejora as $tip): ?>
                    <li><?php echo $tip; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <button class="accordion" onclick="toggleAccordion('recomendaciones')">Otras recomendaciones</button>
        <div class="panel" id="recomendaciones">
            <p>Recomendaciones adicionales para tu farmacia.</p>
            <form>
                <input type="text" name="nombre" placeholder="Nombre (sin apellidos)">
                <input type="email" name="email" placeholder="Email">
                <button type="submit">¡Me Apunto!</button>
            </form>
        </div>
    </div>
</body>
</html>
