<?php
$titulo = "Loja";
include 'includes/header.php';
require_once 'includes/db.php';

// Buscar obras disponíveis
$sql = "
    SELECT o.id, o.titulo, o.imagem, o.preco
    FROM obra o
    JOIN estado_obra e ON o.estado_id = e.id
    WHERE o.preco IS NOT NULL
    AND e.nome = 'Disponível'
";
$stmt = $pdo->query($sql);
?>

<main class="loja">
<section class="hero">
    <h1>Loja</h1>
    <h4>“A arte é vida. Sinta-se vivo connosco.”</h4>
    <a class="btn" href="carrinho.php">Ir ao Carrinho </a>

</section>

<div class="grid">
<?php while ($obra = $stmt->fetch()): ?>

    <?php
    $isFavorito = false;
    if (isset($_SESSION['id'])) {
        $stmtFav = $pdo->prepare("SELECT id FROM favorito WHERE user_id = ? AND obra_id = ?");
        $stmtFav->execute([$_SESSION['id'], $obra['id']]);
        $isFavorito = (bool)$stmtFav->fetch();
    }
    ?>
    

    <div class="card">
        <div class="card-img-wrapper">
            <img src="assets/img/obras/<?= htmlspecialchars($obra['imagem']) ?>" alt="<?= htmlspecialchars($obra['titulo']) ?>">
        </div>

        <h3><?= $obra['titulo'] ?></h3>
        <p><?= number_format($obra['preco'], 2, ',', '.') ?> €</p>

        <a href="obra.php?id=<?= $obra['id'] ?>" class="btn">Detalhes</a>


        <!-- Dentro do seu while ($obra = $stmt->fetch()) -->
<?php 
    // Verifica se esta obra específica já está no carrinho
    $jaNoCarrinho = isset($_SESSION['carrinho'][$obra['id']]); 
?>

<button 
    class="btn btn-carrinho add-carrinho" 
    data-obra="<?= $obra['id'] ?>"
    <?= $jaNoCarrinho ? 'disabled style="opacity: 0.5;"' : '' ?>
>
    <i class="fa <?= $jaNoCarrinho ? 'fa-check' : 'fa-cart-plus' ?>"></i>
    <?= $jaNoCarrinho ? ' No carrinho' : '' ?>
</button>


        <?php if (isset($_SESSION['id'])): ?>
            <button class="btn-fav toggle-fav <?= $isFavorito ? 'fav-ativo' : '' ?>"
            data-obra="<?= $obra['id'] ?>">
        <?= $isFavorito ? '❤️' : '🤍' ?>
        </button>


        <?php endif; ?>
    </div>

<?php endwhile; ?>
</div>
</main>

<?php include 'includes/footer.php'; ?>
