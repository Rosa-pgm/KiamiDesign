<?php
session_start();
require 'includes/init_admin.php';

header('Content-Type: application/json');

// Verificar se é POST e tem ID
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'])) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Requisição inválida']);
    exit;
}

$id = intval($_POST['id']);

// Verificar se a obra existe
$stmt = $pdo->prepare("SELECT titulo FROM obra WHERE id = ?");
$stmt->execute([$id]);
$obra = $stmt->fetch();

if (!$obra) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Obra não encontrada']);
    exit;
}

// Reativar como "Indisponível" (ID = 6)
$sql = "UPDATE obra SET estado_id = 6 WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);

echo json_encode([
    'sucesso' => true,
    'mensagem' => 'Obra reativada com sucesso!',
    'titulo' => $obra['titulo'],
    'id' => $id
]);
exit;
?>