<?php
session_start();
require 'includes/init_admin.php';

header('Content-Type: application/json');

if (!isset($_POST['id']) || !isset($_POST['acao'])) {
    echo json_encode(['sucesso' => false, 'erro' => 'Parâmetros inválidos']);
    exit;
}

$id = intval($_POST['id']);
$acao = $_POST['acao'];

// Verificar quantas obras já estão em destaque
$stmt = $pdo->query("SELECT COUNT(*) FROM obra WHERE destaque = 1");
$totalDestaques = $stmt->fetchColumn();

// Se for adicionar, verificar limite
if ($acao === 'adicionar' && $totalDestaques >= 3) {
    echo json_encode([
        'sucesso' => false,
        'erro' => 'limite',
        'mensagem' => 'Só é permitido ter 3 obras em destaque. Remova uma antes de adicionar outra.'
    ]);
    exit;
}

// Buscar título da obra para mensagem
$stmt = $pdo->prepare("SELECT titulo FROM obra WHERE id = ?");
$stmt->execute([$id]);
$obra = $stmt->fetch();

if (!$obra) {
    echo json_encode(['sucesso' => false, 'erro' => 'Obra não encontrada']);
    exit;
}

// Atualizar destaque
if ($acao === 'adicionar') {
    $novoDestaque = 1;
    $mensagem = "Obra adicionada aos destaques com sucesso!";
} else {
    $novoDestaque = 0;
    $mensagem = "Obra removida dos destaques com sucesso!";
}

$stmt = $pdo->prepare("UPDATE obra SET destaque = ? WHERE id = ?");
$stmt->execute([$novoDestaque, $id]);

// Buscar novo total de destaques
$stmt = $pdo->query("SELECT COUNT(*) FROM obra WHERE destaque = 1");
$novoTotal = $stmt->fetchColumn();

echo json_encode([
    'sucesso' => true,
    'acao' => $acao,
    'mensagem' => $mensagem,
    'totalDestaques' => $novoTotal,
    'titulo' => $obra['titulo']
]);
exit;
?>