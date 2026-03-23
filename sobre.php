<?php
$titulo = "Sobre Mim";
include 'includes/header.php';
require_once 'includes/db.php';

$stmt = $pdo->query("SELECT * FROM sobre_mim LIMIT 1");
$sobre = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<main class="sobre-container">

    <!-- IMAGEM -->
    <div class="sobre-img-box">
        <?php if (!empty($sobre['imagem'])): ?>
            <img 
                src="assets/img/sobre_artista/<?= htmlspecialchars($sobre['imagem']) ?>" 
                alt="Sobre a artista"
            >
        <?php endif; ?>
    </div>

    <!-- INFO -->
    <div class="sobre-info">

        <h1><?= htmlspecialchars($sobre['nome']) ?></h1>

        <div class="sobre-bio">
            <?= nl2br(htmlspecialchars($sobre['bio'])) ?>
        </div>

        <?php if (!empty($sobre['instagram'])): ?>
            <a 
                href="<?= htmlspecialchars($sobre['instagram']) ?>"
                target="_blank"
                class="sobre-insta"
                aria-label="Instagram">
                <i class="fa-brands fa-instagram"></i>
                <span>Siga no Instagram</span>
            </a>
        <?php endif; ?>

    </div>

</main>


<?php include 'includes/footer.php'; ?>