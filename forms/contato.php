<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require '../assets/vendor/php-email-from/PHPMailer/src/Exception.php';
require '../assets/vendor/php-email-from/PHPMailer/src/PHPMailer.php';
require '../assets/vendor/php-email-from/PHPMailer/src/SMTP.php';

$receiving_email_address = 'j.abreu@aluno.ifsp.edu.br'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars(trim($_POST['name'] ?? ''));
    $email = htmlspecialchars(trim($_POST['email'] ?? ''));
    $subject = htmlspecialchars(trim($_POST['subject'] ?? ''));
    $message = htmlspecialchars(trim($_POST['message'] ?? ''));

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        echo "Por favor, preencha todos os campos obrigatórios.";
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Endereço de e-mail inválido.";
        exit;
    }

    try {
        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'julia14.amarques@gmail.com'; 
        $mail->Password   = 'lzkf ovot rsvu ekqp';   
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        $mail->CharSet    = 'UTF-8';
        $mail->setLanguage('pt_br');

        $mail->setFrom($email, $name);
        $mail->addAddress($receiving_email_address);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $body = "<h2>Nova Mensagem de Contato</h2>";
        $body .= "<p><strong>Nome:</strong> " . $name . "</p>";
        $body .= "<p><strong>Email:</strong> " . $email . "</p>";
        $body .= "<p><strong>Assunto:</strong> " . $subject . "</p>";
        $body .= "<p><strong>Mensagem:</strong><br>" . nl2br($message) . "</p>";
        $mail->Body    = $body;
        $mail->AltBody = "Nova Mensagem de Contato:\nNome: " . $name . "\nEmail: " . $email . "\nAssunto: " . $subject . "\nMensagem:\n" . $message; 
        
        $mail->send();
        echo ("OK");

    } catch (Exception $e) {
        echo "Erro ao enviar a mensagem. Detalhes: {$mail->ErrorInfo}";
    }
    exit;

} else {
    echo "Método de requisição inválido.";
    exit;
}
?>