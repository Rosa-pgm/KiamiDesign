<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

require_once '../includes/db.php';
require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;

$pagamento_id = $_POST['pagamento_id'] ?? null;
$venda_id     = $_POST['venda_id'] ?? null;

if (!$pagamento_id || !$venda_id) {
    $_SESSION['mensagem'] = ['texto' => 'Erro: pagamento ou venda não encontrados.', 'tipo' => 'erro'];
    header("Location: pagamentos.php");
    exit;
}

/* Buscar dados */
$stmt = $pdo->prepare("
    SELECT 
        u.email, u.nome,
        o.id AS obra_id,
        p.valor
    FROM pagamento p
    JOIN venda v ON v.id = p.venda_id
    JOIN venda_item vi ON vi.venda_id = v.id
    JOIN obra o ON o.id = vi.obra_id
    JOIN utilizador u ON u.id = v.user_id
    WHERE p.id = ?
");
$stmt->execute([$pagamento_id]);
$info = $stmt->fetch();

if (!$info) {
    $_SESSION['mensagem'] = ['texto' => 'Erro: pagamento ou venda não encontrados.', 'tipo' => 'erro'];
    header("Location: pagamentos.php");
    exit;
}

try {
    /* 1. Atualizar pagamento */
    $pdo->prepare("UPDATE pagamento SET estado = 'Reembolsado' WHERE id = ?")
        ->execute([$pagamento_id]);

    /* 2. Libertar obra */
    $pdo->prepare("
        UPDATE obra 
        SET estado_id = (SELECT id FROM estado_obra WHERE nome = 'Disponível')
        WHERE id = ?
    ")->execute([$info['obra_id']]);

    /* 3. Cancelar venda */
    $pdo->prepare("UPDATE venda SET estado = 'Cancelada' WHERE id = ?")
        ->execute([$venda_id]);

    /* 4. Enviar email ao cliente */
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
    $mail->Subject = "Reembolso confirmado — Encomenda cancelada";

    $mail->Body = "
        <h2>Reembolso confirmado</h2>
        <p>Olá <strong>{$info['nome']}</strong>,</p>
        <p>O seu pagamento de <strong>{$info['valor']}€</strong> foi reembolsado.</p>
        <p>A encomenda foi cancelada e a obra voltou a estar disponível.</p>
        <p>— Kiami Design</p>
    ";

    $mail->send();

    // ===== REDIRECIONAMENTO COM MENSAGEM PADRONIZADA =====
    $_SESSION['mensagem'] = ['texto' => 'Reembolso confirmado com sucesso!', 'tipo' => 'sucesso'];
    
} catch (Exception $e) {
    $_SESSION['mensagem'] = ['texto' => 'Erro ao processar reembolso: ' . $e->getMessage(), 'tipo' => 'erro'];
}

header("Location: pagamentos.php");
exit;
?>