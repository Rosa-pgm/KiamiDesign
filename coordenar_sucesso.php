<?php
session_start();
$titulo = "Coordenação Bem Sucedida";
include 'includes/header.php';
?>

<main class="coordenar-sucesso">
    <div class="sucesso-container">
        <div class="sucesso-card">
            <h1>Pedido Enviado <span style="font-size: 2.5rem;"></span></h1>
            
            <p>
                O seu pedido foi enviado com sucesso.<br>
                O artista irá entrar em contacto consigo em breve para coordenar os detalhes.
            </p>
            
            <a href="cliente/obras_reservadas.php" class="btn">Ver Minhas Reservas</a>
        </div>
    </div>
</main>


<?php include 'includes/footer.php'; ?>