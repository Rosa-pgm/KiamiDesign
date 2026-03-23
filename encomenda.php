<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Mensagens da sessão
$erro = $_SESSION['encomenda_erro'] ?? '';
$sucesso = $_SESSION['encomenda_sucesso'] ?? '';

// Limpar sessão imediatamente para não acumular
unset($_SESSION['encomenda_erro'], $_SESSION['encomenda_sucesso']);

if (!isset($_SESSION['id'])) {
    $_SESSION['auth_msg'] = "Para pedir uma encomenda personalizada, precisa de estar registado ou fazer login.";
    $_SESSION['login_redirect'] = "encomenda.php";
    header("Location: login.php");
    exit;
}

$titulo = "Encomenda Personalizada";
include 'includes/header.php';
?>
<main>
<section class="hero">
    <h1>Encomenda Personalizada</h1>
    <h4>“Na arte, a magia acontece por cores e reflexão.”</h4>
</section>

<div class="auth-wrapper encomenda-wrapper">
    <form class="encomenda-card" method="POST" action="encomenda_process.php" enctype="multipart/form-data">
        <h2>Faça o Seu Pedido</h2>

        <!-- Mensagens -->
        <?php if ($erro): ?>
            <div class="auth-error"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>
        
        <?php if ($sucesso): ?>
            <div class="auth-success"><?= htmlspecialchars($sucesso) ?></div>
        <?php endif; ?>

<textarea 
    name="descricao" 
    id="descricao"
    placeholder="Descreva a obra..." 
    required
    minlength="20"
    maxlength="1000"></textarea>

<div id="contador" class="contador-caracteres"></div>
<div id="erro-descricao" class="erro-mensagem"></div>
<br>
        <label>Imagem de referência (opcional)</label>
        <input type="file" name="imagem" accept="image/*">

        <button class="btn">Enviar Pedido</button>

        
    </form>
</div>

        </main>
<?php include 'includes/footer.php'; ?>