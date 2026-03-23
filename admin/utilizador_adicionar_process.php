<?php
require 'includes/init_admin.php';
require_once '../includes/db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php'; // ajuste o caminho se necessário

$nome     = trim($_POST['nome'] ?? '');
$email    = trim($_POST['email'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');

$_SESSION['form_data'] = [
    'nome' => $nome,
    'email' => $email,
    'telefone' => $telefone
];

$erro = "";

// Validações
if (!$nome || !$email) {
    $erro = "Preencha todos os campos obrigatórios.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $erro = "Email inválido.";
}

// Verificar email duplicado
if (!$erro) {
    $check = $pdo->prepare("SELECT id FROM utilizador WHERE LOWER(email) = LOWER(?)");
    $check->execute([$email]);
    if ($check->rowCount() > 0) {
        $erro = "Este email já está registado.";
    }
}

if ($erro) {
    $_SESSION['mensagem'] = ['texto' => $erro, 'tipo' => 'erro'];
    header("Location: utilizador_adicionar.php");
    exit;
}

// Password padrão
$pass = "Admin1234";
$hash = password_hash($pass, PASSWORD_DEFAULT);

// Inserir admin com flag de alterar password
$stmt = $pdo->prepare("
    INSERT INTO utilizador (nome, email, telefone, password, tipo, estado_conta, alterar_password)
    VALUES (?, ?, ?, ?, 'admin', 'ativa', 1)
");
$stmt->execute([$nome, $email, $telefone ?: null, $hash]);

unset($_SESSION['form_data']);

// Enviar email com instruções
try {
    $mail = new PHPMailer(true);
    $mail->CharSet = 'UTF-8';
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'seuemail@gmail.com';
    $mail->Password = 'sua_password_google';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;

    $mail->setFrom('seuemail@gmail.com', 'Kiami Design');
    $mail->addAddress($email, $nome);
    $mail->isHTML(true);
    $mail->Subject = 'Nova conta de administrador Kiami Design';

    $nomeSeguro = htmlspecialchars($nome, ENT_QUOTES, 'UTF-8');
    $mail->Body = "
    <div style='font-family:Arial,sans-serif; max-width:600px; margin:auto;'>
        <h2>Olá {$nomeSeguro},</h2>
        <p>Uma conta de administrador foi criada para si.</p>
        <p>Sua password temporária é <strong>Admin1234</strong></p>
        <p>Ao entrar, será solicitado alterar sua password imediatamente.</p>
        <p><a href='http://localhost/PAP-14-KiamiDesign/projeto/login.php'>Clique aqui para fazer login</a>.</p>
        <p style='font-size:12px;color:#777'>Se não esperava esta conta, contacte o administrador principal.</p>
        <p>— Kiami Design</p>
    </div>";
    $mail->send();
} catch (Exception $e) {
    error_log("Erro ao enviar email: " . $e->getMessage());
}

// Sucesso
$_SESSION['mensagem'] = ['texto' => 'Administrador criado com sucesso!', 'tipo' => 'sucesso'];
header("Location: utilizadores.php");
exit;
?>