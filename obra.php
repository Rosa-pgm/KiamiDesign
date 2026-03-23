<?php
$titulo = "Detalhes da obra";
include 'includes/header.php'; 
require_once 'includes/db.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    echo "<p style='text-align:center'>Obra não encontrada.</p>";
    include 'includes/footer.php';
    exit;
}

$sql = "
SELECT 
    o.id, 
    o.titulo, o.descricao, o.preco, o.dimensao, o.imagem,
    DATE_FORMAT(o.data_criacao, '%Y') AS ano,
    m.nome AS material,
    e.nome AS estado
FROM obra o
JOIN material m ON o.material_id = m.id
JOIN estado_obra e ON o.estado_id = e.id
WHERE o.id = ?
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$obra = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$obra) {
    echo "<p style='text-align:center'>Obra não encontrada.</p>";
    include 'includes/footer.php';
    exit;
}

$isFavorito = false;
if (isset($_SESSION['id'])) {
    $stmtFav = $pdo->prepare("SELECT id FROM favorito WHERE user_id = ? AND obra_id = ?");
    $stmtFav->execute([$_SESSION['id'], $id]);
    $isFavorito = (bool)$stmtFav->fetch();
}
?>

<main>
    <!-- Botão de Voltar (FORA do grid, mas centralizado) -->
    <div style="max-width: 1200px; margin: 1rem auto 0; padding: 0 2rem;">
        <a href="javascript:history.back()" class="btn-voltar">
            <i class="fa-solid fa-arrow-left"></i> Voltar
        </a>
    </div>

    <div class="obra-container">
        <!-- IMAGEM -->
        <div class="obra-img-box">
            <!-- Botão de Zoom -->
            <button class="portfolio-zoom-btn" data-index="0">
                <i class="fa-solid fa-magnifying-glass-plus"></i> Ampliar
            </button>

            <img 
                src="assets/img/obras/<?= htmlspecialchars($obra['imagem']) ?>" 
                alt="<?= htmlspecialchars($obra['titulo']) ?>" 
                id="obraImagem"
            >
            
            <!-- FAVORITOS -->
            <?php if (isset($_SESSION['id'])): ?>
                <button class="btn-fav toggle-fav <?= $isFavorito ? 'fav-ativo' : '' ?>"
        data-obra="<?= $obra['id'] ?>">
    <?= $isFavorito ? '❤️' : '🤍' ?>
</button>
            <?php endif; ?>
        </div>

        <!-- INFO -->
        <div class="obra-info">
            <h1><?= htmlspecialchars($obra['titulo']) ?></h1>

            <ul class="obra-meta">
                <li><strong>Técnica:</strong> <?= $obra['material'] ?: '—' ?></li>
                <li><strong>Dimensão:</strong> 
                    <?= $obra['dimensao'] ? nl2br($obra['dimensao']) : 'Sem dimensão disponível.' ?>
                </li>
                <li><strong>Ano:</strong> <?= $obra['ano'] ?: '—' ?></li>
                <li><strong>Estado:</strong> 
                    <span class="<?= $obra['estado'] !== 'Disponível' ? 'estado-alerta' : '' ?>">
                        <?= $obra['estado'] ?>
                    </span>
                </li>
                <li><strong>Descrição:</strong> 
                    <?= $obra['descricao'] ? nl2br($obra['descricao']) : 'Sem descrição disponível.' ?>
                </li>
            </ul>

            <?php if ($obra['preco'] !== null): ?>
                <p class="obra-preco">€<?= number_format($obra['preco'], 2, ',', '.') ?></p>
            <?php endif; ?>

            <!-- BOTÃO CARRINHO -->
            <?php if ($obra['estado'] === 'Disponível'): ?>
                <?php $jaNoCarrinho = isset($_SESSION['carrinho'][$id]); ?>
            
                <button 
                    class="btn btn-carrinho add-carrinho" 
                    data-obra="<?= $id ?>" 
                    <?= $jaNoCarrinho ? 'disabled style="opacity: .5;"' : '' ?>
                >
                    <i class="fa <?= $jaNoCarrinho ? 'fa-check' : 'fa-cart-plus' ?>"></i>
                    <?= $jaNoCarrinho ? ' No carrinho' : 'Adicionar ao carrinho' ?>
                </button>
            <?php endif; ?>
        </div>
    </div>
</main>

<!-- LIGHTBOX -->
<div class="portfolio-lightbox" id="portfolioLightbox">
    <div class="lightbox-overlay" id="lightboxOverlay"></div>
    <div class="lightbox-content">
        <button class="lightbox-close" id="lightboxClose">
            <i class="fa-solid fa-times"></i>
        </button>
        <div class="lightbox-image-container">
            <img id="lightboxImage" alt="">
            <div class="lightbox-title" id="lightboxTitle"></div>
            <div id="lightboxCounter"></div>
        </div>
    </div>
</div>

<script>
const portfolioObras = [
    {
        titulo: "<?= htmlspecialchars($obra['titulo']) ?>",
        imagem: "<?= htmlspecialchars($obra['imagem']) ?>"
    }
];
</script>

<?php include 'includes/footer.php'; ?>