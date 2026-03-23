<?php 
// Verifica se o admin precisa alterar senha
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT alterar_password FROM utilizador WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $user['alterar_password'] == 1) {
        header("Location: alterar_password.php");
        exit;
    }
}
require 'includes/init_admin.php'; 

$bloquear_menu = isset($_SESSION['alterar_password']);
// Página atual
$current = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo ?? 'Kiami Design' ?></title>

    <link rel="icon" type="image/png" sizes="16x16" href="../assets/img/favicons/favicon-16x16.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/img/favicons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="192x192" href="../assets/img/favicons/android-chrome-192x192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="../assets/img/favicons/android-chrome-512x512.png">
    <link rel="apple-touch-icon" href="../assets/img/favicons/apple-touch-icon.png">
    <link rel="manifest" href="../assets/img/favicons/site.webmanifest">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="includes/dashboard1.css">

    <!-- Primeiro carrega a biblioteca -->
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@18.1.1/build/js/intlTelInput.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@18.1.1/build/css/intlTelInput.css">

<!-- Depois carrega seu script -->
<script src="includes/dashboard.js" defer></script>
</head>

<body>

<header>
    <div class="logo">
    <img src="../assets/img/logo_kiami.png" alt="Kiami Logo">
    </div>
<?php if ($bloquear_menu): ?>
<div class="alerta-password">
   <i class="fa-solid fa-bell" style="color: #f39c12; margin-left: 8px;"></i> Deve alterar a sua password antes de continuar a usar o painel.
</div>
<?php endif; ?>

