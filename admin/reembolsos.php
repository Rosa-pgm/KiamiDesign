<?php 
require 'includes/init_admin.php';
include 'includes/admin_header.php';


foreach ($pedidos as $p): ?>
<tr>
    <td><?= $p['venda_id'] ?></td>
    <td><?= $p['nome_cliente'] ?></td>
    <td><?= $p['total'] ?> €</td>
    <td><?= $p['metodo_pagamento'] ?></td>
    <td>
        <a href="confirmar_reembolso.php?venda_id=<?= $p['venda_id'] ?>&pagamento_id=<?= $p['pagamento_id'] ?>"
           class="btn-cancelar"
           onclick="return confirm('Confirmar que o reembolso já foi feito na Stripe?');">
           Confirmar Reembolso
        </a>
    </td>
</tr>
<?php endforeach; ?>
