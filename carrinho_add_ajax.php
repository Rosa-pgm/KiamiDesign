<?php
session_start();
require_once 'includes/db.php';

$obra_id = intval($_POST['obra_id'] ?? 0);

if (!$obra_id) {
    echo json_encode(["erro" => "missing_id"]);
    exit;
}

if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

// Se já existe, não duplica
if (!isset($_SESSION['carrinho'][$obra_id])) {

    $stmt = $pdo->prepare("SELECT id, titulo, preco, imagem FROM obra WHERE id = ?");
    $stmt->execute([$obra_id]);
    $obra = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$obra) {
        echo json_encode(["erro" => "obra_nao_existe"]);
        exit;
    }

    $_SESSION['carrinho'][$obra_id] = [
        'id'         => $obra['id'],
        'titulo'     => $obra['titulo'],
        'preco'      => $obra['preco'],
        'imagem'     => basename($obra['imagem']),
        'quantidade' => 1
    ];
}

// No final do ficheiro AJAX:
echo json_encode([
    "estado" => "adicionado",
    "total"  => count($_SESSION['carrinho']) // Isto é o que o JS vai ler
]);
exit;

