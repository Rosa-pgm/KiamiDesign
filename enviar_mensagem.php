<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: mensagem.php");
    exit;
}

$nome = trim($_POST['nome']);
$email = trim($_POST['email']);
$mensagem = trim($_POST['mensagem']);

if (!$nome || !$email || !$mensagem) {
    $_SESSION['mensagem_erro'] = "Preencha todos os campos.";
    header("Location: mensagem.php");
    exit;
}

// Gravar na BD
$stmt = $pdo->prepare("
    INSERT INTO mensagem (nome, email, mensagem)
    VALUES (?, ?, ?)
");
$stmt->execute([$nome, $email, $mensagem]);

// Enviar email com PHPMailer
try {
    $mail = new PHPMailer(true);
    $mail->CharSet = 'UTF-8';

    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'seuemail@gmail.com'; // teu email
    $mail->Password = 'sua_password_google';     // tua senha de app
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;

    // Remetente (sempre tu)
    $mail->setFrom('seuemail@gmail.com', 'Kiami Design');

    // Para onde o pintor recebe
    $mail->addAddress('seuemail@gmail.com', 'Kiami Design');

    // Para onde o pintor responde
    $mail->addReplyTo($email, $nome);
    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';

    $mail->isHTML(true);
    $mail->Subject = 'Nova mensagem do site - Kiami Design';
    $mail->Body = "
        <h3>Nova mensagem enviada pelo formulário do site</h3>
        <p><strong>Nome:</strong> $nome</p>
        <p><strong>Email:</strong> $email</p>
        <p><strong>Mensagem:</strong><br>$mensagem</p>
    ";

    $mail->send();

    $_SESSION['mensagem_sucesso'] = "Mensagem enviada com sucesso!";
} catch (Exception $e) {
    $_SESSION['mensagem_erro'] = "Erro ao enviar email: " . $mail->ErrorInfo;
}

header("Location: mensagem.php");
exit;
