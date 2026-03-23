<?php
session_start();
require_once 'includes/db.php';

header('Content-Type: application/json');

// Verificar se o carrinho existe na sessão
if (!isset($_SESSION['carrinho']) || empty($_SESSION['carrinho'])) {
    echo json_encode([
        'sucesso' => true,
        'contador' => 0,
        'subtotal' => '0',
        'total' => '0'
    ]);
    exit();
}



$carrinho = $_SESSION['carrinho'];
$total = 0;
$quantidade_total = 0;

// Se houver IDs no carrinho, buscar preços atualizados
if (!empty($carrinho)) {
    $ids = implode(',', array_keys($carrinho));
    
    // Buscar preços atualizados das obras
    $stmt = $pdo->query("SELECT id, preco FROM obra WHERE id IN ($ids)");
    $precos = [];
    while ($row = $stmt->fetch()) {
        $precos[$row['id']] = $row['preco'];
    }
    
    // Calcular totais
    foreach ($carrinho as $id_obra => $quantidade) {
        $quantidade_total += $quantidade;
        if (isset($precos[$id_obra])) {
            $total += $precos[$id_obra] * $quantidade;
        }
    }
}

// Formatar valores
$subtotal_formatado = number_format($total, 2, ',', '.');
$total_formatado = number_format($total, 2, ',', '.');

// Retornar JSON
echo json_encode([
    'sucesso' => true,
    'contador' => $quantidade_total,
    'subtotal' => $subtotal_formatado,
    'total' => $total_formatado
]);
?>