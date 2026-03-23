<?php
$titulo = "Portfólio";
include 'includes/header.php';
require_once 'includes/db.php';


// Buscar todas as obras
$sql = "SELECT id, titulo, imagem FROM obra ORDER BY id ASC";
$stmt = $pdo->query($sql);
$obras = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<min>
<!-- Hero Section -->
<section class="hero">
    <h1 data-i18n="portfolio_title">Portfólio</h1>
    <h4 data-i18n="portfolio_quote">
        "Cada obra é um fragmento de identidade, memória e tempo."
    </h4>
    <a class="btn" href="loja.php" data-i18n="go_store">Ir à loja</a>
</section>

<!-- Grid de Obras -->
<section class="grid">

<?php foreach ($obras as $index => $obra): ?>

    <?php
    // Verificar se é favorito
    $isFavorito = false;

    if (isset($_SESSION['id'])) {
        $stmtFav = $pdo->prepare("SELECT id FROM favorito WHERE user_id = ? AND obra_id = ?");
        $stmtFav->execute([$_SESSION['id'], $obra['id']]);
        $isFavorito = (bool)$stmtFav->fetch();
    }
    ?>

    <div class="card" data-id="<?= $obra['id'] ?>" data-index="<?= $index ?>">

        <a href="obra.php?id=<?= $obra['id'] ?>" class="card-link">
            <div class="card-img-wrapper">
                <img 
                src="assets/img/obras/<?= htmlspecialchars($obra['imagem']) ?>"
                alt="<?= htmlspecialchars($obra['titulo']) ?>"sss>

            </div>
            <h3><?= $obra['titulo'] ?></h3>
        </a>

        <!-- Botão de Zoom (Canto Superior Direito) -->
        <button class="portfolio-zoom-btn" data-index="<?= $index ?>">
            <i class="fa-solid fa-magnifying-glass-plus"></i> Ampliar
        </button>
        <br>
        <!-- Botão de Favorito (Canto Inferior Direito) -->
        <?php if (isset($_SESSION['id'])): ?>
            <button 
            type="button"
            class="btn-fav toggle-fav <?= $isFavorito ? 'fav-ativo' : '' ?>" 
            data-obra="<?= $obra['id'] ?>">
            <?= $isFavorito ? "❤️" : "🤍" ?>
            </button>

        <?php endif; ?>

    </div>

<?php endforeach; ?>

</section>
        </main>
<!-- Lightbox -->
<div class="portfolio-lightbox" id="portfolioLightbox">
    <div class="lightbox-overlay" id="lightboxOverlay"></div>
    
    <div class="lightbox-content">
        <button class="lightbox-close" id="lightboxClose">
            <i class="fa-solid fa-times"></i>
        </button>
        <button class="lightbox-nav lightbox-prev" id="lightboxPrev">
            <i class="fa-solid fa-chevron-left"></i>
        </button>

        <div class="lightbox-image-container">
            <img id="lightboxImage" alt="">
            <div class="lightbox-title" id="lightboxTitle"></div>
            <div class="lightbox-counter" id="lightboxCounter"></div>
        </div>

        <button class="lightbox-nav lightbox-next" id="lightboxNext">
            <i class="fa-solid fa-chevron-right"></i>
        </button>
    </div>
</div>

<?php 
echo '<script>';
echo 'const portfolioObras = ' . json_encode($obras) . ';';
echo '</script>';
?>

<?php include 'includes/footer.php'; ?>
