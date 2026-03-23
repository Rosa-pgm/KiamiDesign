<?php
session_start();
$titulo = "Coordenar Com o Pintor";
include 'includes/header.php';

if (!isset($_SESSION['temp_coordenar'])) {
    header("Location: checkout.php");
    exit;
}
?>

<main class="coordenar">
    
    <div class="coordenar-container">
        
        <div class="coordenar-titulo linha-decorativa">
            <h1>Coordenar com o Artista</h1>
        </div>

        <form method="POST" action="coordenar_process.php" class="coordenar-form">
            <label>Explique ao artista o que pretende</label>
            <textarea name="mensagem"     id="descricao"
            placeholder="Escreva sua mensagem aqui... dúvidas ou que métodos de pagamentos deseja acertar com o pintor." 
            minlength="20"
            maxlength="1000"
            required></textarea>
            <!-- Contador de caracteres -->
            <div id="contador" class="contador-caracteres"></div>
            <div id="erro-descricao" class="erro-mensagem"></div>      

            <button class="btn" type="submit">Enviar Pedido</button>
            <a href="checkout.php" class="btn-voltar">← Voltar</a>
        </form>
        
    </div>

</main>

<?php include 'includes/footer.php'; ?>