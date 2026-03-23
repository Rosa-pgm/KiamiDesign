<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

require_once '../includes/db.php';

$sql = "
    SELECT p.*, u.nome AS autor
    FROM post p
    JOIN utilizador u ON p.user_id = u.id
    ORDER BY p.data_criacao ASC
";
$stmt = $pdo->query($sql);
$posts = $stmt->fetchAll();

$titulo = "Posts";
include 'includes/admin_header.php';
?>

<!-- ===== BLOCO PARA MENSAGENS PADRONIZADO ===== -->
<?php if (isset($_SESSION['mensagem'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.mostrarMensagem(
                "<?= addslashes($_SESSION['mensagem']['texto']) ?>", 
                "<?= $_SESSION['mensagem']['tipo'] ?>"
            );
        });
    </script>
    <?php unset($_SESSION['mensagem']); ?>
<?php endif; ?>
<!-- ===== FIM DO BLOCO DE MENSAGENS ===== -->

<main class="admin-content">
<div class="content-header">
        <h1><?= $titulo ?></h1>
        <button class="theme-content-btn" id="theme-toggle">
            <i class="fa-solid fa-sun" id="theme-icon"></i>
            <span id="theme-text">Modo Claro</span>
        </button>
    </div>

    <a href="criar_post.php" class="btn-editar" style="margin-bottom:20px; display:inline-block;"> 
        + Criar novo post
    </a>

    <table class="tabela-admin">
        <tr>
            <th>ID</th>
            <th>Título</th>
            <th>Tipo</th>
            <th>Ficheiro</th>
            <th>Estado</th>
            <th>Autor</th>
            <th>Data</th>
            <th>Ações</th>
        </tr>

        <?php foreach ($posts as $p): ?>
        <tr>
            <td><?= $p['id'] ?></td>
            <td><?= htmlspecialchars($p['titulo']) ?></td>
            <td><?= $p['tipo'] ?></td>
            <td>
                <?php if ($p['tipo'] === 'imagem'): ?>
                    <img src="../assets/posts/<?= htmlspecialchars($p['caminho']) ?>" class="thumb">
                <?php elseif ($p['tipo'] === 'video'): ?>
                    <video class="thumb" muted>
                    <source src="../assets/posts/<?= htmlspecialchars($p['caminho']) ?>" type="video/mp4">
                    </video>
                <?php else: ?>
                    <span>Texto</span>
                <?php endif; ?>
            </td>

            <td><?= $p['estado'] ?></td>
            <td><?= $p['autor'] ?></td>
            <td><?= date('d/m/Y H:i', strtotime($p['data_criacao'])) ?></td>

          <td>
    <a href="post_editar.php?id=<?= $p['id'] ?>" class="btn-editar">Editar</a>

    <?php if ($p['estado'] === 'publicado'): ?>
        <a href="#" 
           class="btn-editar btn-danger"
           onclick="abrirModalRemoverPost(<?= $p['id'] ?>, '<?= htmlspecialchars(addslashes($p['titulo'])) ?>'); return false;">
            Remover
        </a>
    <?php elseif ($p['estado'] === 'removido'): ?>
        <a href="#" 
           class="btn-editar btn-success"
           onclick="abrirModalPublicarPost(<?= $p['id'] ?>, '<?= htmlspecialchars(addslashes($p['titulo'])) ?>'); return false;">
            Publicar
        </a>
    <?php endif; ?>
</td>
        </tr>
        <?php endforeach; ?>
    </table>

    </main>
<!-- Modal de Confirmação para Remover Post -->
<div class="modal" id="modal-remover-post">
    <div class="modal-content">
        <h3>Remover Post</h3>
        <p style="margin: 20px 0; color: var(--text-secondary);" id="modal-post-titulo"></p>
        
        <form method="POST" action="#" id="form-remover-post" onsubmit="return confirmarRemocaoPost(event)">
            <input type="hidden" name="id" id="remover-post-id">
            <button type="submit" class="modal-save" style="background: #b91c1c;">Confirmar Remoção</button>
            <button type="button" class="modal-close">Cancelar</button>
        </form>
    </div>
</div>

<!-- Modal de Confirmação para Publicar Post -->
<div class="modal" id="modal-publicar-post">
    <div class="modal-content">
        <h3>Publicar Post</h3>
        <p style="margin: 20px 0; color: var(--text-secondary);" id="modal-post-publicar-titulo"></p>
        
        <form method="POST" action="#" id="form-publicar-post" onsubmit="return confirmarPublicacaoPost(event)">
            <input type="hidden" name="id" id="publicar-post-id">
            <button type="submit" class="modal-save" style="background: #28a745;">Confirmar Publicação</button>
            <button type="button" class="modal-close">Cancelar</button>
        </form>
    </div>
</div>