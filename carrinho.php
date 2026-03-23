<?php
session_start();
$titulo = "Carrinho";
include 'includes/header.php';

$carrinho = $_SESSION['carrinho'] ?? [];
?>

<main class="carrinho">

    <div class="carrinho-header linha-decorativa">
    <h1>Meu Carrinho</h1>
        
    </div>

    <?php if (empty($carrinho)): ?>
        <div class="carrinho-vazio">
            <div class="vazio-icone">
                <i class="fa-solid fa-cart-shopping"></i>
            </div>
            <h2>Seu carrinho está vazio</h2>
            <p>Explore obras incríveis e comece sua coleção</p>
            <a href="loja.php" class="btn">Explorar Loja</a>
        </div>
    <?php else: ?>

    <div class="carrinho-content">
        <div class="carrinho-items">
            <?php foreach ($carrinho as $id_obra => $item): ?>
            <div class="carrinho-item" id="item-<?= $id_obra ?>">
                <div class="item-imagem">
                    <img src="assets/img/obras/<?= htmlspecialchars($item['imagem']) ?>" alt="<?= htmlspecialchars($item['titulo']) ?>">
                </div>
                
                <div class="item-info">
                    <h3 class="item-titulo"><?= htmlspecialchars($item['titulo']) ?></h3>
                    <div class="item-detalhes">
                        <span class="item-preco"><?= number_format($item['preco'], 2, ',', '.') ?> €</span>
                        <span class="item-quantidade">Quantidade: <?= $item['quantidade'] ?></span>
                    </div>
                </div>
                
                <div class="item-total">
                    <span class="total-label">Subtotal</span>
                    <strong class="total-valor"><?= number_format($item['preco'] * $item['quantidade'], 2, ',', '.') ?> €</strong>
                </div>
                
                <button class="item-remover btn-remover-ajax" data-id="<?= $id_obra ?>">
                    <i class="fa-regular fa-trash-can"></i>
                </button>
            </div>
            <?php endforeach; ?>
        </div>

        <?php
        $totalGeral = array_sum(array_map(function($item) {
            return $item['preco'] * $item['quantidade'];
        }, $carrinho));
        ?>

<div class="carrinho-resumo">
    <h3>Resumo do Pedido</h3>
    
    <!-- LINHA COM TOTAL DE ITENS -->
    <div class="resumo-linha total-itens">
        <span>Itens no carrinho: </span>
        <span><?= array_sum(array_column($carrinho, 'quantidade')) ?> <?= array_sum(array_column($carrinho, 'quantidade')) > 1 ? 'itens' : 'item' ?></span>
    </div>
    
    <div class="resumo-linha">
        <span>Subtotal: </span>
        <span><?= number_format($totalGeral, 2, ',', '.') ?> €</span>
    </div>
    
    <div class="resumo-total">
        <span>Total:</span>
        <strong><?= number_format($totalGeral, 2, ',', '.') ?> €</strong>
    </div>

    <a href="checkout.php" class="btn-finalizar">
        Finalizar Compra
        <i class="fa-solid fa-arrow-right"></i>
    </a>

    <a href="loja.php" class="btn-continuar">
        <i class="fa-solid fa-arrow-left"></i> Continuar Comprando
    </a>
</div>
    </div>

    <?php endif; ?>

</main>

<?php include 'includes/footer.php'; ?>