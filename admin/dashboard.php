<?php 
$titulo = "Dashboard";
include 'includes/admin_header.php'; 

// Total de obras
$stmt = $pdo->query("SELECT COUNT(*) FROM obra");
$totalObras = $stmt->fetchColumn();

// Total de encomendas
$stmt = $pdo->query("SELECT COUNT(*) FROM encomenda_personalizada");
$totalEncomendas = $stmt->fetchColumn();

// Total de vendas
$stmt = $pdo->query("SELECT COUNT(*) FROM venda");
$totalVendas = $stmt->fetchColumn();

// Total de envios
$stmt = $pdo->query("SELECT COUNT(*) FROM envio");
$totalEnvio = $stmt->fetchColumn();

// Total de envios
$stmt = $pdo->query("SELECT COUNT(*) FROM pagamento");
$totalPagamento = $stmt->fetchColumn();

// Total de contactos (mensagens)
$stmt = $pdo->query("SELECT COUNT(*) FROM mensagem");
$totalContactos = $stmt->fetchColumn();

// Total de utilizadores
$stmt = $pdo->query("SELECT COUNT(*) FROM utilizador");
$totalClientes = $stmt->fetchColumn();

// Total de posts
$stmt = $pdo->query("SELECT COUNT(*) FROM post");
$totalPosts = $stmt->fetchColumn();

?>


<main class="admin-content">
    <div class="content-header">
        <h1><?= $titulo ?></h1>
        <button class="theme-content-btn" id="theme-toggle">
            <i class="fa-solid fa-sun" id="theme-icon"></i>
            <span id="theme-text">Modo Claro</span>
        </button>
    </div>
    <p>Bem-vindo ao painel administrativo do Kiami Design.</p>
<?php
if (isset($_SESSION['sucesso'])) {
    echo '<div class="alert-success">' . $_SESSION['sucesso'] . '</div>';
    unset($_SESSION['sucesso']);
}

if (isset($_SESSION['erro'])) {
    echo '<div class="auth-error">' . $_SESSION['erro'] . '</div>';
    unset($_SESSION['erro']);
}
?>
    <section class="dashboard-cards-wrapper">
    <div class="dashboard-cards">
        
        <a href="obras.php" class="card-link">
            <div class="card">
                <i class="fa-solid fa-image fa-2x"></i>
                <h3>Obras</h3>
                <p class="card-number"><?= $totalObras ?></p>
            </div>
        </a>

        <a href="encomendas.php" class="card-link">
            <div class="card">
                <i class="fa-solid fa-box fa-2x"></i>
                <h3>Encomendas Personalizadas</h3>
                <p class="card-number"><?= $totalEncomendas ?></p>
            </div>
        </a>

        <a href="encomendas.php" class="card-link">
            <div class="card">
                <i class="fa fa-shop"></i>
                <h3>Vendas da Loja</h3>
                <p class="card-number"><?= $totalVendas ?></p>
            </div>
        </a>

        <a href="pagamentos.php" class="card-link">
            <div class="card">
                <i class="fa fa-credit-card"></i>
                <h3>Pagamentos</h3>
                <p class="card-number"><?= $totalPagamento ?></p>
            </div>
        </a>

        <a href="envios.php" class="card-link">
            <div class="card">
                <i class="fa fa-truck"></i>
                <h3>Envios</h3>
                <p class="card-number"><?= $totalEnvio ?></p>
            </div>
        </a>

        <a href="mensagens.php" class="card-link">
            <div class="card">
                <i class="fa-solid fa-envelope fa-2x"></i>
                <h3>Mensagens</h3>
                <p class="card-number"><?= $totalContactos ?></p>
            </div>
        </a>

        <a href="utilizadores.php" class="card-link">
            <div class="card">
                <i class="fa-solid fa-users fa-2x"></i>
                <h3>Utilizadores</h3>
                <p class="card-number"><?= $totalClientes ?></p>
            </div>
        </a>

        <a href="posts_admin.php" class="card-link">
            <div class="card">
                <i class="fa-solid fa-pen-to-square fa-2x"></i>
                <h3>Posts</h3>
                <p class="card-number"><?= $totalPosts ?></p>
            </div>
        </a>

    </div>
</section>



</main>


</div> <!-- fecha admin-layout -->

