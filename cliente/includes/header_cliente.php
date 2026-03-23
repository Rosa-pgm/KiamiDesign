<?php require 'includes/init_cliente.php'; ?>
<?php $current = basename($_SERVER['PHP_SELF']); ?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <!-- SCRIPT DE TEMA - EXECUTA IMEDIATAMENTE (ANTES DE QUALQUER CSS) -->
    <script>
        (function() {
            // Verificar preferência salva
            const savedTheme = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            
            // Aplicar tema imediatamente
            if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
                document.documentElement.classList.add('dark-mode');
                document.body.classList.add('dark-mode');
                document.documentElement.style.backgroundColor = '#1a1a1a';
            } else {
                document.documentElement.style.backgroundColor = '#f5f5f5';
            }
        })();
    </script>
    <title><?= $titulo ?? 'Kiami Design' ?></title>

    <link rel="icon" type="image/png" sizes="16x16" href="../assets/img/favicons/favicon-16x16.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/img/favicons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="192x192" href="../assets/img/favicons/android-chrome-192x192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="../assets/img/favicons/android-chrome-512x512.png">
    <link rel="apple-touch-icon" href="../assets/img/favicons/apple-touch-icon.png">
    <link rel="manifest" href="../assets/img/favicons/site.webmanifest">

    <link rel="stylesheet" href="includes/cliente1.css">
    <!-- Manifest -->
	<link rel="manifest" href="assets/img/favicons/site.webmanifest">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>

<header>
    <div class="logo">
        <img src="../assets/img/logo_kiami.png" alt="Kiami Logo">
    </div>

    <nav>
        <a href="perfil_cliente.php" class="menu-link <?= $current === 'perfil_cliente.php' ? 'ativo' : '' ?>">
            <i class="fa fa-user"></i> Meu Perfil
        </a>

        <a href="../index.php" class="menu-link">
            <i class="fa fa-globe"></i> Ir ao Site
        </a>

        <a href="encomendas.php" class="menu-link <?= $current === 'encomendas.php' ? 'ativo' : '' ?>">
            <i class="fa fa-box"></i> Minhas Encomendas
        </a>

        <a href="obras_reservadas.php" class="menu-link <?= $current === 'obras_reservadas.php' ? 'ativo' : '' ?>">
            <i class="fa fa-calendar-check"></i> Obras Reservadas
        </a>

        <a href="favoritos.php" class="menu-link <?= $current === 'favoritos.php' ? 'ativo' : '' ?>">
            <i class="fa fa-heart"></i> Obras Favoritas
        </a>

        <!-- Botão Modo Claro/Escuro -->
        <button class="menu-link theme-toggle" id="theme-toggle">
            <i class="fa-solid fa-sun" id="theme-icon"></i>
            <span id="theme-text">Modo Claro</span>
        </button>

        <!-- Políticas de Utilização -->
        <a href="politicas.php" class="menu-link">
            <i class="fa-solid fa-file-contract"></i> Políticas de Utilização
        </a>
        
        <button class="menu-link logout-btn" id="btn-logout">
            <i class="fa-solid fa-arrow-right-from-bracket"></i> Sair
        </button>
    </nav>
</header>



<!-- Modal de Logout -->
<div class="modal" id="modal-logout">
    <div class="modal-content">
        <h3>Tem a certeza que deseja terminar sessão?</h3>
        
            <button class="modal-save" onclick="window.location.href='logout_cliente.php'">
                Sim, sair
            </button>

            <button type="button" class="modal-close">Cancelar</button>
    </div>
</div>
<!-- Botão menu mobile -->
<button class="menu-toggle" id="menu-toggle">
    <i class="fa fa-bars"></i>
</button>

<!-- Overlay para mobile -->
<div class="menu-overlay" id="menu-overlay"></div>