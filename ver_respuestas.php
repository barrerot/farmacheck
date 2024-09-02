<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

require 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$mysqli = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME']);

if ($mysqli->connect_error) {
    die("Error de conexión: " . $mysqli->connect_error);
}

$session_id = $_GET['session_id'];

// Obtener respuestas para la sesión
$result = $mysqli->query("SELECT p.necesidad, r.respuesta 
                          FROM respuestas r 
                          JOIN preguntas p ON r.pregunta_id = p.id 
                          WHERE r.session_id = '$session_id'");
$respuestas = $result->fetch_all(MYSQLI_ASSOC);

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Respuestas</title>
    <!-- Enlaces a tus archivos CSS -->
    <link rel="stylesheet" href="global.css">
    <link rel="stylesheet" href="index.css">
    <style>
        body {
            font-family: var(--font-family-base);
            background-color: var(--color-whitesmoke);
        }

        .respuestas-container {
            padding: var(--padding-xl);
            max-width: 1200px;
            margin: 0 auto;
            background-color: var(--color-white);
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        h2 {
            color: var(--color-primary-dark);
            font-size: 24px;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-family: var(--font-family-base);
            background-color: var(--color-white);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }

        th, td {
            padding: 15px;
            text-align: left;
            font-size: var(--font-size-base);
        }

        th {
            background-color: var(--color-primary);
            color: var(--color-white);
            font-weight: bold;
        }

        tr:nth-child(even) {
            background-color: var(--color-whitesmoke);
        }

        tr:hover {
            background-color: var(--color-light-gray);
        }

        .respuesta-icon {
            display: inline-flex;
            align-items: center;
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
            font-size: var(--font-size-base);
        }

        .respuesta-si {
            color: var(--color-success);
            background-color: rgba(0, 200, 83, 0.1); /* Verde claro */
        }

        .respuesta-no {
            color: var(--color-error);
            background-color: rgba(244, 67, 54, 0.1); /* Rojo claro */
        }

        .respuesta-icon svg {
            margin-right: 8px;
            height: 16px;
            width: 16px;
        }

        .volver-panel {
            color: var(--color-primary-dark);
            text-decoration: none;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            padding: 10px 15px;
            border-radius: 4px;
            transition: background-color 0.3s, color 0.3s;
            border: 1px solid var(--color-primary-dark);
        }

        .volver-panel:hover {
            background-color: var(--color-primary-dark);
            color: var(--color-white);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="cabecera-azul4">
        <img class="icon2" alt="FarmaCheck Logo" src="./public/undefined@2x.png">
    </div>

    <!-- Contenido Principal -->
    <div class="respuestas-container">
        <h2>Respuestas para la sesión: <?php echo htmlspecialchars($session_id); ?></h2>
        <table>
            <thead>
                <tr>
                    <th>Pregunta</th>
                    <th>Respuesta</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($respuestas as $respuesta): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($respuesta['necesidad']); ?></td>
                        <td>
                            <?php if ($respuesta['respuesta'] === 'Sí'): ?>
                                <span class="respuesta-icon respuesta-si">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M9 16.17l-4.17-4.17L3 13.83l6 6 12-12L19.17 7 9 16.17z"/></svg>
                                    Sí
                                </span>
                            <?php else: ?>
                                <span class="respuesta-icon respuesta-no">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm5 13.59L15.59 17 12 13.41 8.41 17 7 15.59 10.59 12 7 8.41 8.41 7 12 10.59 15.59 7 17 8.41 13.41 12 17 15.59z"/></svg>
                                    No
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="admin_panel.php" class="volver-panel">
            Volver al Panel
        </a>
    </div>

    <!-- Footer -->
    <div class="pie">
        <img class="logo-cxlab-invertido" alt="Logo CxLab" src="./public/logo-cxlab-invertido@2x.png">
    </div>
</body>
</html>
