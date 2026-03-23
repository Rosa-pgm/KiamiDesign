<?php
require 'includes/init_admin.php';
$titulo = "Editar Sobre Mim";
include 'includes/admin_header.php';


// Buscar dados atuais
$stmt = $pdo->query("SELECT * FROM sobre_mim LIMIT 1");
$sobre = $stmt->fetch(PDO::FETCH_ASSOC);

// Se não existir registo, cria um vazio
if (!$sobre) {
    $pdo->query("INSERT INTO sobre_mim (nome, bio) VALUES ('', '')");
    $stmt = $pdo->query("SELECT * FROM sobre_mim LIMIT 1");
    $sobre = $stmt->fetch(PDO::FETCH_ASSOC);
}
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
   
    <div class="form-card">

        <form action="sobremim_update.php" method="POST" enctype="multipart/form-data" class="form-admin">

            <input type="hidden" name="id" value="<?= $sobre['id'] ?>">

            <label>Imagem atual</label><br>
            <?php if (!empty($sobre['imagem'])): ?>
                <img 
                    src="../assets/img/sobre_artista/<?= htmlspecialchars($sobre['imagem']) ?>" 
                    width="150"
                >
            <?php else: ?>
                <p><i>Sem imagem</i></p>
            <?php endif; ?>

            <label>Alterar imagem</label>
            <input type="file" name="imagem">

            <label>Nome</label>
            <input type="text" name="nome" value="<?= htmlspecialchars($sobre['nome']) ?>" required>

            <label>Biografia</label>
            <textarea name="bio" rows="6" required><?= htmlspecialchars($sobre['bio']) ?></textarea>

            <label>Instagram (link)</label>
            <input type="text" name="instagram" value="<?= htmlspecialchars($sobre['instagram']) ?>">

            <button type="submit" class="btn-save">Guardar Alterações</button>
        </form>

    </div>
</main>
