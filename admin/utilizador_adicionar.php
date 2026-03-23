<?php
require 'includes/init_admin.php';
require_once '../includes/db.php';
$titulo = "Adicionar Admin";
include 'includes/admin_header.php';
?>
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

<main class="admin-content">
<div class="content-header">
        <h1><?= $titulo ?></h1>
        <button class="theme-content-btn" id="theme-toggle">
            <i class="fa-solid fa-sun" id="theme-icon"></i>
            <span id="theme-text">Modo Claro</span>
        </button>
    </div>
<div style="max-width: 500px; margin: 0 auto;">
    <form class="auth-card" method="POST" action="utilizador_adicionar_process.php">

    

        <label for="nome">Nome</label>
        <input type="text" name="nome" id="nome" placeholder="Digite o nome" required class="auth-input"
               value="<?= htmlspecialchars($_SESSION['form_data']['nome'] ?? '') ?>">

        <label for="email">Email</label>
        <input type="email" name="email" id="email" placeholder="Digite o email" required class="auth-input"
               value="<?= htmlspecialchars($_SESSION['form_data']['email'] ?? '') ?>">

        <label for="telefone">Telefone (opcional)</label>
<input type="tel" 
       name="telefone" 
       id="telefone_registo" 
       value="<?= htmlspecialchars($_SESSION['form_data']['telefone'] ?? '') ?>"
       placeholder="Digite o telefone"
       class="telefone-input">

        <button class="btn-editar" type="submit" style="width: 100%; margin-top: 20px;">Criar Admin</button>

        <p style="text-align: center; margin-top: 15px;">
            <a href="utilizadores.php" style="color: #041C34;">← Voltar para lista de utilizadores</a>
        </p>
    </form>
</div>
</main>