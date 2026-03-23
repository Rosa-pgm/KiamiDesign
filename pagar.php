<?php
require_once 'includes/db.php'; 
include 'includes/header.php'; 
require_once 'config.php';
$user_id = $_SESSION['id'];
$stmt = $pdo->prepare("SELECT email FROM utilizadores WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$user_email = $user['email'] ?? null;

try {
    // Criei uma "Sessão de Checkout"
    $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card', 'mb_way'], // Aqui ativei os métodos
        'line_items' => [[
            'price_data' => [
                'currency' => 'eur',
                'product_data' => [
                    'name' => 'Obra de Arte: Teste Kiami',
                ],
                'unit_amount' => 5000, // 50.00€
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        'invoice_creation' => ['enabled' => true],
        'success_url' => 'http://localhost/PAP-14-KiamiDesign/projeto/sucesso.php?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => 'http://localhost/PAP-14-KiamiDesign/projeto/carrinho.php',
    ]);

    // Redireciona o cliente para a página da Stripe
    header("Location: " . $session->url);
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}

include 'includes/footer.php';