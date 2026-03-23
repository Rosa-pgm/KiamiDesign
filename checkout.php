<?php
session_start();
$titulo = "Checkout";
include 'includes/header.php';
require_once 'includes/db.php';

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$carrinho = $_SESSION['carrinho'] ?? [];

if (empty($carrinho)) {
    header("Location: carrinho.php");
    exit;
}

$user_id = $_SESSION['id'];

$stmt = $pdo->prepare("
    SELECT nome, morada, telefone 
    FROM utilizador 
    WHERE id = ?
");
$stmt->execute([$user_id]);
$dados = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<main class="checkout-wrapper">
    <div class="checkout">
        <div class="checkout-titulo">
            <h1>Finalizar Compra</h1>
        </div>

        <form action="checkout_process.php" method="POST" class="checkout-form">

        <h2>Dados de Envio</h2>

        <label for="nome_destinatario">Nome do destinatário</label>
        <input type="text" name="nome_destinatario" id="nome_destinatario"
               value="<?= htmlspecialchars($dados['nome'] ?? '') ?>" required>

        <label for="morada">Morada (rua, código postal, cidade e país)</label>
        <input type="text" name="morada" id="morada"
               value="<?= htmlspecialchars($dados['morada'] ?? '') ?>" required>

        <label for="telefone">Telefone (com indicativo)</label>
        <input type="tel" name="telefone" id="telefone"
               value="<?= htmlspecialchars($dados['telefone'] ?? '') ?>"
               required>

        <h2>Método de Pagamento</h2>

        <select name="metodo_pagamento" required>
            <option value="transferencia">Cartão Bancário</option>
            <option value="mbway">MBWay</option>
            <option value="coordenar_pintor">Coordenar com o pintor</option>
        </select>

        <button type="submit" class="btn-finalizar">Confirmar Compra</button>
        
        <a href="carrinho.php" class="btn-voltar">
            <i class="fa-solid fa-arrow-left"></i> Voltar ao Carrinho
        </a>
        

    </form>

</main>



<?php include 'includes/footer.php'; ?>