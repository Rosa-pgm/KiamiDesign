<?php
require 'includes/init_admin.php';
require_once '../includes/db.php';
$titulo = "Gerir Utilizadores";
include 'includes/admin_header.php';
// Buscar todos os utilizadores
$stmt = $pdo->query("SELECT * FROM utilizador ORDER BY id ASC");
$users = $stmt->fetchAll();
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
        <h1>Utilizadores</h1>
        <a href="utilizador_adicionar.php" class="btn-editar" style="margin-bottom:20px; display:inline-block;">+ Adicionar Admin</a>
    

    <table class="tabela-admin">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Email</th>
                <th>Tipo</th>
                <th>Estado</th>
                <th>Ação</th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
                <td><?= $u['id'] ?></td>
                <td><?= htmlspecialchars($u['nome']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= $u['tipo'] ?></td>

                <td>
                    <?php if ($u['estado_conta'] === 'ativa'): ?>
                        <span style="color:green; font-weight:bold;">Ativa</span>
                    <?php else: ?>
                        <span style="color:red; font-weight:bold;">Eliminada</span>
                    <?php endif; ?>
                </td>

               <td>
    <button class="btn-editar" onclick="abrirModalEliminar(<?= $u['id'] ?>)">
        Eliminar
    </button>
</td>



            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</main>
<div class="modal" id="modal-eliminar">
    <div class="modal-content">
        <h3>Eliminar Utilizador</h3>
        <p>Tem a certeza que deseja eliminar este utilizador? Esta ação é irreversível.</p>

        <form method="POST" action="utilizador_eliminar.php">
            <input type="hidden" name="id" id="id-eliminar">

            <button type="submit" class="btn-editar">Eliminar</button>
            <button type="button" class="modal-close" onclick="fecharModal()">Cancelar</button>
        </form>
    </div>
</div>

<script>
function abrirModalEliminar(id) {
    document.getElementById('id-eliminar').value = id;
    document.getElementById('modal-eliminar').style.display = 'flex';
}

function fecharModal() {
    document.getElementById('modal-eliminar').style.display = 'none';
}
</script>
