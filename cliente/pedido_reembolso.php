<?php
session_start();
require_once '../includes/db.php';
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;

if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['id'];
$pagamento_id = $_POST['pagamento_id'];
$venda_id = $_POST['venda_id'];

// Buscar dados do pagamento
$stmt = $pdo->prepare("
    SELECT p.valor, p.metodo_pagamento, u.nome, u.email
    FROM pagamento p
    JOIN venda v ON v.id = p.venda_id
    JOIN utilizador u ON u.id = v.user_id
    WHERE p.id = ?
");
$stmt->execute([$pagamento_id]);
$info = $stmt->fetch();
$valor = $info['valor'];
$metodo = $info['metodo_pagamento'];
$cliente_nome = $info['nome'];
$cliente_email = $info['email'];

// Enviar email ao admin
$mail = new PHPMailer(true);
$mail->CharSet = 'UTF-8';
$mail->Encoding = 'base64';

$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'dseuemail@gmail.com';
$mail->Password = 'sua_password_google';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
$mail->Port = 465;

$mail->setFrom('seuemail@gmail.com', 'Kiami Design');
$mail->addAddress('seuemail@gmail.com.com', 'Admin');

$mail->isHTML(true);
$mail->Subject = "Pedido de Reembolso - Venda #$venda_id";

$mail->Body = "
    <h3>Pedido de Reembolso</h3>
    <p><strong>Cliente:</strong> $cliente_nome ($cliente_email)</p>
    <p><strong>Venda:</strong> #$venda_id</p>
    <p><strong>Pagamento:</strong> #$pagamento_id</p>
    <p><strong>Método:</strong> $metodo</p>
    <p><strong>Valor:</strong> €$valor</p>
";

$mail->send();

$_SESSION['alerta_sucesso'] = "Pedido de reembolso enviado ao administrador.";
header("Location: encomendas.php?reembolso=ok&pedido_id=" . $venda_id);
exit;

