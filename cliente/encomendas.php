<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['id'];

/* ==============================
   VENDAS DA LOJA
================================ */
$sql_vendas = "
    SELECT 
        v.id AS pedido_id,
        'venda' AS tipo,
        v.data_criacao AS data_encomenda,
        p.id AS pagamento_id,
        p.metodo_pagamento,
        p.estado AS estado_pagamento,
        COALESCE(SUM(vi.preco),0) AS total,
        e.transportadora,
        e.numero_rastreio,
        e.estado,
        e.data_envio,
        e.data_entrega,
        GROUP_CONCAT(o.titulo SEPARATOR ', ') AS titulos,
        GROUP_CONCAT(o.imagem SEPARATOR ', ') AS imagens
    FROM venda v
    JOIN venda_item vi ON vi.venda_id = v.id
    JOIN obra o ON o.id = vi.obra_id
    LEFT JOIN pagamento p ON p.venda_id = v.id
    LEFT JOIN envio e ON e.pagamento_id = v.id
    WHERE v.user_id = ?
    GROUP BY v.id
";

/* ==============================
   ENCOMENDAS PERSONALIZADAS
================================ */
$sql_encomendas = "
    SELECT 
        ep.id AS pedido_id,
        'encomenda' AS tipo,
        ep.data_pedido AS data_encomenda,
        p.id AS pagamento_id,
        p.metodo_pagamento,
        p.estado AS estado_pagamento,
        p.valor AS total,
        e.transportadora,
        e.numero_rastreio,
        e.estado,
        e.data_envio,
        e.data_entrega,
        ep.descricao AS titulos,
        ep.imagem AS imagens
    FROM encomenda_personalizada ep
    LEFT JOIN pagamento p ON p.encomenda_id = ep.id
    LEFT JOIN envio e ON e.pagamento_id = p.id
    WHERE ep.user_id = ?
";

/* ==============================
   Executar e combinar
================================ */
$stmt_vendas = $pdo->prepare($sql_vendas);
$stmt_vendas->execute([$user_id]);
$vendas = $stmt_vendas->fetchAll(PDO::FETCH_ASSOC);

$stmt_encomendas = $pdo->prepare($sql_encomendas);
$stmt_encomendas->execute([$user_id]);
$encomendas_personalizadas = $stmt_encomendas->fetchAll(PDO::FETCH_ASSOC);

$encomendas = array_merge($vendas, $encomendas_personalizadas);

/* Ordenar por data_encomenda desc */
usort($encomendas, function($a, $b) {
    return strtotime($b['data_encomenda']) - strtotime($a['data_encomenda']);
});

$titulo = "Minhas Encomendas";
include 'includes/header_cliente.php';
?>

<div class="cliente-content">
    <h1>As Minhas Encomendas</h1>

    <?php if (!empty($_SESSION['alerta_sucesso'])): ?>
        <div class="alert-success">
            <?= $_SESSION['alerta_sucesso']; ?>
        </div>
        <?php unset($_SESSION['alerta_sucesso']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['alerta_erro'])): ?>
        <div class="alert-error">
            <?= $_SESSION['alerta_erro']; ?>
        </div>
        <?php unset($_SESSION['alerta_erro']); ?>
    <?php endif; ?>

    <?php if (empty($encomendas)): ?>
    <p>Ainda não tem encomendas no histórico.
    </p>
<?php endif; ?>


    <?php foreach ($encomendas as $e): ?>
        <div class="order-card">

            <div class="order-header">
            <h2>
    <?= $e['tipo'] === 'venda' ? 'Compra' : 'Encomenda' ?> 
    #<?= $e['pedido_id'] ?>
</h2>

            <?php $data = new DateTime($e['data_encomenda']); ?>
                <span>
                    <?= $data->format('d/m/Y') ?><br>
                    <?= $data->format('H:i') ?>
                </span>
            </div>

            <div class="order-section">
                <h3><?= $e['tipo'] === 'venda' ? 'Obras' : 'Descrição' ?></h3>

                <?php if ($e['tipo'] === 'venda'): 
                    $titulos = explode(',', $e['titulos']);
                    $imagens = explode(',', $e['imagens']);
                    foreach ($titulos as $i => $titulo):
                ?>
                    <div style="margin-bottom:8px;">
                        <img src="../assets/img/obras/<?= trim($imagens[$i]) ?>" width="120">
                        <p><strong><?= htmlspecialchars(trim($titulo)) ?></strong></p>
                    </div>
                <?php endforeach; else: ?>
                    <?php if (!empty($e['imagens'])): ?>
                        <img src="../assets/img/encomendas_personalizadas/<?= $e['imagens'] ?>" width="120">
                    <?php endif; ?>
                    <p><?= nl2br(htmlspecialchars($e['titulos'])) ?></p>
                <?php endif; ?>

                <?php if (!is_null($e['total'])): ?>
                    <p><strong>Preço:</strong> <?= number_format($e['total'], 2, ',', '.') ?> €</p>
                <?php endif; ?>
            </div>

            <div class="order-section">
                <h3>Pagamento</h3>
                <p><strong>Método:</strong> <?= $e['metodo_pagamento'] ?: 'a definir' ?></p>
                <?php if (!is_null($e['total'])): ?>
                    <p><strong>Total:</strong> <?= number_format($e['total'], 2, ',', '.') ?> €</p>
                <?php endif; ?>
                <p><strong>Estado:</strong> <?= $e['estado_pagamento'] ?: 'a definir' ?></p>
            </div>

            <div class="order-section">
    <h3>Envio</h3>

    <?php if ($e['estado_pagamento'] === 'Reembolsado'): ?>
        
        <p>O envio foi cancelado devido ao reembolso.</p>

    <?php elseif (!empty($e['estado'])): ?>

        <p><strong>Transportadora:</strong> <?= $e['transportadora'] ?></p>
        <p><strong>Rastreio:</strong> <?= $e['numero_rastreio'] ?: '—' ?></p>
        <p><strong>Estado:</strong> <?= $e['estado'] ?></p>
        <p><strong>Data envio:</strong> <?= $e['data_envio'] ?></p>
        <p><strong>Data entrega:</strong> <?= $e['data_entrega'] ?: '—' ?></p>

    <?php else: ?>

        <p>Ainda não enviado</p>

    <?php endif; ?>
