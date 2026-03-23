<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';
require_once '../includes/db.php';

if (!isset($_POST['id'], $_POST['tipo'])) {
    header("Location: encomendas.php");
    exit;
}

$id = $_POST['id'];
$tipo = $_POST['tipo'];
$user_id = $_SESSION['id'];


$stmt = $pdo->prepare("
    SELECT v.id, o.titulo AS obra_titulo, u.nome AS cliente_nome, u.email AS cliente_email
    FROM venda v
    JOIN utilizador u ON u.id = v.user_id
    JOIN venda_item vi ON vi.venda_id = v.id
    JOIN obra o ON o.id = vi.obra_id
    WHERE v.id = ? AND v.user_id = ?
");
$stmt->execute([$id, $user_id]);
$encomenda = $stmt->fetch();


if (!$encomenda) {
    $_SESSION['alerta_erro'] = "Encomenda não encontrada.";
    header("Location: encomendas.php");
    exit;
}

/* ============================
   ENVIAR EMAIL AO PINTOR
============================ */

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
    $mail->addAddress('dseuemail@gmail.com', 'Kiami Design');
    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';

    $mail->isHTML(true);
    $mail->Subject = "Alerta: Encomenda pendente (#$id)";
    $mail->Body = "
    <h3>Compra da Loja Pendente</h3>
    <p><strong>Cliente:</strong> {$encomenda['cliente_nome']} ({$encomenda['cliente_email']})</p>
    <p><strong>Obra:</strong> {$encomenda['obra_titulo']}</p>
    <p>O cliente está à espera de resposta.</p>
";
$mail->SMTPDebug = 2;

    $mail->send();

    $_SESSION['alerta_sucesso'] = "O pintor foi alertado com sucesso.";

} catch (Exception $e) {
    $_SESSION['alerta_erro'] = "Erro ao enviar alerta: " . $mail->ErrorInfo;
}

header("Location: encomendas.php?alerta=ok&pedido_id=" . $id);
exit;
