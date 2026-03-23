<?php
session_start();
header('Content-Type: application/json');

// Verificar se foi enviado o ID
if (!isset($_POST['id'])) {
    echo json_encode(['sucesso' => false, 'erro' => 'ID não fornecido']);
    exit();
}

$id_obra = (int)$_POST['id'];

// Verificar se o carrinho existe
if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

// Remover o item do carrinho
if (isset($_SESSION['carrinho'][$id_obra])) {
    unset($_SESSION['carrinho'][$id_obra]);
}

// Calcular novo total e contador
$quantidade_total = 0;
$total = 0;

// Incluir conexão com o banco de dados
require_once 'includes/db.php'; 

if (!empty($_SESSION['carrinho'])) {
    $ids = implode(',', array_keys($_SESSION['carrinho']));
    
    // Buscar preços atualizados
    $stmt = $pdo->query("SELECT id, preco FROM obra WHERE id IN ($ids)");
    $precos = [];
    while ($row = $stmt->fetch()) {
        $precos[$row['id']] = $row['preco'];
    }
    
    // Calcular totais
    foreach ($_SESSION['carrinho'] as $id => $item) {
    $quantidade_total += $item['quantidade'];
    if (isset($precos[$id])) {
        $total += $precos[$id] * $item['quantidade'];
    }
}

}

// Formatar total
$total_formatado = number_format($total, 2, ',', '.');

// Retornar resposta
echo json_encode([
    'sucesso' => true,
    'contador' => $quantidade_total,
    'totalGeral' => $total_formatado . ' €'
]);
?>