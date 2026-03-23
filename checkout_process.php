<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/obra_estado.php';
require_once 'config.php';

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['id'];
$carrinho = $_SESSION['carrinho'] ?? [];

if (empty($carrinho)) {
    header("Location: carrinho.php");
    exit;
}

// Dados do formulário
$nome = $_POST['nome_destinatario'];
$morada = $_POST['morada'];
$telefone = $_POST['telefone'];
$metodo = $_POST['metodo_pagamento'];

// Atualizar dados do utilizador
$stmt = $pdo->prepare("
    UPDATE utilizador 
    SET nome = ?, morada = ?, telefone = ?
    WHERE id = ?
");
$stmt->execute([$nome, $morada, $telefone, $user_id]);

// Calcular total
$total = 0;
foreach ($carrinho as $item) {
    $total += $item['preco'] * $item['quantidade'];
}
// Converter método técnico → método humano
$metodo_humano = match($metodo) {
    'transferencia' => 'Cartão Bancário',
    'mbway' => 'MBWay',
    'coordenar_pintor' => 'Coordenar com o pintor',
    default => 'Desconhecido'
};
/* ============================================================
   CASO 1: COORDENAR COM O PINTOR
=============================================================== */
if ($metodo === 'coordenar_pintor') {

    $_SESSION['temp_coordenar'] = [
        'nome'      => $nome,
        'morada'    => $morada,
        'telefone'  => $telefone,
        'metodo'    => 'Coordenar com o pintor', // ← TEXTO BONITO AQUI
        'total'     => $total,
        'carrinho'  => $carrinho
    ];

    header("Location: coordenar.php");
    exit;
}


/* ============================================================
   CASO 2: STRIPE (cartão / MBWay / transferência Stripe)
=============================================================== */



$_SESSION['temp_compra'] = [
    'nome'      => $nome,
    'morada'    => $morada,
    'telefone'  => $telefone,
    'metodo'    => $metodo_humano,
    'total'     => $total,
    'carrinho'  => $carrinho
];


try {
    $metodos = [];

    if ($metodo === 'transferencia') {
        $metodos = ['card'];
    } elseif ($metodo === 'mbway') {
        $metodos = ['mb_way'];
    }

    $line_items = array_map(function($item) {
        return [
            'price_data' => [
                'currency' => 'eur',
                'product_data' => ['name' => $item['titulo']],
                'unit_amount' => (int)round($item['preco'] * 100),
            ],
            'quantity' => $item['quantidade'],
        ];
    }, array_values($carrinho));

    $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => $metodos,
        'line_items' => $line_items,
        'mode' => 'payment',
        'success_url' => 'http://localhost/PAP-14-KiamiDesign/projeto/sucesso.php?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => 'http://localhost/PAP-14-KiamiDesign/projeto/checkout.php',
    ]);

    header("Location: " . $session->url);
    exit;

} catch (Exception $e) {
    die("Erro ao conectar à Stripe: " . $e->getMessage());
}
