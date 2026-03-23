<?php
$titulo = "Posts";
include 'includes/header.php';
require_once 'includes/db.php';

/*
    Vamos buscar:
    - apenas posts PUBLICADOS
    - ordenados do mais recente para o mais antigo
*/
$sql = "
    SELECT 
        p.id,
        p.titulo,
        p.descricao,
        p.tipo,
        p.caminho,
        p.data_criacao,
        u.nome AS autor
    FROM post p
    JOIN utilizador u ON p.user_id = u.id
    WHERE p.estado = 'publicado'
    ORDER BY p.data_criacao DESC
";

$stmt = $pdo->query($sql);
?>
<main>
<section class="hero">
    <h1>Posts</h1>
    <h4>“Acompanhe os projetos, exposições e processos criativos do artista.”</h4>
</section>

<section class="posts-container">

<?php while ($post = $stmt->fetch()): ?>
    <article class="post-card">

        <!-- IMAGEM -->
        <?php if ($post['tipo'] === 'imagem' && $post['caminho']): ?>
        <img 
        src="assets/posts/<?= htmlspecialchars($post['caminho']) ?>"
            alt="<?= htmlspecialchars($post['titulo']) ?>">
        <?php endif; ?>

        <!-- VÍDEO -->
        <?php if ($post['tipo'] === 'video' && $post['caminho']): ?>
            <video controls>
            <source src="assets/posts/<?= htmlspecialchars($post['caminho']) ?>" type="video/mp4">
            </video>
        <?php endif; ?>


        <div class="post-content">
            <h3><?= $post['titulo'] ?></h3>

            <small>
                <?= date('d/m/Y', strtotime($post['data_criacao'])) ?>
                · por <?= $post['autor'] ?>
            </small>

            <p><?= nl2br($post['descricao']) ?></p>

            <a href="post_detalhe.php?id=<?= $post['id'] ?>" class="btn">
                Ler mais
            </a>
        </div>

    </article>
<?php endwhile; ?>

</section>
        </main>
<?php include 'includes/footer.php'; ?>
