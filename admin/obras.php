<!-- Resto do código permanece igual --><?php
$titulo = "Todas as Obras";
require 'includes/init_admin.php';
include 'includes/admin_header.php';

$sql = "
    SELECT 
        o.id,
        o.titulo,
        o.imagem,
        o.destaque,
        e.nome AS estado
    FROM obra o
    JOIN estado_obra e ON o.estado_id = e.id
    WHERE o.estado_id != 4  /* 4 = Arquivada */
    ORDER BY o.id DESC
";

$stmt = $pdo->query($sql);
?>

<!-- Resto do código permanece igual -->

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

<?php if(isset($_GET['sucesso'])): ?>
    <div class="alert-success">
        <?php
            switch ($_GET['sucesso']) {
                case 'obra_removida':
                    echo "Obra removida com sucesso!";
                    break;

                case 'obra_arquivada':
                    echo "Obra arquivada com sucesso!";
                    break;

                case 'obra_adicionada':
                    echo "Obra adicionada com sucesso!";
                    break;

                default:
                    echo "Operação realizada com sucesso!";
            }
        ?>
    </div>
<?php endif; ?>


    <div class="dashboard-cards" id="obras-container">

        <?php while ($obra = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
    <div class="card" 
         data-id="<?= $obra['id'] ?>" 
         data-destaque="<?= $obra['destaque'] ?>" 
         id="obra-<?= $obra['id'] ?>">

        <img src="../assets/img/obras/<?= htmlspecialchars($obra['imagem']) ?>" 
             style="width:100%; border-radius:6px">

        <h3><?= htmlspecialchars($obra['titulo']) ?></h3>

        <p><strong>Estado:</strong> <?= $obra['estado'] ?></p>

        <div style="display:flex; gap:10px; margin-top:10px; flex-wrap: wrap;">

            <a href="editar_obra.php?id=<?= $obra['id'] ?>" class="btn-editar">
                Editar
            </a>

            <?php if ($obra['destaque'] == 1): ?>
                <button class="btn-remover-destaque" 
                        data-id="<?= $obra['id'] ?>" 
                        data-acao="remover">
                    Remover dos Destaques
                </button>
            <?php else: ?>
                <button class="btn-adicionar-destaque" 
                        data-id="<?= $obra['id'] ?>" 
                        data-acao="adicionar">
                    Adicionar aos Destaques
                </button>
            <?php endif; ?>

            <!-- BOTÃO REMOVER AJUSTADO -->
            <button class="btn-remover-card" 
        onclick="abrirModalRemoverObra(<?= $obra['id'] ?>, '<?= htmlspecialchars(addslashes($obra['titulo'])) ?>')">
    Remover
</button>

        </div>

    </div>
<?php endwhile; ?>


    </div>
</main>
<div class="modal" id="modal-aviso-destaque">
    <div class="modal-content">
        <h3>Não é possível remover esta obra</h3>
        <p style="margin: 20px 0; color: #666;">
            Esta obra está nos destaques. Remova dos destaques antes de a eliminar.
        </p>
        <button type="button" class="modal-save" onclick="fecharModalAvisoDestaque()" style="background:#041C34;">
            OK
        </button>
    </div>
</div>

<!-- Modal de Confirmação de Remoção -->
<div class="modal" id="modal-remover-obra">
    <div class="modal-content">
        <h3>Remover Obra</h3>
        <p style="margin: 20px 0; color: #666;" id="modal-obra-titulo"></p>
        
        
        <form method="POST" action="obra_delete_ajax.php" id="form-remover-obra" onsubmit="return enviarRemocao(event)">
            <input type="hidden" name="id" id="remover-obra-id">
            <button type="submit" class="modal-save" style="background: #b91c1c;">Confirmar Remoção</button>
            <button type="button" class="modal-close">Cancelar</button>
        </form>
    </div>
</div>

<!-- Modal de Erro - Limite de Destaques -->
<div class="modal" id="modal-erro-destaque">
    <div class="modal-content">
        <h3>Limite de Destaques Atingido</h3>
        <p style="margin: 20px 0; color: #666;" id="modal-erro-mensagem">
            Só é permitido ter 3 obras em destaque.
        </p>
        <p style="margin: 10px 0; color: #b91c1c; font-size: 0.9rem;">
            <i class="fa-solid fa-exclamation-triangle"></i> 
            Remova uma obra dos destaques antes de adicionar outra.
        </p>
        <button type="button" class="modal-save" onclick="fecharModalErro()" style="background: #041C34;">OK</button>
    </div>
</div>

