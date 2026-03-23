<?php
$titulo = "Adicionar Obra";
require 'includes/init_admin.php';
include 'includes/admin_header.php';

// Buscar materiais
$catStmt = $pdo->query("SELECT id, nome FROM material ORDER BY nome ASC");
$materiais = $catStmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar estados da obra
$estStmt = $pdo->query("SELECT id, nome FROM estado_obra ORDER BY id ASC");
$estados = $estStmt->fetchAll(PDO::FETCH_ASSOC);

//mensagem de sucesso
 if (isset($_SESSION['mensagem'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.mostrarMensagem(
                "<?= addslashes($_SESSION['mensagem']['texto']) ?>", 
                "<?= $_SESSION['mensagem']['tipo'] ?>"
            );
        });
    </script>
    <?php unset($_SESSION['mensagem']); ?>
<?php endif;
?>

<main class="admin-content">
    <div class="content-header">
        <h1><?= $titulo ?></h1>
        <button class="theme-content-btn" id="theme-toggle">
            <i class="fa-solid fa-sun" id="theme-icon"></i>
            <span id="theme-text">Modo Claro</span>
        </button>
    </div>

<div class="form-card">
<form action="obras_upload.php" method="POST" enctype="multipart/form-data">

    <label>Título</label>
    <input type="text" name="titulo" required>

    <label>Descrição</label>
    <textarea name="descricao"></textarea>

    <label>Estado</label>
    <select name="estado_id" required>
        <?php foreach ($estados as $est): ?>
            <option value="<?= $est['id'] ?>"><?= $est['nome'] ?></option>
        <?php endforeach; ?>
    </select>

    <label>Preço</label>
    <input type="number" step="0.01" min="0" name="preco" id="precoInput">

    <label>Dimensão</label>
    <input type="text" name="dimensao">

    <label>Material</label>
    <select name="material_id" id="materialSelect" required>
<option value="">Selecione...</option>

<?php foreach ($materiais as $cat): ?>
<option value="<?= $cat['id'] ?>"><?= $cat['nome'] ?></option>
<?php endforeach; ?>

<option value="outro">Outro</option>
</select>
<input type="text" name="novo_material" id="novoMaterial" placeholder="Novo material" style="display:none;">

    <label>Imagem</label>
    <input type="file" name="imagem" required>

    <button type="submit" class="btn-save">Guardar</button>

</form>
</div>
<!-- Script para controlar o campo preço -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const estadoSelect = document.querySelector('select[name="estado_id"]');
    const precoInput = document.getElementById('precoInput');
    const estadoDisponivelId = 1; // ID do estado "Disponível" (ajusta se necessário)
    
    // Função para verificar se deve desativar o campo preço
    function verificarEstado() {
        const estadoId = parseInt(estadoSelect.value);
        
        if (estadoId === estadoDisponivelId) {
            // Se estiver disponível, ativar o campo
            precoInput.disabled = false;
            precoInput.required = true;
            precoInput.style.opacity = '1';
            precoInput.title = '';
        } else {
            // Se não estiver disponível, desativar e limpar
            precoInput.disabled = true;
            precoInput.required = false;
            precoInput.value = ''; // Limpar o preço
            precoInput.style.opacity = '0.7';
            precoInput.title = 'Preço só pode ser definido para obras disponíveis';
        }
    }
    
    // Verificar ao carregar a página (se já houver um estado selecionado)
    if (estadoSelect.value) {
        verificarEstado();
    }
    
    // Verificar quando o estado mudar
    estadoSelect.addEventListener('change', verificarEstado);
});
document.addEventListener('DOMContentLoaded', function () {
    const materialSelect = document.getElementById('materialSelect');
    const novoMaterialInput = document.getElementById('novoMaterial');

    materialSelect.addEventListener('change', function () {
        if (this.value === 'outro') {
            novoMaterialInput.style.display = 'block';
            novoMaterialInput.required = true;
        } else {
            novoMaterialInput.style.display = 'none';
            novoMaterialInput.required = false;
            novoMaterialInput.value = '';
        }
    });
});
</script>
</main>