<nav class="<?= $bloquear_menu ? 'menu-bloqueado' : 'admin-menu' ?>">

    <!-- LINKS SIMPLES -->

    <a href="dashboard.php" class="menu-link <?= $current === 'dashboard.php' ? 'ativo' : '' ?>">
        <i class="fa fa-home"></i> Dashboard
    </a>

    <a href="../index.php" class="menu-link" target="_blank">
        <i class="fa fa-globe"></i> Ver Site
    </a>

    <a href="perfil_admin.php" class="menu-link <?= $current === 'perfil_admin.php' ? 'ativo' : '' ?>">
        <i class="fa fa-user-gear"></i> Meu Perfil
    </a>

    <!-- ===================== OBRAS ===================== -->
    <?php
    $obras_active = in_array($current, [
        'obras.php',
        'reservas_admin.php',
        'add_obras.php',
        'obras_destaque.php',
        'obras_removidas.php'
    ]);
    ?>
    <button class="dropdown-btn <?= $obras_active ? 'ativo' : '' ?>">
        <i class="fa fa-image"></i> Obras
        <i class="fa fa-chevron-down"></i>
    </button>

    <div class="dropdown-container <?= $obras_active ? 'show' : '' ?>">
        <a href="obras.php" class="<?= $current === 'obras.php' ? 'ativo' : '' ?>">Todas as obras</a>
        <a href="reservas_admin.php" class="<?= $current === 'reservas_admin.php' ? 'ativo' : '' ?>">Obras reservadas</a>
        <a href="add_obras.php" class="<?= $current === 'add_obras.php' ? 'ativo' : '' ?>">Adicionar obra</a>
        <a href="obras_removidas.php" class="<?= $current === 'obras_removidas.php' ? 'ativo' : '' ?>">Obras removidas</a>
    </div>


    <!-- ===================== LOJA ===================== -->
    <a href="loja_dashboard.php" class="menu-link <?= $current === 'loja_dashboard.php' ? 'ativo' : '' ?>">
        <i class="fa fa-shop"></i> Loja
    </a>


    <!-- ===================== ENCOMENDAS ===================== -->
    <?php
    $enc_active = in_array($current, [
        'encomendas.php',
        'encomendas_personalizadas.php'
    ]);
    ?>
    <button class="dropdown-btn <?= $enc_active ? 'ativo' : '' ?>">
        <i class="fa fa-box"></i> Encomendas
        <i class="fa fa-chevron-down"></i>
    </button>

    <div class="dropdown-container <?= $enc_active ? 'show' : '' ?>">
        <a href="encomendas.php" class="<?= $current === 'encomendas.php' ? 'ativo' : '' ?>">Encomendas</a>
        <a href="encomendas_personalizadas.php" class="<?= $current === 'encomendas_personalizadas.php' ? 'ativo' : '' ?>">Encomendas Personalizadas</a>
    </div>


    <!-- ===================== PAGAMENTOS ===================== -->
    <?php
    $pag_active = in_array($current, [
        'pagamentos.php',
        'add_pagamentos.php'
    ]);
    ?>
    <button class="dropdown-btn <?= $pag_active ? 'ativo' : '' ?>">
        <i class="fa fa-credit-card"></i> Pagamentos
        <i class="fa fa-chevron-down"></i>
    </button>

    <div class="dropdown-container <?= $pag_active ? 'show' : '' ?>">
        <a href="pagamentos.php" class="<?= $current === 'pagamentos.php' ? 'ativo' : '' ?>">Todos os Pagamentos</a>
        <a href="adicionar_pagamento.php" class="<?= $current === 'adicionar_pagamento.php' ? 'ativo' : '' ?>">Adicionar Pagamento</a>
    </div>


    <!-- ===================== ENVIOS ===================== -->
    <?php
    $env_active = in_array($current, [
        'envios.php',
        'envio_add.php'
    ]);
    ?>
    <button class="dropdown-btn <?= $env_active ? 'ativo' : '' ?>">
        <i class="fa fa-truck"></i> Envios
        <i class="fa fa-chevron-down"></i>
    </button>

    <div class="dropdown-container <?= $env_active ? 'show' : '' ?>">
        <a href="envios.php" class="<?= $current === 'envios.php' ? 'ativo' : '' ?>">Lista de Envios</a>
        <a href="envio_add.php" class="<?= $current === 'envio_add.php' ? 'ativo' : '' ?>">Criar Envio</a>
    </div>


    <!-- ===================== CONTEÚDOS ===================== -->
    <?php
    $cont_active = in_array($current, [
        'sobre_mim.php',
        'posts_admin.php',
        'criar_post.php'
    ]);
    ?>
    <button class="dropdown-btn <?= $cont_active ? 'ativo' : '' ?>">
        <i class="fa fa-pen"></i> Conteúdos
        <i class="fa fa-chevron-down"></i>
    </button>

    <div class="dropdown-container <?= $cont_active ? 'show' : '' ?>">
        <a href="sobre_mim.php" class="<?= $current === 'sobre_mim.php' ? 'ativo' : '' ?>">Sobre mim</a>
        <a href="posts_admin.php" class="<?= $current === 'posts_admin.php' ? 'ativo' : '' ?>">Posts</a>
        <a href="criar_post.php" class="<?= $current === 'criar_post.php' ? 'ativo' : '' ?>">Criar Post</a>
    </div>


    <!-- ===================== CONTACTOS ===================== -->
    <a href="mensagens.php" class="menu-link <?= $current === 'mensagens.php' ? 'ativo' : '' ?>">
        <i class="fa fa-envelope"></i> Mensagens
    </a>


    <!-- ===================== UTILIZADORES ===================== -->
    <a href="utilizadores.php" class="menu-link <?= $current === 'utilizadores.php' ? 'ativo' : '' ?>">
        <i class="fa fa-users"></i> Utilizadores
    </a>
  

    <!-- LOGOUT -->
    <button class="perfil-btn logout-btn" data-modal="modal-logout">
        <i class="fa-solid fa-arrow-right-from-bracket"></i> Sair
    </button>

    <div class="modal" id="modal-logout">
        <div class="modal-content">
            <h3>Tem a certeza que deseja terminar sessão?</h3>

            <button class="modal-save" onclick="window.location.href='logout.php'">
                Sim, sair
            </button>

            <button class="modal-close">Cancelar</button>
        </div>
    </div>

</nav>

</header>
 <!-- Botão menu mobile -->
<button class="menu-toggle" id="menu-toggle">
    <i class="fa fa-bars"></i>
</button>

<!-- Overlay para mobile -->
<div class="menu-overlay" id="menu-overlay"></div>

<!-- Container para mensagens AJAX - OBRIGATÓRIO PARA TODAS AS PÁGINAS -->
    <div id="ajax-mensagem-container"></div>
</body>

</html>
