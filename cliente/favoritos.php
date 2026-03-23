<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit;
}

require_once '../includes/db.php';

$user_id = $_SESSION['id'];

$sql = "
    SELECT o.*, eo.nome AS estado_obra
    FROM favorito f
    JOIN obra o ON f.obra_id = o.id
    JOIN estado_obra eo ON eo.id = o.estado_id
    WHERE f.user_id = ?
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$obras = $stmt->fetchAll();

$titulo = "Obras Favoritas";
include 'includes/header_cliente.php';
?>

<div class="cliente-content" >

    <section class="hero">
        <h1>As minhas obras favoritas</h1>
    </section>

    <section class="grid">

    <?php if (!$obras): ?>
        <p>Não tem obras favoritas ainda.</p>
    <?php else: ?>

        <?php foreach ($obras as $index => $obra): ?>

            <div class="card" data-id="<?= $obra['id'] ?>" data-index="<?= $index ?>">

                <a href="../obra.php?id=<?= $obra['id'] ?>" class="card-link">
                <div class="card-img-wrapper">
                <img 
                src="../assets/img/obras/<?= htmlspecialchars($obra['imagem']) ?>"
                alt="<?= htmlspecialchars($obra['titulo']) ?>">
                
                </div>
                <h3><?= htmlspecialchars($obra['titulo']) ?></h3>
                </a>
    
                <button class="portfolio-zoom-btn" data-index="<?= $index ?>">
                <i class="fa-solid fa-magnifying-glass-plus"></i> Ampliar
                </button>
                <!-- FAVORITO -->
                                     <button 
    class="btn-fav fav-ativo toggle-fav" 
    data-obra="<?= $obra['id'] ?>">
    ❤️
</button>
                
               <?php if ($obra['estado_obra'] === 'Disponível'): ?>
<p class="card-preco">
    <strong>Preço:</strong> <?= number_format($obra['preco'], 2, ',', '.') ?> €
</p>
<?php else: ?>
<p class="card-estado">
    <strong>Estado:</strong> <?= htmlspecialchars($obra['estado_obra']) ?>
</p>
<?php endif; ?>


            </div>


        <?php endforeach; ?>

    <?php endif; ?>

    </section>

</div>

<!-- Lightbox igual ao portfólio -->
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

<script>
const portfolioObras = <?= json_encode($obras) ?>;
</script>

<?php include 'includes/footer_cliente.php'; ?>
