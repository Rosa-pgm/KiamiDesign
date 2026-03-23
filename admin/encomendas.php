<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

require_once '../includes/db.php';

# ============================
# 1. ENCOMENDAS PERSONALIZADAS
# ============================
$sqlPersonalizadas = "
    SELECT 
        e.id,
        e.user_id,
        u.nome AS cliente_nome,
        u.email AS cliente_email,
        e.descricao,
        e.imagem,
        e.estado,
        e.data_pedido,
        'Personalizada' AS tipo
    FROM encomenda_personalizada e
    JOIN utilizador u ON e.user_id = u.id
";

$personalizadas = $pdo->query($sqlPersonalizadas)->fetchAll();

# ============================
# 2. ENCOMENDAS DA LOJA
# ============================
$sqlLoja = "
    SELECT 
        v.id,
        v.user_id,
        u.nome AS cliente_nome,
        u.email AS cliente_email,
        CONCAT('Compra na loja (Venda #', v.id, ')') AS descricao,
        (
            SELECT GROUP_CONCAT(o.imagem)
            FROM venda_item vi
            JOIN obra o ON o.id = vi.obra_id
            WHERE vi.venda_id = v.id
        ) AS imagens,
        v.estado AS estado,  /* ← MUDAR AQUI: de p.estado para v.estado */
        v.data_criacao AS data_pedido,
        'Loja' AS tipo
    FROM venda v
    JOIN utilizador u ON v.user_id = u.id
    LEFT JOIN pagamento p ON p.venda_id = v.id
";


$loja = $pdo->query($sqlLoja)->fetchAll();

# ============================
# 3. JUNTAR TUDO
# ============================
$encomendas = array_merge($personalizadas, $loja);

# Ordenar por data
#usort($encomendas, function($a, $b) {return strtotime($b['data_pedido']) - strtotime($a['data_pedido']);});

$titulo = "Todas as Encomendas";
include 'includes/admin_header.php';
?>

<main class="admin-content">
<div class="content-header">
        <h1><?= $titulo ?></h1>
        <button class="theme-content-btn" id="theme-toggle">
            <i class="fa-solid fa-sun" id="theme-icon"></i>
            <span id="theme-text">Modo Claro</span>
        </button>
    </div>

    <table class="tabela-admin">
        <tr>
            <th>ID</th>
            <th>Tipo</th>
            <th>Cliente</th>
            <th>Descrição</th>
            <th>Imagem</th>
            <th>Estado</th>
            <th>Data e Hora</th>
        </tr>

        <?php foreach ($encomendas as $e): ?>
        <tr>
            <td><?= $e['id'] ?></td>
            <td><?= $e['tipo'] ?></td>

            <td>
                <?= $e['cliente_nome'] ?><br>
                <small><?= $e['cliente_email'] ?></small>
            </td>

            <td class="descricao"><?= nl2br(htmlspecialchars($e['descricao'])) ?></td>

            <td>
    <?php if ($e['tipo'] === 'Personalizada'): ?>

        <?php if (!empty($e['imagem'])): ?>
            <?php $basePath = "../assets/img/encomendas_personalizadas/"; ?>
            <img src="<?= $basePath . htmlspecialchars($e['imagem']) ?>"
                 class="thumb"
                 data-img="<?= $basePath . htmlspecialchars($e['imagem']) ?>">
        <?php else: ?>
            <span style="color:#888;">Sem imagem</span>
        <?php endif; ?>

    <?php else: ?> <!-- Loja -->

        <?php if (!empty($e['imagens'])): ?>
            <?php 
                $imgs = explode(',', $e['imagens']);
                $basePath = "../assets/img/obras/";
            ?>
            <?php foreach ($imgs as $img): ?>
                <img src="<?= $basePath . trim($img) ?>"
                     class="thumb"
                     data-img="<?= $basePath . trim($img) ?>">
            <?php endforeach; ?>
        <?php else: ?>
            —
        <?php endif; ?>

    <?php endif; ?>
</td>


            <td><?= $e['estado'] ?></td>
            <td><?= date("d/m/Y H:i", strtotime($e['data_pedido'])) ?></td>
        </tr>
        <?php endforeach; ?>

    </table>

        </main>
