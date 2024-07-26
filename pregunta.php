<?php
session_start();

$mysqli = new mysqli('localhost', 'carlos_farmacheck', 'AZS12olp..', 'carlos_farmacheck');

if ($mysqli->connect_error) {
    die("Error de conexión: " . $mysqli->connect_error);
}

echo "Conexión a la base de datos establecida.<br>";

// Verificar si la sesión existe, si no, crear una nueva
if (!isset($_SESSION['session_id'])) {
    echo "Creando nueva sesión.<br>";
    $_SESSION['session_id'] = bin2hex(random_bytes(16));
    $stmt = $mysqli->prepare("INSERT INTO sesiones (session_id, completado) VALUES (?, 0)");
    if ($stmt) {
        $stmt->bind_param('s', $_SESSION['session_id']);
        if ($stmt->execute()) {
            echo "Sesión creada y guardada en la base de datos.<br>";
        } else {
            echo "Error al ejecutar la declaración de sesión: " . $stmt->error . "<br>";
        }
        $stmt->close();
    } else {
        die("Error en la preparación de la declaración de sesión: " . $mysqli->error);
    }
}

$session_id = $_SESSION['session_id'];
echo "Session ID: " . $session_id . "<br>";

// Obtener todas las preguntas en el orden en que están en la base de datos
$result = $mysqli->query("SELECT id, nivel, nivel_descripcion, necesidad, descripcion, explicacion, tips FROM preguntas ORDER BY id ASC");
if ($result) {
    echo "Preguntas obtenidas correctamente.<br>";
    $preguntas = $result->fetch_all(MYSQLI_ASSOC);
} else {
    die("Error al obtener preguntas: " . $mysqli->error);
}

// Calcular el progreso
$pregunta_num = isset($_GET['num']) ? (int)$_GET['num'] : 1;
$total_preguntas = count($preguntas);
$progreso = round(($pregunta_num - 1) / $total_preguntas * 100);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $respuesta = $_POST['respuesta'];
    $pregunta_id = $_POST['pregunta_id'];

    $stmt = $mysqli->prepare("INSERT INTO respuestas (session_id, pregunta_id, respuesta) VALUES (?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param('sis', $session_id, $pregunta_id, $respuesta);
        if ($stmt->execute()) {
            echo "Respuesta guardada.<br>";
        } else {
            echo "Error al ejecutar la declaración de respuesta: " . $stmt->error . "<br>";
        }
        $stmt->close();
    } else {
        die("Error en la preparación de la declaración de respuesta: " . $mysqli->error);
    }

    if ($pregunta_num >= $total_preguntas) {
        $stmt = $mysqli->prepare("UPDATE sesiones SET completado = 1 WHERE session_id = ?");
        if ($stmt) {
            $stmt->bind_param('s', $session_id);
            if ($stmt->execute()) {
                echo "Sesión completada.<br>";
            } else {
                echo "Error al ejecutar la declaración de actualización de sesión: " . $stmt->error . "<br>";
            }
            $stmt->close();
        } else {
            die("Error en la preparación de la declaración de actualización de sesión: " . $mysqli->error);
        }
        header('Location: diagnostico.php');
        exit();
    } else {
        header('Refresh: 0.5; URL=pregunta.php?num=' . ($pregunta_num + 1));
        exit();
    }
}

$pregunta_actual = $preguntas[$pregunta_num - 1];

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pregunta</title>
    <link rel="stylesheet" href="src/css/styles.css">
    <style>
        .option.selected {
            background-color: #00d1a1;
            color: #ffffff;
        }

        .option:hover {
            background-color: #e0e0e0;
        }
    </style>
    <script>
        function selectOption(button, value) {
            const options = document.querySelectorAll('.option');
            options.forEach(option => option.classList.remove('selected'));
            button.classList.add('selected');
            document.getElementById('respuesta').value = value;
            setTimeout(() => {
                document.getElementById('questionForm').submit();
            }, 500); // 0.5 second delay for transitioning to the next question
        }
    </script>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>PREGUNTA <?php echo $pregunta_num; ?> de <?php echo $total_preguntas; ?></h2>
            <div class="progress-bar-container">
                <div class="progress-bar">
                    <div class="progress" style="width: <?php echo $progreso; ?>%;"></div>
                </div>
                <div class="progress-percentage"><?php echo $progreso; ?>%</div>
            </div>
        </div>
        <div class="content">
            <h3><?php echo htmlspecialchars($pregunta_actual['necesidad']); ?></h3>
            <p><?php echo htmlspecialchars($pregunta_actual['descripcion']); ?></p>
            <form id="questionForm" method="post">
                <input type="hidden" name="pregunta_id" value="<?php echo $pregunta_actual['id']; ?>">
                <input type="hidden" id="respuesta" name="respuesta" value="">
                <button type="button" class="option" onclick="selectOption(this, 'Sí');">Sí</button>
                <button type="button" class="option" onclick="selectOption(this, 'No');">No</button>
            </form>
        </div>
        <div class="navigation">
            <?php if ($pregunta_num > 1): ?>
                <a href="pregunta.php?num=<?php echo $pregunta_num - 1; ?>" class="back-button">Atrás</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
