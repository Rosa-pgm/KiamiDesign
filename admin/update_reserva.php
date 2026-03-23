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
    $_SESSION['mensagem'] = ['texto' => 'ID inválido.', 'tipo' => 'erro'];
    header("Location: reservas_admin.php");
    exit;
}

// Buscar obra
$stmt = $pdo->prepare("SELECT preco FROM obra WHERE id = ?");
$stmt->execute([$id]);
$obra = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$obra) {
    $_SESSION['mensagem'] = ['texto' => 'Obra não encontrada.', 'tipo' => 'erro'];
    header("Location: reservas_admin.php");
    exit;
}

// Impedir marcar como Disponível sem preço
if ($estado_id == 1 && empty($obra['preco'])) {
    $_SESSION['mensagem'] = ['texto' => 'Não pode marcar como Disponível uma obra sem preço.', 'tipo' => 'erro'];
    header("Location: reservas_admin.php");
    exit;
}

// Atualizar estado
$stmt = $pdo->prepare("UPDATE obra SET estado_id = ? WHERE id = ?");
$stmt->execute([$estado_id, $id]);

// Mapeamento dos estados para mensagens mais amigáveis
$estados = [
    1 => 'Disponível',
    2 => 'Vendida',
    3 => 'Reservada'
];

$estado_nome = $estados[$estado_id] ?? 'desconhecido';
$_SESSION['mensagem'] = ['texto' => "Estado da obra atualizado para: $estado_nome", 'tipo' => 'sucesso'];

header("Location: reservas_admin.php");
exit;
?>