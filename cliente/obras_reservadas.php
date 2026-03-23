<?php
session_start();
require 'includes/init_cliente.php';
$titulo = "Minhas Reservas";
include 'includes/header_cliente.php';

$user_id = $_SESSION['id'];

$sql = "
    SELECT 
        r.venda_id,
        MIN(r.id) AS reserva_id, -- só para ter um ID de referência
        MIN(r.data_reserva) AS data_reserva,
        COUNT(*) AS total_obras,
        GROUP_CONCAT(o.titulo SEPARATOR '|||') AS titulos,
        GROUP_CONCAT(o.imagem SEPARATOR '|||') AS imagens,
        GROUP_CONCAT(o.preco SEPARATOR '|||') AS precos,
        SUM(o.preco) AS total_preco
    FROM reserva_cliente r
    JOIN obra o ON o.id = r.obra_id
    WHERE r.user_id = ?
    GROUP BY r.venda_id
    ORDER BY MIN(r.data_reserva) DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$reservasAgrupadas = $stmt->fetchAll();
?>

<div class="cliente-content">
    
    <h1>Minhas Reservas</h1>
    
    <div class="auth-card">


    <?php if (isset($_SESSION['alerta_sucesso'])): ?>
    <div class="auth-success">
        <?= $_SESSION['alerta_sucesso']; unset($_SESSION['alerta_sucesso']); ?>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['alerta_erro'])): ?>
    <div class="auth-error">
        <?= $_SESSION['alerta_erro']; unset($_SESSION['alerta_erro']); ?>
    </div>
<?php endif; ?>

        <?php if (empty($reservasAgrupadas)): ?>
            <p class="vazio-mensagem">Não tem reservas ainda.</p>
        <?php else: ?>

            <table class="tabela-admin">
                <tr>
                    <th>Compra #</th>
                    <th>Obras</th>
                    <th>Imagens</th>
                    <th>Preço Total</th>
                    <th>Data da Reserva</th>
                    <th>Ações</th>
                </tr>

                <?php foreach ($reservasAgrupadas as $r): 
                    $titulos = explode('|||', $r['titulos']);
                    $imagens = explode('|||', $r['imagens']);
                    $precos = explode('|||', $r['precos']);
                ?>
                <tr>
                    <td><strong>#<?= $r['venda_id'] ?></strong></td>
                    
                    <td>
                        <ul style="margin:0; padding-left:20px;">
                            <?php foreach ($titulos as $titulo): ?>
                                <li><?= htmlspecialchars($titulo) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </td>
                    
                    <td>
                        <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                            <?php foreach ($imagens as $img): ?>
                                <img src="../assets/img/obras/<?= trim($img) ?>" 
                                     width="50" height="50" 
                                     style="object-fit: cover; border-radius: 6px;">
                            <?php endforeach; ?>
                        </div>
                    </td>
                    
                    <td><strong><?= number_format($r['total_preco'], 2, ',', '.') ?> €</strong></td>
                    
                    <td><?= date("d/m/Y H:i", strtotime($r['data_reserva'])) ?></td>
                    
                    <td>
                        <div class="acoes-cell">
                            <!-- Botão Cancelar TODA a venda -->
                            <button class="modal-save" 
                                    onclick="abrirModalCancelarVenda(<?= $r['venda_id'] ?>)">
                                Cancelar Compra
                            </button>

                            <!-- Botão Alertar Pintor (para toda a venda) -->
                            <form method="POST" action="reserva_action.php" style="display:inline">
                                <input type="hidden" name="venda_id" value="<?= $r['venda_id'] ?>">
                                <button class="modal-save" name="acao" value="alertar_venda">
                                    Alertar o Pintor
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>

        <?php endif; ?>
    </div>
</div>

<!-- Modal de Cancelamento de Venda -->
<div class="modal" id="modal-cancelar-venda">
    <div class="modal-content">
        <h3>Cancelar Venda</h3>
        <p style="margin: 20px 0; color: #666;">
            Tem certeza que deseja cancelar TODAS as obras desta venda?
        </p>
        <p style="margin: 10px 0; color: #b91c1c; font-size: 0.9rem;">
            <i class="fa-solid fa-exclamation-triangle"></i> 
            Esta ação não pode ser desfeita e todas as obras voltarão a ficar disponíveis.
        </p>
        <form method="POST" action="reserva_action.php" id="form-cancelar-venda">
            <input type="hidden" name="venda_id" id="cancelar-venda-id">
            <input type="hidden" name="acao" value="cancelar">
            <button type="submit" class="modal-save" style="background: #b91c1c;">Confirmar Cancelamento</button>
            <button type="button" class="modal-close">Voltar</button>
        </form>
    </div>
</div>

<script>
function abrirModalCancelarVenda(vendaId) {
    document.getElementById('cancelar-venda-id').value = vendaId;
    document.getElementById('modal-cancelar-venda').style.display = 'flex';
}

// Auto-esconder mensagens após 5 segundos
document.addEventListener('DOMContentLoaded', function() {
    const mensagens = document.querySelectorAll('.auth-success, .auth-error');
    
    if (mensagens.length > 0) {
        mensagens.forEach(function(mensagem) {
            setTimeout(function() {
                mensagem.style.transition = 'opacity 0.5s ease';
                mensagem.style.opacity = '0';
                
                setTimeout(function() {
                    mensagem.style.display = 'none';
                }, 500);
            }, 5000); // 5 segundos
        });
    }
});
</script>


<?php include 'includes/footer_cliente.php'; ?>