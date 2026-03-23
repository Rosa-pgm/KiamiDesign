<?php
session_start();

if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';
require_once '../includes/db.php';

if (!isset($_POST['id'], $_POST['resposta'])) {
    header("Location: mensagens.php");
    exit;
}

$id = $_POST['id'];
$resposta = trim($_POST['resposta']);

/* ============================
   BUSCAR MENSAGEM ORIGINAL
============================ */

$stmt = $pdo->prepare("SELECT * FROM mensagem WHERE id = ?");
$stmt->execute([$id]);
$msg = $stmt->fetch();

if (!$msg) {
    $_SESSION['alerta_erro'] = "Mensagem não encontrada.";
    header("Location: mensagens.php");
    exit;
}

$cliente_nome  = $msg['nome'];
$cliente_email = $msg['email'];
$mensagem_original = $msg['mensagem'];

/* ============================
   ENVIAR EMAIL AO CLIENTE
============================ */

try {
    $mail = new PHPMailer(true);

    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';

    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'seuemail@gmail.com'; 
    $mail->Password = 'sua_password_google';   
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;

    $mail->setFrom('seuemail@gmail.com', 'Kiami Design');
    $mail->addAddress($cliente_email, $cliente_nome);

    $mail->isHTML(true);
    $mail->Subject = "Resposta à sua mensagem - Kiami Design";

    $mail->Body = "
        <p>Olá <strong>$cliente_nome</strong>,</p>

        <p>Recebi a sua mensagem:</p>
        <blockquote>$mensagem_original</blockquote>

        <p><strong>Resposta:</strong></p>
        <p>$resposta</p>

        <br>
        <p>Com os melhores cumprimentos,<br>Kiami.</p>
        <p>— Kiami Design</p>
    ";

    $mail->send();

} catch (Exception $e) {
    $_SESSION['alerta_erro'] = "Erro ao enviar email: " . $mail->ErrorInfo;
    header("Location: mensagens.php");
    exit;
}

/* ============================
   GUARDAR RESPOSTA NA BASE DE DADOS
============================ */

$stmt = $pdo->prepare("
    UPDATE mensagem 
    SET resposta = ?, data_resposta = NOW()
    WHERE id = ?
");
$stmt->execute([$resposta, $id]);

$_SESSION['alerta_sucesso'] = "Resposta enviada com sucesso.";
header("Location: mensagens.php");
exit;
