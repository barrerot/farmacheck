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

// Obtener todas las sesiones
$result = $mysqli->query("SELECT id, session_id, fecha, email FROM sesiones WHERE completado = 1 ORDER BY fecha DESC");
$sesiones = $result->fetch_all(MYSQLI_ASSOC);

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
    <!-- Enlaces a tus archivos CSS -->
    <link rel="stylesheet" href="global.css">
    <link rel="stylesheet" href="index.css">
    <style>
        body {
            font-family: var(--font-family-base);
            background-color: var(--color-whitesmoke);
        }

        .admin-container {
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

        td a {
            color: var(--color-primary-dark);
            text-decoration: none;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            padding: 5px 10px;
            border-radius: 4px;
            transition: background-color 0.3s, color 0.3s;
        }

        td a svg {
            margin-right: 8px;
            height: 16px;
            width: 16px;
        }

        td a:hover {
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
    <div class="admin-container">
        <h2>Panel de Administración</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Session ID</th>
                    <th>Fecha</th>
                    <th>Email</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sesiones as $sesion): ?>
                    <tr>
                        <td><?php echo $sesion['id']; ?></td>
                        <td><?php echo $sesion['session_id']; ?></td>
                        <td><?php echo $sesion['fecha']; ?></td>
                        <td><?php echo $sesion['email']; ?></td>
                        <td>
                            <a href="ver_respuestas.php?session_id=<?php echo $sesion['session_id']; ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="16px" height="16px"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M12 4.5C7.75 4.5 3.5 7.61 2 12c1.5 4.39 5.75 7.5 10 7.5s8.5-3.11 10-7.5c-1.5-4.39-5.75-7.5-10-7.5zm0 13c-3.55 0-6.75-2.27-7.84-5.5C5.25 8.77 8.45 6.5 12 6.5s6.75 2.27 7.84 5.5c-1.09 3.23-4.29 5.5-7.84 5.5zm-1-4h2v-2h-2v2zm0-4h2V7h-2v2z"/></svg>
                                Ver Respuestas
                            </a>
                            <a href="diagnostico.php?session_id=<?php echo $sesion['session_id']; ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="16px" height="16px"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M12 2a7 7 0 0 0-7 7v3H3v7h6v-6H7v-3a5 5 0 1 1 10 0v3h-2v6h6v-7h-2V9a7 7 0 0 0-7-7z"/></svg>
                                Ver Diagnóstico
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Footer -->
    <div class="pie">
        <img class="logo-cxlab-invertido" alt="Logo CxLab" src="./public/logo-cxlab-invertido@2x.png">
    </div>
</body>
</html>
