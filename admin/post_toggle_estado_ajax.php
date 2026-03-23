<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'admin') {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Não autorizado']);
    exit;
}

require_once '../includes/db.php';

$id = $_POST['id'] ?? 0;
$acao = $_POST['acao'] ?? '';

if (!$id || !$acao) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Dados incompletos']);
    exit;
}

try {
    // Buscar o título do post
    $stmtTitulo = $pdo->prepare("SELECT titulo FROM post WHERE id = ?");
    $stmtTitulo->execute([$id]);
    $titulo = $stmtTitulo->fetchColumn();
    
    if ($acao === 'remover') {
        $novo_estado = 'removido';
        $mensagem = 'Post removido com sucesso!';
    } elseif ($acao === 'publicar') {
        $novo_estado = 'publicado';
        $mensagem = 'Post publicado com sucesso!';
    } else {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Ação inválida']);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE post SET estado = ? WHERE id = ?");
    $stmt->execute([$novo_estado, $id]);

    echo json_encode([
        'sucesso' => true,
        'mensagem' => $mensagem,
        'novo_estado' => $novo_estado,
        'id' => $id,
        'titulo' => $titulo
    ]);

} catch (Exception $e) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro na base de dados']);
}
?>