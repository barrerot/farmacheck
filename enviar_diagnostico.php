<?php
require 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $session_id = $_POST['session_id'];
    $email = $_POST['email'];

    $diagnostico = generar_diagnostico_html($session_id);
    $html = $diagnostico['html'];
    $nivel_actual = $diagnostico['nivel_actual'];

    $mail = new PHPMailer(true);

    try {
        // Configuración del servidor SMTP
        $mail->isSMTP();
        $mail->Host = $_ENV['SMTP_HOST'];
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['SMTP_USER'];
        $mail->Password = $_ENV['SMTP_PASS'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $_ENV['SMTP_PORT'];

        // Remitente y destinatario
        $mail->setFrom('from@example.com', 'FarmaCheck'); // Ajusta el remitente según sea necesario
        $mail->addAddress($email); // Usar el correo proporcionado por el usuario

        // Contenido del correo
        $mail->isHTML(true);
        $mail->CharSet = PHPMailer::CHARSET_UTF8;
        $mail->Subject = 'Aquí está el diagnóstico de tu farmacia';
        $mail->Body    = $html;

        // Adjuntar la imagen del nivel
        $nivel_imagen = 'src/images/' . strtoupper($nivel_actual) . '.png';
        $mail->addEmbeddedImage($nivel_imagen, 'nivel_imagen');

        $mail->send();
        echo 'El diagnóstico ha sido enviado por correo electrónico.';
    } catch (Exception $e) {
        echo "Error al enviar el correo: {$mail->ErrorInfo}";
    }
}
?>
