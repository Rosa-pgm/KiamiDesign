<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

require_once '../includes/db.php';
$titulo = "Todos os Pagamentos";
include 'includes/admin_header.php';


$stmt = $pdo->prepare("
    SELECT 
        p.id AS pagamento_id,
        p.metodo_pagamento,
        p.estado AS estado_pagamento,
        p.valor,
        p.data_pagamento,

        v.id AS venda_id,
        ecp.id AS encomenda_id,

        u.nome AS cliente_nome,
        e.estado AS estado_envio,

        COALESCE(
            (SELECT SUM(vi.preco) FROM venda_item vi WHERE vi.venda_id = v.id),
            0
        ) AS total_venda
    FROM pagamento p
    LEFT JOIN venda v ON p.venda_id = v.id
    LEFT JOIN encomenda_personalizada ecp ON p.encomenda_id = ecp.id
    LEFT JOIN utilizador u ON u.id = COALESCE(v.user_id, ecp.user_id)
    LEFT JOIN envio e ON e.pagamento_id = p.id
    ORDER BY p.data_pagamento ASC
");
$stmt->execute();
$pagamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- ===== BLOCO PARA MENSAGENS PADRONIZADO ===== -->
<?php if (isset($_SESSION['mensagem'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.mostrarMensagem(
                "<?= addslashes($_SESSION['mensagem']['texto']) ?>", 
                "<?= $_SESSION['mensagem']['tipo'] ?>"
            );
        });
    </script>
    <?php unset($_SESSION['mensagem']); ?>
<?php endif; ?>
<!-- ===== FIM DO BLOCO DE MENSAGENS ===== -->

<!-- MANTENHA TAMBÉM AS MENSAGENS ANTIGAS PARA COMPATIBILIDADE -->
<?php if (isset($_GET['sucesso']) && $_GET['sucesso'] === 'reembolso_confirmado'): ?>
    <div class="alert-success">
        Reembolso confirmado com sucesso.
    </div>
<?php endif; ?>

<?php if (isset($_GET['erro']) && $_GET['erro'] === 'reembolso_falhou'): ?>
    <div class="auth-error">
        Ocorreu um erro ao processar o reembolso.
    </div>
<?php endif; ?>

<main class='admin-content'>
    <div class="content-header">
        <h1><?= $titulo ?></h1>
        <button class="theme-content-btn" id="theme-toggle">
            <i class="fa-solid fa-sun" id="theme-icon"></i>
            <span id="theme-text">Modo Claro</span>
        </button>
    </div>
    <a href="adicionar_pagamento.php" class="btn-editar" style="margin-bottom:20px; display:inline-block;">
        + Adicionar Pagamento
    </a>

    <?php if (isset($_GET['sucesso']) && $_GET['sucesso'] === 'reembolso_confirmado'): ?>
    <div class="alert-success">
        Reembolso confirmado com sucesso.
    </div>
<?php endif; ?>

<?php if (isset($_GET['erro']) && $_GET['erro'] === 'reembolso_falhou'): ?>
    <div class="auth-error">
        Ocorreu um erro ao processar o reembolso.
    </div>
<?php endif; ?>


    <table class="tabela-admin">
        <tr>
            <th>ID</th>
            <th>Origem</th>
            <th>Cliente</th>
            <th>Método</th>
            <th>Estado</th>
            <th>Valor</th>
            <th>Data</th>
            <th>Ação</th>
        </tr>

        <?php foreach ($pagamentos as $p): ?>
        <tr>
            <td><?= $p['pagamento_id'] ?></td>

            <td>
                <?php if ($p['venda_id']): ?>
                    Venda da Loja #<?= $p['venda_id'] ?>
                <?php elseif ($p['encomenda_id']): ?>
                    Encomenda Personalizada #<?= $p['encomenda_id'] ?>
                <?php endif; ?>
            </td>

            <td><?= htmlspecialchars($p['cliente_nome']) ?></td>
            <td><?= htmlspecialchars($p['metodo_pagamento']) ?></td>
            <td><?= htmlspecialchars($p['estado_pagamento']) ?></td>
            <td><?= number_format($p['valor'], 2) ?> €</td>
            <td><?= date("d/m/Y H:i", strtotime($p['data_pagamento'])) ?></td>

            <td>
    <?php 
    // 1. ENCOMENDA PERSONALIZADA → sempre pode editar
    if ($p['encomenda_id']): ?>

        <a href="editar_pagamento.php?id=<?= $p['pagamento_id'] ?>" class="btn-editar">
            Editar
        </a>

    <?php 
    // 2. VENDA DA LOJA → pagamento PENDENTE → pode editar
     elseif ($p['venda_id'] && ($p['estado_pagamento'] === 'Pendente' || $p['estado_pagamento'] === 'Falhado')): ?>

        <a href="editar_pagamento.php?id=<?= $p['pagamento_id'] ?>" class="btn-editar">
            Editar
        </a>

    <?php 
    // 3. VENDA DA LOJA → pagamento CONCLUÍDO → pode reembolsar (se não tiver envio)
    elseif ($p['venda_id'] && $p['estado_pagamento'] === 'Concluído' && empty($p['estado_envio'])): ?>

        <button class="btn-editar" type="button"
    onclick="abrirModalReembolso(<?= $p['pagamento_id'] ?>, <?= $p['venda_id'] ?>)">
    Confirmar Reembolso
</button>


    <?php 
    // 4. VENDA DA LOJA → já tem envio → não pode reembolsar
    elseif (!empty($p['estado_envio'])): ?>

        <span style="color:#888;">Envio criado — reembolso indisponível</span>

    <?php 
    // 5. VENDA DA LOJA → já reembolsado
    elseif ($p['estado_pagamento'] === 'Reembolsado'): ?>

        <span style="color:#c00; font-weight:bold;">Reembolsado</span>

    <?php else: ?>

        —

    <?php endif; ?>
</td>

        </tr>
        <?php endforeach; ?>
    </table>
    </main>
<div class="modal" id="modal-reembolso">
    <div class="modal-content">
        <h3>Confirmar Reembolso</h3>

        <p id="texto-reembolso"></p>

        <form method="POST" action="confirmar_reembolso.php">
            <input type="hidden" name="pagamento_id" id="pagamento-id">
            <input type="hidden" name="venda_id" id="venda-id">

            <button type="submit" class="modal-save" style="background:#b91c1c;">
                Confirmar
            </button>

            <button type="button" class="modal-close">Cancelar</button>
        </form>
    </div>
</div>
<script>
function abrirModalReembolso(pagamentoId, vendaId) {
    document.getElementById('pagamento-id').value = pagamentoId;
    document.getElementById('venda-id').value = vendaId;

    document.getElementById('texto-reembolso').textContent =
        "Já reembolsou o pagamento na stripe?";

    document.getElementById('modal-reembolso').style.display = 'flex';
}

// Fechar modal
document.querySelectorAll('.modal-close').forEach(btn => {
    btn.addEventListener('click', () => {
        btn.closest('.modal').style.display = 'none';
    });
});
</script>
