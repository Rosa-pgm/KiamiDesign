<?php
include 'includes/init.php'; 
?>



<!DOCTYPE html>
<html lang="pt">
<head>
    <!-- Codificação de caracteres -->
    <meta charset="UTF-8">

    <!-- Responsividade -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Título base do site -->
    <title><?= $titulo ?? 'Kiami Design' ?></title>

    <!-- Favicons padrão -->
	<link rel="icon" type="image/png" sizes="16x16" href="assets/img/favicons/favicon-16x16.png">
	<link rel="icon" type="image/png" sizes="32x32" href="assets/img/favicons/favicon-32x32.png">

	<!-- Android / Chrome -->
	<link rel="icon" type="image/png" sizes="192x192" href="assets/img/favicons/android-chrome-192x192.png">
	<link rel="icon" type="image/png" sizes="512x512" href="assets/img/favicons/android-chrome-512x512.png">

	<!-- iOS -->
	<link rel="apple-touch-icon" href="assets/img/favicons/apple-touch-icon.png">

	<!-- Manifest -->
	<link rel="manifest" href="assets/img/favicons/site.webmanifest">


    <!-- Font Awesome (ícones) -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

      <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@18.1.1/build/css/intlTelInput.css"/>
    <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@18.1.1/build/js/intlTelInput.min.js"></script>
    
    <!-- CSS principal -->
    <link rel="stylesheet" href="assets/css/estilo1.css">
</head>

<body>

<!-- HEADER FIXO -->
<header>
    <!-- Logótipo -->
    <div class="logo">
    <img src="assets/img/logo_kiami.png" alt="Kiami Logo">
    </div>

    <!-- Menu de navegação -->
    <nav>
    <a href="index.php">Home</a>
    <a href="portfolio.php">Portfólio</a>
    <a href="loja.php">Loja</a>
    <a href="encomenda.php">Encomenda Personalizada</a>
    <a href="post.php">Posts</a>
    <a href="sobre.php">Sobre Mim</a>
    <a href="mensagem.php">Mensagem</a>


    <!-- No header.php, SUBSTITUA a parte do carrinho: -->

<!-- DEPOIS (aparece sempre, com contador) -->
<a href="carrinho.php" id="menu-carrinho" class="carrinho-icon">
    <i class="fa-solid fa-cart-shopping"></i>
    <span id="cart-count" class="cart-badge"><?= isset($_SESSION['carrinho']) ? count($_SESSION['carrinho']) : 0 ?></span>
</a>





    <?php if (isset($_SESSION['tipo'])): ?>

    <?php if ($_SESSION['tipo'] === 'admin'): ?>
        <a href="admin/dashboard.php">Dashboard</a>

    <?php elseif ($_SESSION['tipo'] === 'cliente'): ?>
        <a href="cliente/perfil_cliente.php">Meu Perfil</a>

    <?php endif; ?>

    <?php else: ?>
    <a href="login.php">Login</a>
    <?php endif; ?>

    <!-- Botão de modo claro/escuro -->
    <button id="theme-toggle" class="icon-btn">
    <i class="fa-solid fa-sun"></i>
    </button>


    </nav>

</header>

