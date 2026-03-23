<?php
$titulo = "Home";
require_once 'includes/db.php';
include 'includes/header.php';

// Buscar obras em destaque
$destaques = $pdo->query("
    SELECT id, titulo, imagem 
    FROM obra 
    WHERE destaque = 1 AND estado_id != 4
    ORDER BY id DESC
")->fetchAll();
?>

<main>
    <!-- HERO SECTION -->
    <section class="hero">
        <h1>Arte Contemporânea • Estilo Único</h1>
        <h4>Portfólio e loja oficial do artista Kiami</h4>
        <a class="btn" href="portfolio.php">Explorar Portfólio</a>
    </section>

<?php if (!empty($destaques)): ?>
<section class="slideshow-section">
    <h2 class="slideshow-title">Obras em Destaque</h2>
    
    <div class="slideshow-container">
        <div class="slideshow-track">
            <?php foreach ($destaques as $index => $obra): ?>
            <div class="slideshow-slide">
                <a href="obra.php?id=<?= $obra['id'] ?>" class="slideshow-link">
                    <div class="slideshow-image-wrapper">
                        <img 
                            src="assets/img/obras/<?= htmlspecialchars($obra['imagem']) ?>" 
                            alt="<?= htmlspecialchars($obra['titulo']) ?>"
                        >
                    </div>
                    <div class="slideshow-caption">
                        <h3><?= htmlspecialchars($obra['titulo']) ?></h3>
                        <span class="slideshow-btn">Ver Obra</span>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>

        <button class="slideshow-nav slideshow-prev" id="slideshowPrev">
            <i class="fa-solid fa-chevron-left"></i>
        </button>
        <button class="slideshow-nav slideshow-next" id="slideshowNext">
            <i class="fa-solid fa-chevron-right"></i>
        </button>
            
        <div class="slideshow-dots">
            <?php foreach ($destaques as $index => $obra): ?>
            <span class="slideshow-dot <?= $index === 0 ? 'active' : '' ?>"></span>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
</main>


<?php include 'includes/footer.php'; ?>