<?php
session_start();
require 'includes/init_admin.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'])) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Requisição inválida']);
    exit;
}

$id = intval($_POST['id']);

// 🔴 VERIFICAR PRIMEIRO SE ESTÁ EM DESTAQUE
$stmt = $pdo->prepare("SELECT titulo, estado_id, destaque FROM obra WHERE id = ?");
$stmt->execute([$id]);
$obra = $stmt->fetch();

if (!$obra) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Obra não encontrada']);
    exit;
}

// 🔴 SE ESTIVER EM DESTAQUE, RETORNAR ERRO ESPECÍFICO
if ($obra['destaque'] == 1) {
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'destaque',  // Esta string é importante!
        'titulo' => $obra['titulo']
    ]);
    exit;
}

// ID do estado "Arquivada"
$estado_arquivada_id = 4;

// Verificar se já está arquivada
if ($obra['estado_id'] == $estado_arquivada_id) {
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Esta obra já está arquivada'
    ]);
    exit;
}

// Atualizar o estado para "Arquivada"
$stmt = $pdo->prepare("UPDATE obra SET estado_id = ? WHERE id = ?");
$stmt->execute([$estado_arquivada_id, $id]);

echo json_encode([
    'sucesso' => true,
    'mensagem' => 'Obra arquivada com sucesso',
    'titulo' => $obra['titulo']
]);
exit;
?>