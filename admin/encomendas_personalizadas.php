<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

require_once '../includes/db.php';

$stmt = $pdo->query("SELECT e.*, u.nome AS cliente_nome, u.email AS cliente_email
                     FROM encomenda_personalizada e
                     LEFT JOIN utilizador u ON e.user_id = u.id
                     ORDER BY e.data_pedido ASC");
$encomendas = $stmt->fetchAll();

$titulo = "Encomendas Personalizadas";
include 'includes/admin_header.php';
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
<main class="admin-content">
<div class="content-header">
        <h1><?= $titulo ?></h1>
        <button class="theme-content-btn" id="theme-toggle">
            <i class="fa-solid fa-sun" id="theme-icon"></i>
            <span id="theme-text">Modo Claro</span>
        </button>
    </div>
    <!-- MODAL PARA VER IMAGEM EM GRANDE -->
    <div class="modal-img" id="modalImg">
    <img id="modalImgSrc">
    </div>



    <table class="tabela-admin">
    <tr>
        <th>ID</th>
        <th>Cliente</th>
        <th>Descrição</th>
        <th>Imagem</th>
        <th>Estado</th>
        <th>Atualizar</th>
    </tr>

    <?php foreach ($encomendas as $e): ?>
    <tr>
        <td><?= $e['id'] ?></td>
        <td>
            <?= $e['cliente_nome'] ?><br>
            <small><?= $e['cliente_email'] ?></small>
        </td>
        <td class="descricao"><?= nl2br(htmlspecialchars($e['descricao'])) ?></td>
        <td>
            <?php if ($e['imagem']): ?>
                <img src="../assets/img/encomendas_personalizadas/<?= $e['imagem'] ?>"
                     class="thumb"
                     data-img="../assets/img/encomendas_personalizadas/<?= $e['imagem'] ?>">
            <?php else: ?>
                <span style="color:#888;">Sem imagem</span>
            <?php endif; ?>
        </td>
        <td><?= $e['estado'] ?></td>
        <td>
            <form method="POST" action="update_estadoEncom.php">
                <input type="hidden" name="id" value="<?= $e['id'] ?>">
                <select name="estado">
                    <option <?= $e['estado']=="Pendente" ? "selected" : "" ?>>Pendente</option>
                    <option <?= $e['estado']=="Em produção" ? "selected" : "" ?>>Em produção</option>
                    <option <?= $e['estado']=="Concluida" ? "selected" : "" ?>>Concluída</option>
                    <option <?= $e['estado']=="Enviada" ? "selected" : "" ?>>Enviada</option>
                    <option <?= $e['estado']=="Cancelada" ? "selected" : "" ?>>Cancelada</option>
                </select>
                <button type="submit" class="btn-save">Guardar</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
    </table>


            </main>


