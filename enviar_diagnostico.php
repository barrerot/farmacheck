<?php
require 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

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

    // Configuración de codificación y charset
    $mail->CharSet = PHPMailer::CHARSET_UTF8;

    // Configuración del correo
    $mail->setFrom('from@example.com', 'Mailer');
    $mail->addAddress('joe@example.net', 'Joe User'); // Añadir destinatarios

    $mail->isHTML(true);
    $mail->Subject = 'Aquí está el diagnóstico de tu farmacia';
    $mail->Body    = 'Este es el cuerpo del correo en <b>HTML</b>';
    $mail->AltBody = 'Este es el cuerpo del correo en texto plano para clientes de correo no HTML';

    $mail->send();
    echo 'Mensaje enviado';
} catch (Exception $e) {
    echo "No se pudo enviar el mensaje. Mailer Error: {$mail->ErrorInfo}";
}
