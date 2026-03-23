<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db.php';

/* USER */
$userLogado = $_SESSION['id'] ?? null;
$tipoUser   = $_SESSION['tipo'] ?? 'guest';

/* ============================
   CARREGAR CARRINHO DA BD
============================ */

if ($userLogado && empty($_SESSION['carrinho'])) {

    $stmt = $pdo->prepare("
        SELECT 
            ic.obra_id,
            ic.quantidade,
            ic.preco_unitario,
            o.titulo,
            o.imagem
        FROM item_carrinho ic
        JOIN carrinho c ON ic.carrinho_id = c.id
        JOIN obra o ON ic.obra_id = o.id
        WHERE c.user_id = ?
    ");

    $stmt->execute([$userLogado]);

    $carrinho = [];

    while ($item = $stmt->fetch(PDO::FETCH_ASSOC)) {

        $carrinho[$item['obra_id']] = [
            'id' => $item['obra_id'],
            'titulo' => $item['titulo'],
            'imagem' => $item['imagem'],
            'preco' => $item['preco_unitario'],
            'quantidade' => $item['quantidade']
        ];
    }

    $_SESSION['carrinho'] = $carrinho;
}

/* ESTADO DO CARRINHO */
$carrinhoAtivo = !empty($_SESSION['carrinho']);