<?php
$titulo = "Obras Removidas";
require 'includes/init_admin.php';
include 'includes/admin_header.php';

$sql = "
    SELECT 
        o.id,
        o.titulo,
        o.imagem,
        e.nome AS estado
    FROM obra o
    JOIN estado_obra e ON o.estado_id = e.id
    WHERE o.estado_id = 4
    ORDER BY o.id ASC
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

    <!-- Container para mensagens AJAX -->
    <div id="ajax-mensagem-container"></div>

    <div class="dashboard-cards" id="obras-removidas-container">

        <?php while ($obra = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
            <div class="card" id="obra-removida-<?= $obra['id'] ?>">

                <img 
                    src="../assets/img/obras/<?= htmlspecialchars($obra['imagem']) ?>" 
                    style="width:100%; border-radius:6px"
                    alt="<?= htmlspecialchars($obra['titulo']) ?>">

                <h3><?= htmlspecialchars($obra['titulo']) ?></h3>

                <p><strong>Estado:</strong> <?= $obra['estado'] ?></p>

                <div style="display:flex; gap:10px; margin-top:10px;">
                    <!-- Botão AJAX em vez de link -->
                    <button 
                        class="btn-reativar-obra" 
                        data-id="<?= $obra['id'] ?>"
                        data-titulo="<?= htmlspecialchars($obra['titulo']) ?>">
                        Reativar
                    </button>
                </div>

            </div>
        <?php endwhile; ?>

    </div>
</main>