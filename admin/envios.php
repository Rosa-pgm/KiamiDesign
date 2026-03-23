<?php
require 'includes/init_admin.php';
require_once '../includes/db.php';
$titulo = "Lista de Envios";

// Buscar todos os envios + venda + pagamento
$stmt = $pdo->query("
    SELECT 
        e.id,
        e.transportadora,
        e.numero_rastreio,
        e.nome_destinatario,
        e.estado AS estado_envio,
        e.data_envio,
        e.data_entrega,

        p.valor AS total_pago,
        v.id AS venda_codigo,
        ep.id AS encomenda_codigo

    FROM envio e
    JOIN pagamento p ON p.id = e.pagamento_id
    LEFT JOIN venda v ON v.id = p.venda_id
    LEFT JOIN encomenda_personalizada ep ON ep.id = p.encomenda_id
    ORDER BY e.id ASC
");

$envios = $stmt->fetchAll();
include 'includes/admin_header.php'; ?>

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

<main class="admin-content">

<div class="content-header">
        <h1><?= $titulo ?></h1>
        <button class="theme-content-btn" id="theme-toggle">
            <i class="fa-solid fa-sun" id="theme-icon"></i>
            <span id="theme-text">Modo Claro</span>
        </button>
    </div>

<a href="envio_add.php" class="btn-editar" style="margin-bottom:20px; display:inline-block;">
    + Criar Novo Envio
</a>

<table class="tabela-admin">
    <thead>
        <tr>
            <th>ID</th>
            <th>Venda</th>
            <th>Total Pago</th>
            <th>Transportadora</th>
            <th>Rastreio</th>
            <th>Destinatário</th>
            <th>Estado</th>
            <th>Data Envio</th>
            <th>Data Entrega</th>
            <th>Ação</th>
        </tr>
    </thead>

    <tbody>
        <?php foreach ($envios as $e): ?>
        <tr>
            <td><?= $e['id'] ?></td>

            <td>
                <?php 
                if (!empty($e['venda_codigo'])) {
                    echo "Venda #" . $e['venda_codigo'];
                } elseif (!empty($e['encomenda_codigo'])) {
                    echo "Encomenda Personalizada #" . $e['encomenda_codigo'];
                } else {
                    echo "-";
                }
                ?>
            </td>

            <td><?= $e['total_pago'] ? number_format($e['total_pago'], 2, ',', '.') . ' €' : '-' ?></td>
            <td><?= htmlspecialchars($e['transportadora']) ?></td>
            <td><?= htmlspecialchars($e['numero_rastreio']) ?></td>
            <td><?= htmlspecialchars($e['nome_destinatario']) ?></td>
            <td><?= htmlspecialchars($e['estado_envio']) ?></td>
            <td><?= $e['data_envio'] ? date("d/m/Y", strtotime($e['data_envio'])) : '-' ?></td>
            <td><?= $e['data_entrega'] ? date("d/m/Y", strtotime($e['data_entrega'])) : '-' ?></td>
            <td><a href="envio_editar.php?id=<?= $e['id'] ?>" class="btn-editar">Editar</a></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</main>

</body>
</html>