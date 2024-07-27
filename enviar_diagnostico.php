<?php
require 'vendor/autoload.php';
require 'generar_diagnostico.php';

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
        // Configuración del servidor
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Configura tu servidor SMTP
        $mail->SMTPAuth = true;
        $mail->Username = 'barrerot@gmail.com'; // Configura tu usuario SMTP
        $mail->Password = 'zwzq khcv vbrs uwch'; // Configura tu contraseña SMTP actualizada
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Remitente y destinatario
        $mail->setFrom('barrerot@gmail.com', 'FarmaCheck');
        $mail->addAddress($email);

        // Contenido del correo
        $mail->isHTML(true);
        $mail->Subject = '=?UTF-8?B?' . base64_encode('Diagnóstico de tu farmacia') . '?=';
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
