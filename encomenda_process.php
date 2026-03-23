<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
require_once 'includes/db.php';

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: encomenda.php");
    exit;
}
if (strlen($_POST['descricao']) < 15) {
    $_SESSION['erro'] = 'A descrição deve ter pelo menos 15 caracteres.';
    header('Location: encomenda.php');
    exit;
}
$user_id = $_SESSION['id'];
$descricao = trim($_POST['descricao']);

if (!$descricao) {
    $_SESSION['encomenda_erro'] = "Descreva a obra que deseja encomendar.";
    header("Location: encomenda.php");
    exit;
}

/* ===============================
   Upload da imagem (opcional)
================================ */
$imagemNome = null;

if (!empty($_FILES['imagem']['name'])) {

    $imagemNome = time() . "_" . basename($_FILES['imagem']['name']);
    $basePath = "assets/img/encomendas_personalizadas/";
    $destino = $basePath . $imagemNome;

    if (!is_dir($basePath)) {
        mkdir($basePath, 0777, true);
    }

    if (!move_uploaded_file($_FILES['imagem']['tmp_name'], $destino)) {
        $_SESSION['encomenda_erro'] = "Erro ao carregar a imagem.";
        header("Location: encomenda.php");
        exit;
    }
}

/* ===============================
   Enviar email
================================ */
try {

    $mail = new PHPMailer(true);
    $mail->CharSet = 'UTF-8';

    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'seuemail@gmail.com';
    $mail->Password = 'sua_google_password';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;

    $mail->setFrom('seuemail@gmail.com', 'Kiami Design');
    $mail->addAddress('seuemail@gmail.com');

    if (!empty($_SESSION['email'])) {
        $mail->addReplyTo($_SESSION['email'], $_SESSION['nome'] ?? 'Cliente');
    }

    $mail->isHTML(true);
    $mail->Subject = 'Nova Encomenda Personalizada';

    $mail->Body = "
        <h3>Nova encomenda personalizada</h3>
        <p><strong>Cliente:</strong> {$_SESSION['nome']} (ID {$user_id})</p>
        <p><strong>Descrição:</strong><br>" . nl2br(htmlspecialchars($descricao)) . "</p>
    ";

    if ($imagemNome) {
        $mail->addAttachment("assets/img/encomendas_personalizadas/$imagemNome");
    }

    $mail->send();

} catch (Exception $e) {

    // NUNCA mostrar erro técnico ao utilizador
    $_SESSION['encomenda_erro'] = "Não foi possível enviar o pedido neste momento. Tente novamente mais tarde.";
    header("Location: encomenda.php");
    exit;
}

/* ===============================
   Guardar na BD (SÓ SE EMAIL OK)
================================ */
$stmt = $pdo->prepare("
    INSERT INTO encomenda_personalizada (user_id, descricao, imagem, estado)
    VALUES (?, ?, ?, 'Pendente')
");
$stmt->execute([$user_id, $descricao, $imagemNome]);

$_SESSION['encomenda_sucesso'] = "Pedido enviado com sucesso! Entraremos em contacto em breve.";
header("Location: encomenda.php");
exit;