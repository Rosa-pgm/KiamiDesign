<?php
$titulo = "Compra Confirmada";
include 'includes/header.php';

$venda_id = $_GET['venda'] ?? null;
?>

<main class="confirmacao">
    <h1>Compra Finalizada</h1>

    <p>A sua encomenda foi registada com sucesso.</p>

    <p>Número da encomenda: <strong>#<?= $venda_id ?></strong></p>

    <a href="cliente/encomendas.php" class="btn">Ver Encomendas</a>
</main>

<?php include 'includes/footer.php'; ?>