</div>

            
            <div class="order-actions">

                <!-- 2. REEMBOLSO BLOQUEADO (já existe envio) -->
                <?php if (!empty($e['estado'])): ?>
                    <button class="btn-reembolso-bloqueado" disabled>
                        Não é possível pedir reembolso após o envio
                    </button>
                <?php endif; ?>

                <!-- 3. PEDIDO DE REEMBOLSO ENVIADO (somente para esta encomenda) -->
                <?php if (
                    isset($_GET['reembolso']) &&
                    $_GET['reembolso'] === 'ok' &&
                    isset($_GET['pedido_id']) &&
                    $_GET['pedido_id'] == $e['pedido_id'] &&
                    empty($e['estado']) &&
                    $e['estado_pagamento'] === 'Concluído'
                ): ?>
                    <button class="btn-reembolso-pendente" disabled>
                        <i class="fa-solid fa-hourglass-half"></i> Pedido de Reembolso Enviado
                    </button>
                <?php endif; ?>

                <!-- 4. PEDIR REEMBOLSO (somente para concluídas sem envio) -->
                <?php if (
                    $e['tipo'] === 'venda' &&
                    $e['estado_pagamento'] === 'Concluído' &&
                    empty($e['estado']) &&
                    (
                        !isset($_GET['reembolso']) ||
                        $_GET['reembolso'] !== 'ok' ||
                        !isset($_GET['pedido_id']) ||
                        $_GET['pedido_id'] != $e['pedido_id']
                    )
                ): ?>
                    <button class="btn-reembolso"
                        onclick="abrirModalReembolso(<?= $e['pagamento_id'] ?>, <?= $e['pedido_id'] ?>)">
                        <i class="fa-solid fa-rotate-left"></i> Pedir Reembolso
                    </button>
                <?php endif; ?>

            </div>

        </div>
    <?php endforeach; ?>

</div>

<!-- Modal de Reembolso (FORA do foreach, no final da página) -->
<div class="modal" id="modal-reembolso">
    <div class="modal-content">
        <h3>Confirmar Pedido de Reembolso</h3>
        <p style="margin: 20px 0; color: #666;">
            Tem certeza que deseja solicitar o reembolso desta encomenda?
        </p>
        <p style="margin: 10px 0; color: #b91c1c; font-size: 0.9rem;">
            <i class="fa-solid fa-exclamation-triangle"></i> 
            Esta ação não pode ser desfeita.
        </p>
        <form method="POST" action="pedido_reembolso.php" id="form-reembolso">
            <input type="hidden" name="pagamento_id" id="reembolso-pagamento-id">
            <input type="hidden" name="venda_id" id="reembolso-venda-id">
            <button type="submit" class="modal-save" style="background: #b91c1c;">Confirmar Reembolso</button>
            <button type="button" class="modal-close">Cancelar</button>
        </form>
    </div>
</div>

<script>
function abrirModalReembolso(pagamentoId, vendaId) {
    document.getElementById('reembolso-pagamento-id').value = pagamentoId;
    document.getElementById('reembolso-venda-id').value = vendaId;
    document.getElementById('modal-reembolso').style.display = 'flex';
}

function alertarPintor(pedidoId, btn) {
    fetch('alertar_pintor.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + encodeURIComponent(pedidoId) + '&tipo=venda'
    })
    .then(response => response.text())
    .then(() => {
        btn.outerHTML = `
            <button class="btn-alertar-enviado" disabled>
                <i class="fa-solid fa-check"></i> Pintor Alertado
            </button>
        `;
    })
    .catch(err => {
        console.error(err);
        alert('Ocorreu um erro ao alertar o pintor.');
    });
}
</script>
<?php include 'includes/footer_cliente.php'; ?>