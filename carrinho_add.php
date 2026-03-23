<?php
session_start();
require_once 'includes/db.php';

$id = intval($_POST['id'] ?? 0);

if (!$id) {
    header("Location: loja.php?carrinho=erro");
    exit;
}

if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

// Já no carrinho
if (isset($_SESSION['carrinho'][$id])) {
    header("Location: loja.php?carrinho=ja_no_carrinho");
    exit;
}

$stmt = $pdo->prepare("SELECT id, titulo, preco, imagem FROM obra WHERE id = ?");
$stmt->execute([$id]);
$obra = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$obra) {
    header("Location: loja.php?carrinho=erro");
    exit;
}

// GUARDA SÓ O NOME DA IMAGEM
$_SESSION['carrinho'][$id] = [
    'id'         => $obra['id'],
    'titulo'     => $obra['titulo'],
    'preco'      => $obra['preco'],
    'imagem'     => basename($obra['imagem']),
    'quantidade' => 1
];

header("Location: loja.php?carrinho=ok");
exit;
