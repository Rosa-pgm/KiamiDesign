<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

require_once '../includes/db.php';

$id         = intval($_POST['id'] ?? 0);
$estado_id  = intval($_POST['estado_id'] ?? 0);

if ($id <= 0) {
    $_SESSION['obra_msg'] = "ID inválido.";
    header("Location: loja_admin.php");
    exit;
}

// Buscar obra
$stmt = $pdo->prepare("SELECT preco FROM obra WHERE id = ?");
$stmt->execute([$id]);
$obra = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$obra) {
    $_SESSION['obra_msg'] = "Obra não encontrada.";
    header("Location: loja_admin.php");
    exit;
}

// Impedir marcar como Disponível sem preço
if ($estado_id == 1 && empty($obra['preco'])) {
    $_SESSION['obra_msg'] = "Não pode marcar como Disponível uma obra sem preço.";
    header("Location: loja_admin.php");
    exit;
}

// Atualizar estado
$stmt = $pdo->prepare("UPDATE obra SET estado_id = ? WHERE id = ?");
$stmt->execute([$estado_id, $id]);

$_SESSION['obra_msg'] = "Estado atualizado com sucesso.";
header("Location: loja_dashboard.php");
exit;
