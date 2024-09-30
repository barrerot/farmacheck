<?php
require 'vendor/autoload.php';
require 'fpdf/fpdf.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$mysqli = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME']);

if ($mysqli->connect_error) {
    die("Error de conexión: " . $mysqli->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $session_id = $_POST['session_id'];
    $nivel_actual = $_POST['nivel_actual'];
    $nivel_descripcion = strip_tags(html_entity_decode($_POST['nivel_descripcion']));
    $necesidad = strip_tags(html_entity_decode($_POST['necesidad']));
    $explicacion = strip_tags(html_entity_decode($_POST['explicacion']));
    $tips = strip_tags(html_entity_decode($_POST['tips']));

    if ($nivel_actual && $email && $session_id) {
        // Guardar el correo electrónico en la base de datos
        $stmt = $mysqli->prepare("UPDATE sesiones SET email = ? WHERE session_id = ?");
        $stmt->bind_param('ss', $email, $session_id);
        $stmt->execute();
        $stmt->close();

        // Crear una nueva instancia de FPDF
        $pdf = new FPDF();
        $pdf->AddPage();

        // Header con color ajustado y logo de FarmaCheck
        $pdf->SetFillColor(23, 26, 31); // Color ajustado a rgba(23, 26, 31, 1)
        $pdf->Rect(0, 0, 210, 30, 'F'); // Rectángulo para el header
        $pdf->Image('./public/undefined@2x.png',10,5,30); // Logo FarmaCheck (ajusta la ruta)

        $pdf->Ln(40); // Salto de línea después del header

        // Título
        $pdf->SetFont('Arial', 'B', 24);
        $pdf->Cell(0, 10, utf8_decode('Diagnóstico FarmaCheck'), 0, 1, 'C');
        $pdf->Ln(10);

        // Imagen de nivel de la farmacia (Pirámide)
        $piramide_image = "./src/images/" . strtoupper($nivel_actual) . ".png";
        if (file_exists($piramide_image)) {
            $pdf->Image($piramide_image, 50, 60, 100); // Centrada en la página con ancho de 100 unidades
        } else {
            $pdf->Cell(0, 10, 'Imagen de pirámide no encontrada.', 0, 1, 'C');
        }
        $pdf->Ln(70);

        // Nivel de la farmacia
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->SetTextColor(23, 26, 31); // Color ajustado para el título de la sección
        $pdf->Cell(0, 10, 'Nivel de tu farmacia:', 0, 1);
        $pdf->SetFont('Arial', '', 12);
        $pdf->SetTextColor(0, 0, 0); // Color negro para el texto normal
        $pdf->MultiCell(0, 10, utf8_decode($nivel_descripcion));
        $pdf->Ln(10);

        // Necesidad vital
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->SetTextColor(23, 26, 31); // Color ajustado para el título de la sección
        $pdf->Cell(0, 10, 'Necesidad Vital:', 0, 1);
        $pdf->SetFont('Arial', '', 12);
        $pdf->SetTextColor(0, 0, 0); // Color negro para el texto normal
        $pdf->MultiCell(0, 10, utf8_decode($necesidad));
        $pdf->Ln(5);
        $pdf->MultiCell(0, 10, utf8_decode($explicacion));
        $pdf->Ln(10);

        // Consejos para mejorar
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->SetTextColor(23, 26, 31); // Color ajustado para el título de la sección
        $pdf->Cell(0, 10, 'Consejos para mejorar:', 0, 1);
        $pdf->SetFont('Arial', '', 12);
        $pdf->SetTextColor(0, 0, 0); // Color negro para el texto normal
        $pdf->MultiCell(0, 10, utf8_decode($tips));
        $pdf->Ln(10);

        // Footer con color ajustado y logo de CxLab
        $pdf->SetFillColor(23, 26, 31); // Color ajustado a rgba(23, 26, 31, 1)
        $pdf->Rect(0, 270, 210, 30, 'F'); // Rectángulo para el footer
        $pdf->Image('./public/logo-cxlab-invertido@2x.png',10,275,30); // Logo CxLab (ajusta la ruta)

        

        // Guardar PDF temporalmente
        $pdf_file = tempnam(sys_get_temp_dir(), 'diagnostico') . '.pdf';
        $pdf->Output($pdf_file, 'F');

        // Enviar el PDF por correo
        $mail = new PHPMailer(true);
        try {
            // Configuración del servidor SMTP
            $mail->isSMTP();
            $mail->Host = $_ENV['SMTP_HOST'];
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['SMTP_USER'];
            $mail->Password = $_ENV['SMTP_PASS'];
            $mail->SMTPSecure = 'tls';
            $mail->Port = $_ENV['SMTP_PORT'];

            // Remitente
            $mail->setFrom($_ENV['SMTP_USER'], 'FarmaCheck');

            // Destinatario
            $mail->addAddress($email);

            // Asunto
            $mail->Subject = '=?UTF-8?B?' . base64_encode('👉 Aquí tienes tu diagnóstico') . '?=';

            // Cuerpo del correo
            $mail->isHTML(true); // Usar HTML
            $mail->Body = "<p>Hola!</p>"
                        . "<p>Aquí tienes tu diagnóstico.</p>"
                        . "<p>Pero antes quiero darte <b>3 recomendaciones importantes</b>:</p>"
                        . "<p>La primera es que <b>marques este email como favorito</b> (una estrella en Gmail) para que lo puedas consultar cuando lo necesites.</p>"
                        . "<p>La segunda es que cuando mejores en tu Necesidad Vital (lo más importante), vuelvas a <b><i>FarmaCheck</i></b> y hagas <b>un nuevo diagnóstico</b> para identificar vuestro siguiente reto.</p>"
                        . "<p>Y la tercera es que aproveches tu <b>sesión estratégica gratuita conmigo</b>.</p>"
                        . "<p>Como entenderás esto me consume mucho tiempo y <u>sólo puedo ayudar a un número muy reducido de personas</u>, pulsa en el enlace para ser tú una ellas.</p>"
                        . "<p><b><a href='https://farmacia.cxlab.es/sesiongratis'>Reservar sesión estratégica gratis</a></b></p>"
                        . "<p>Estamos en contacto! 😉</p>"
                        . "<p><b>Dani Segarra</b>.</p>";

            // Adjuntar PDF
            $mail->addAttachment($pdf_file);

            // Enviar correo
            $mail->send();

            // Mostrar mensaje amigable al usuario
            echo '<div style="text-align:center; margin-top:50px;">';
            echo '<h2 style="font-size:24px;">¡El diagnóstico en PDF ha sido enviado a tu correo!</h2>';
            echo '<p style="font-size:18px;">Por favor, revisa tu buzón y descarga el documento.</p>';
            echo '<p style="font-size:18px;">¡Gracias por confiar en FarmaCheck!</p>';
            echo '</div>';

            // Eliminar archivo temporal
            unlink($pdf_file);
        } catch (Exception $e) {
            echo '<div style="text-align:center; margin-top:50px;">';
            echo '<h2 style="font-size:24px; color:red;">No se pudo enviar el correo</h2>';
            echo '<p style="font-size:18px;">Error: ' . $mail->ErrorInfo . '</p>';
            echo '</div>';
        }
    } else {
        echo '<div style="text-align:center; margin-top:50px;">';
        echo '<h2 style="font-size:24px; color:red;">Correo electrónico no válido</h2>';
        echo '<p style="font-size:18px;">Por favor, verifica la dirección de correo e intenta nuevamente.</p>';
        echo '</div>';
    }
} else {
    echo '<div style="text-align:center; margin-top:50px;">';
    echo '<h2 style="font-size:24px; color:red;">Método no permitido</h2>';
    echo '</div>';
}

$mysqli->close();
?>
