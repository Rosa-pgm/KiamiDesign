<?php
$titulo = "Obras em Destaque";
require 'includes/init_admin.php';
include 'includes/admin_header.php';

$sql = "
    SELECT id, titulo, imagem, destaque
    FROM obra
    WHERE estado_id != 4
    ORDER BY id DESC
";

$stmt = $pdo->query($sql);
?>

<main class="admin-content">
    <div class="content-header">
        <h1><?= $titulo ?></h1>
        <button class="theme-content-btn" id="theme-toggle">
            <i class="fa-solid fa-sun" id="theme-icon"></i>
            <span id="theme-text">Modo Claro</span>
        </button>
    </div>
<h1>Gerir Obras em Destaque</h1>

<div class="dashboard-cards">

<?php while ($obra = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
    <div class="card">

    <img 
    src="../assets/img/obras/<?= htmlspecialchars($obra['imagem']) ?>" 
    style="width:100%; border-radius:6px">

        <h3><?= htmlspecialchars($obra['titulo']) ?></h3>

        <div style="margin-top:10px;">
            <?php if ($obra['destaque'] == 1): ?>
                <a href="toggle_destaque_ajax.php?id=<?= $obra['id'] ?>" class="btn-remover">
                    Remover dos Destaques
                </a>
            <?php else: ?>
                <a href="toggle_destaque.php?id=<?= $obra['id'] ?>" class="btn-reativar">
                    Adicionar aos Destaques
                </a>
            <?php endif; ?>
        </div>

    </div>
<?php endwhile; ?>

</div>
</main>
