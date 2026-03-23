<?php 
$titulo = "Mensagem";
require_once 'includes/header.php'; 
?>
<main>
<section class="hero">
    <h1>Mensagem</h1>
    <h3>“A arte é a nossa língua, fale connosco!”</h3>
</section>

<div class="auth-wrapper mensagem-wrapper">
    <form class="mensagem-card" method="POST" action="enviar_mensagem.php">
        <h2>Contatar o Artista</h2>

        <!-- Mensagens -->
        <?php if (isset($_SESSION['mensagem_sucesso'])): ?>
            <div class="auth-success mensagem-sucesso">
                <?= $_SESSION['mensagem_sucesso']; unset($_SESSION['mensagem_sucesso']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['mensagem_erro'])): ?>
            <div class="auth-error mensagem-erro">
                <?= $_SESSION['mensagem_erro']; unset($_SESSION['mensagem_erro']); ?>
            </div>
        <?php endif; ?>

        <input type="text" name="nome" placeholder="Seu nome completo" required>
        <input type="email" name="email" placeholder="Seu email" required>
<textarea 
    name="mensagem" 
    id="descricao"
    placeholder="Escreva sua mensagem aqui... dúvidas, elogios, sugestões ou apenas para dizer olá!" 
    required
    minlength="20"
    maxlength="1000"
    class="mensagem-textarea"></textarea>

<!-- Contador de caracteres (opcional) -->
<div id="contador" class="contador-caracteres"></div>
<div id="erro-descricao" class="erro-mensagem"></div>        
<button class="btn">Enviar Mensagem</button>
    </form>
</div>
        </main>

<?php include 'includes/footer.php'; ?>