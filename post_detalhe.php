<?php
$titulo = "Detalhes do post";
include 'includes/header.php'; 
require_once 'includes/db.php';

$id = $_GET['id'] ?? null;
if (!$id) exit("Post não encontrado.");

$sql = "
    SELECT 
        p.*,
        u.nome AS autor
    FROM post p
    JOIN utilizador u ON p.user_id = u.id
    WHERE p.id = ?
    AND p.estado = 'publicado'
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$post = $stmt->fetch();

if (!$post) exit("Post não encontrado.");
?>
<main>
<section class="post-detalhe">

    <!-- Botão Voltar no topo -->
    <div class="post-navegacao-topo">
        <a href="javascript:history.back()" class="btn-voltar">
            <i class="fa-solid fa-arrow-left"></i> Voltar
        </a>
    </div>

    <h1><?= $post['titulo'] ?></h1>

    <small>
        <?= date('d/m/Y', strtotime($post['data_criacao'])) ?>
        · por <?= $post['autor'] ?>
    </small>

    <!-- IMAGEM -->
    <?php if ($post['tipo'] === 'imagem' && $post['caminho']): ?>
    <img 
    src="assets/posts/<?= htmlspecialchars($post['caminho']) ?>"
    class="post-img">
    <?php endif; ?>

    <!-- VÍDEO -->
    <?php if ($post['tipo'] === 'video' && $post['caminho']): ?>
        <video controls class="post-video">
            <source src="assets/posts/<?= htmlspecialchars($post['caminho']) ?>" type="video/mp4">
        </video>
    <?php endif; ?>

    <!-- TEXTO -->
    <p><?= nl2br($post['descricao']) ?></p>

    <!-- BOTÕES DE AÇÃO NO FINAL -->
    <div class="post-acoes">
        <h3>Gostou deste conteúdo?</h3>
        <p>Explore mais obras do artista ou visite a loja!</p>
        
        <div class="post-botoes">
            <a href="portfolio.php" class="btn-portfolio">
                <i class="fa-regular fa-image"></i> Ver Portfólio
            </a>
            
            <a href="loja.php" class="btn-loja">
                <i class="fa-solid fa-cart-shopping"></i> Visitar Loja
            </a>
            
            <a href="post.php" class="btn-portfolio">
                <i class="fa-regular fa-newspaper"></i> Mais Posts
            </a>
        </div>
    </div>

</section>
    </main>
<?php include 'includes/footer.php'; ?>