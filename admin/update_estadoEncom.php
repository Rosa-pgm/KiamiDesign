<?php
session_start();
require_once '../includes/db.php';
require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;

if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$id = $_POST['id'];
$estado = $_POST['estado'];

// Buscar dados da encomenda + cliente
$stmt = $pdo->prepare("
    SELECT e.*, u.email, u.nome
    FROM encomenda_personalizada e
    JOIN utilizador u ON u.id = e.user_id
    WHERE e.id = ?
");
$stmt->execute([$id]);
$info = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$info) {
    $_SESSION['mensagem'] = ['texto' => 'Encomenda não encontrada.', 'tipo' => 'erro'];
    header("Location: encomendas_personalizadas.php");
    exit;
}

try {
    // Atualizar estado
    $pdo->prepare("UPDATE encomenda_personalizada SET estado = ? WHERE id = ?")
        ->execute([$estado, $id]);

    // Enviar email ao cliente
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
    $mail->addAddress($info['email'], $info['nome']);

    $mail->isHTML(true);
    $mail->Subject = "Atualização da sua encomenda personalizada";

    $mail->Body = "
        <h2>Atualização da sua encomenda personalizada</h2>
        <p>Olá <strong>{$info['nome']}</strong>,</p>
        <p>A sua encomenda personalizada foi atualizada para o estado:</p>
        <h3 style='color: #a67b5b;'>{$estado}</h3>
        <p>Obrigado por confiar na Kiami Design.</p>
        <p>— Kiami Design</p>
    ";

    $mail->send();

    $_SESSION['mensagem'] = ['texto' => 'Estado atualizado e email enviado ao cliente.', 'tipo' => 'sucesso'];

} catch (Exception $e) {
    $_SESSION['mensagem'] = ['texto' => 'Erro ao atualizar estado.', 'tipo' => 'erro'];
}

header("Location: encomendas_personalizadas.php");
exit;
?>