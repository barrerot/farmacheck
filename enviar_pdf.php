<?php
require 'vendor/autoload.php';
require 'fpdf/fpdf.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $nivel_actual = isset($_POST['nivel_actual']) ? $_POST['nivel_actual'] : null;
    $nivel_descripcion = strip_tags(html_entity_decode($_POST['nivel_descripcion']));
    $necesidad = strip_tags(html_entity_decode($_POST['necesidad']));
    $explicacion = strip_tags(html_entity_decode($_POST['explicacion']));
    $tips = strip_tags(html_entity_decode($_POST['tips']));

    if ($nivel_actual && $email) {
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

        // Incluir imagenes vector.png y vector1.png con tamaños y posiciones ajustados
        if (file_exists('./public/vector.png')) {
            $pdf->Image('./public/vector.png', 40, 140, 130); // Imagen grande centrada
        }
        if (file_exists('./public/vector1.png')) {
            $pdf->Image('./public/vector1.png', 50, 190, 110); // Imagen grande centrada
        }

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
            $mail->Subject = '=?UTF-8?B?' . base64_encode('Tu diagnóstico de FarmaCheck en PDF') . '?=';

            // Cuerpo del correo
            $mail->Body = 'En este email te adjunto en PDF el resultado del diagnóstico que hemos hecho tras tus respuestas. Pero recuerda que tienes una consultoría gratuita que puedes agendar aquí: https://tidycal.com/danisegarra/taller-como-diagnosticar-la-salud-de-tu-farmacia';

            // Adjuntar PDF
            $mail->addAttachment($pdf_file);

            // Enviar correo
            $mail->send();
            echo 'El diagnóstico en PDF ha sido enviado a tu correo.';

            // Eliminar archivo temporal
            unlink($pdf_file);
        } catch (Exception $e) {
            echo "No se pudo enviar el correo. Error: {$mail->ErrorInfo}";
        }
    } else {
        echo 'Correo electrónico no válido.';
    }
} else {
    echo 'Método no permitido.';
}
?>
