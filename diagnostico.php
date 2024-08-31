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
    <link rel="stylesheet" href="./global.css">
    <link rel="stylesheet" href="./index.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,400;0,700;0,800;1,400;1,700&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap">
</head>
<body>
    <div class="escritorio1">
        <div class="cabecera-azul4">
            <img class="icon2" alt="FarmaCheck Logo" src="./public/undefined@2x.png">
        </div>
        <div class="contenido2">
            <div class="introduccin">
                <div class="enhorabuena-parent">
                    <div class="enhorabuena">¡Enhorabuena!</div>
                    <div class="aqu-tienes-tu">Aquí tienes tu diagnóstico</div>
                </div>
                <div class="intro-texto-container">
                    <p class="muchas-gracias-por-completar-l">
                        <span class="muchas-gracias-por">Muchas gracias por completar la evaluación online de </span>
                        <i class="farmacheck">FarmaCheck</i>
                        <span>. </span>
                    </p>
                    <p class="muchas-gracias-por-completar-l">
                        Estamos seguros de que la información que encontrarás a continuación te hará reflexionar y cambiar la forma en que ves tu farmacia.
                    </p>
                    <p class="te-deseamos-muchos">Te deseamos muchos éxitos.</p>
                </div>
                <div class="advertencia-texto-predefinid">
                    <div class="contenido3">
                        <b class="ttulo">Nota importante</b>
                        <div class="nota">
                            <p class="la-precisin-de">
                                La precisión de este diagnóstico depende directamente de la calidad de tus respuestas.
                            </p>
                            <p class="te-deseamos-muchos">
                                <span>Antes de tomar acción, te recomendamos que consultes tus resultados con un </span>
                                <b class="consultor-certificado-oficialm">consultor certificado oficialmente en Experiencia de Cliente y con experiencia en Farmacias</b>
                                <span>.</span>
                            </p>
                        </div>
                    </div>
                </div>
                <img class="divider-icon" alt="" src="./public/divider.svg">
            </div>

            <!-- Sección Nivel -->
            <div class="seccin-nivel">
                <div class="imagen-nivel-wrapper">
                    <img class="imagen-nivel-icon" alt="Pirámide de Nivel" src="src/images/<?php echo strtoupper($nivel_actual); ?>.png">
                </div>
                <b class="el-nivel-de">El nivel de tu farmacia</b>
                <div class="texto-generado-container">
                    <p><?php echo $nivel_descripcion; ?></p>
                </div>
                <img class="divider-icon" alt="" src="./public/divider1.svg">
            </div>

            <!-- Sección Prioridad -->
            <div class="seccin-prioridad">
                <div class="imagen-nivel-wrapper">
                    <img class="vector-icon" alt="" src="./public/vector.svg">
                </div>
                <b class="el-nivel-de">La prioridad de la farmacia</b>
                <div class="texto-generado-container">
                    <p class="muchas-gracias-por-completar-l">Pueden haber muchas cosas importantes para hacer pero, de todas ellas, sólo una es la MÁS IMPORTANTE.</p>
                    <p class="muchas-gracias-por-completar-l">
                        <span>Esta tarea más importante o prioritaria es lo que nosotros llamamos vuestra </span>
                        <b class="muchas-gracias-por">Necesidad Vital</b><span>. </span>
                    </p>
                    <p class="te-deseamos-muchos">
                        <span>Cuando identificas la Necesidad Vital de tu farmacia y mejoras en ella, aunque sea un poco, te darás cuenta de que </span>
                        <b class="muchas-gracias-por">avanzarás mucho más rápido y fácil en las siguientes necesidades</b><span>.</span>
                    </p>
                </div>
                <div class="texto-generado-container">
                    <p><?php echo html_entity_decode($necesidad_vital['necesidad']); ?></p>
                    <p><?php echo html_entity_decode($necesidad_vital['explicacion']); ?></p>
                </div>
                <img class="divider-icon" alt="" src="./public/divider2.svg">
            </div>

            <!-- Sección Consejos -->
            <div class="seccin-prioridad">
                <div class="imagen-nivel-wrapper">
                    <img class="vector-icon1" alt="" src="./public/vector1.svg">
                </div>
                <b class="el-nivel-de">Consejos para mejorar</b>
                <div class="texto-generado-container">
                    <p class="muchas-gracias-por-completar-l"><span class="muchas-gracias-por">¡Estupendo!</span></p>
                    <p class="muchas-gracias-por-completar-l">
                        <span class="muchas-gracias-por">Ya conoces el nivel de desarrollo de tu farmacia y también has descubierto cuál es el aspecto MÁS importante en el que os debéis enfocar ahora: vuestra Necesidad Vital.</span>
                    </p>
                    <p class="muchas-gracias-por-completar-l">
                        <span class="muchas-gracias-por">Ahora es el momento de ponerse manos a la obra pero, ¿qué puedes hacer?</span>
                    </p>
                    <p class="muchas-gracias-por-completar-l">
                        <b class="muchas-gracias-por">A continuación te voy a dar un consejo sencillo de aplicar para que empieces a mejorar desde mañana mismo en vuestra Necesidad Vital.</b>
                    </p>
                </div>
                <div class="texto-generado-container">
                    <p><?php echo html_entity_decode($necesidad_vital['tips']); ?></p>
                </div>
                <img class="divider-icon" alt="" src="./public/divider3.svg">
            </div>

            <!-- Últimas recomendaciones -->
            <div class="seccin-prioridad">
                <b class="el-nivel-de">Últimas recomendaciones</b>
                <div class="texto-generado-container">
                    <span>Ahora te quiero dar dos recomendaciones que te ayudarán a llevar tu farmacia</span>
                    <b>al siguiente nivel</b>
                    <span>.</span>
                </div>
                <div class="texto-generado-container">
                    <p class="muchas-gracias-por-completar-l">La primera es que NO seas perfeccionista.</p>
                    <p class="muchas-gracias-por-completar-l">El momento perfecto no existe. Si esperas a que sea el momento perfecto, no harás nada.</p>
                    <p class="muchas-gracias-por-completar-l">Actúa ahora.</p>
                    <p class="el-momento-perfecto-es-ahora"><b>El momento perfecto es AHORA.</b></p>
                    <p class="muchas-gracias-por-completar-l">
                        <span>En lugar de eso, </span>
                        <b class="muchas-gracias-por">intenta mejorar sólo un 10%</b>
                        <span> y pasa a otra necesidad (volviendo a hacer el diagnóstico de </span>
                        <i class="farmacheck">FarmaCheck</i><span>). No quedes pegado en una toda el tiempo.</span>
                    </p>
                    <p class="muchas-gracias-por-completar-l">
                        Se trata de ser ágil y AVANZAR poco a poco, pero de manera constante.
                    </p>
                    <p class="te-deseamos-muchos">
                        <span>El segundo consejo es que aproveches </span>
                        <b class="muchas-gracias-por">tu SESIÓN GRATIS DE CONSULTORÍA conmigo</b>
                        <span>, donde podrás preguntarme tus dudas y los mejores consejos y prácticas que he ido aprendiendo a lo largo de los últimos 8 años.</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- CTA -->
        <div class="cta">
            <div class="cta1">
                <b class="texto-cta-">Pulsa el botón para reservar una sesión GRATIS con uno de nuestros expertos.</b>
                <div class="botn-base-frame">
                    <a href="https://tidycal.com/danisegarra/taller-como-diagnosticar-la-salud-de-tu-farmacia" class="botn-base2">
                        <b class="quiero-mi-sesin">Quiero mi Sesión Gratis</b>
                    </a>
                </div>
            </div>
        </div>

        <!-- Sección para solicitar diagnóstico en PDF -->
        <div class="seccin-prioridad">
            <div class="texto-generado-container">
                <b class="el-nivel-de">Obtén tu diagnóstico en PDF</b>
                <p>Introduce tu correo electrónico y te enviaremos tu diagnóstico en formato PDF.</p>
                <form action="enviar_pdf.php" method="POST">
                    <input type="hidden" name="nivel_actual" value="<?php echo htmlspecialchars($nivel_actual); ?>">
                    <input type="hidden" name="nivel_descripcion" value="<?php echo htmlspecialchars($nivel_descripcion); ?>">
                    <input type="hidden" name="necesidad" value="<?php echo htmlspecialchars($necesidad_vital['necesidad']); ?>">
                    <input type="hidden" name="explicacion" value="<?php echo htmlspecialchars($necesidad_vital['explicacion']); ?>">
                    <input type="hidden" name="tips" value="<?php echo htmlspecialchars($necesidad_vital['tips']); ?>">
                    <input type="email" name="email" placeholder="Tu correo electrónico" required class="input-email">
                    <button type="submit" class="boton-enviar-pdf">Quiero mi diagnóstico en PDF</button>
                </form>
            </div>
        </div>

        <!-- Pie de página -->
        <div class="pie">
            <img class="logo-cxlab-invertido" alt="Logo CxLab" src="./public/logo-cxlab-invertido@2x.png">
        </div>
    </div>
</body>
</html>
