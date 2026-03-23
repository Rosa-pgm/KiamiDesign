<?php
require 'includes/init_admin.php';
$titulo = "Editar Obra";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    echo "<p>ID inválido.</p>";
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM obra WHERE id = ?");
$stmt->execute([$id]);
$obra = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$obra) {
    echo "<p>Obra não encontrada.</p>";
    exit;
}

// buscar materiais e estados
$materiais = $pdo->query("SELECT id, nome FROM material ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
$estados   = $pdo->query("SELECT id, nome FROM estado_obra ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

// ID do estado "Disponível"
$estado_disponivel_id = 1; // ajustar se necessário

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $titulo      = trim($_POST['titulo'] ?? '');
    $descricao   = trim($_POST['descricao'] ?? '');
    $preco       = $_POST['preco'] ?? null;
    $dimensao    = trim($_POST['dimensao'] ?? '');
    $material_id = $_POST['material_id'] ?? null;
    $estado_id   = $_POST['estado_id'] ?? null;

    if ($preco !== null && $preco !== '' && $preco < 0) {
        $_SESSION['mensagem'] = ['texto' => 'O preço não pode ser negativo.', 'tipo' => 'erro'];
        header("Location: editar_obra.php?id=".$id);
        exit;
    }

    // preço obrigatório se disponível
    if ($estado_id == $estado_disponivel_id && empty($preco)) {
        $_SESSION['mensagem'] = ['texto' => 'O preço é obrigatório para obras disponíveis.', 'tipo' => 'erro'];
        header("Location: editar_obra.php?id=".$id);
        exit;
    }

    // impedir alteração do preço se não estiver disponível
    if ($estado_id != $estado_disponivel_id) {
        $preco = $obra['preco'];
    }

    /* ================= IMAGEM ================= */

    $nomeImagem = $obra['imagem'];

    if (!empty($_FILES['imagem']['name'])) {

        $ext = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));
        $permitidas = ['jpg','jpeg','png','webp'];

        if (!in_array($ext, $permitidas)) {
            $_SESSION['mensagem'] = ['texto' => 'Formato de imagem inválido.', 'tipo' => 'erro'];
            header("Location: editar_obra.php?id=".$id);
            exit;
        }

        $nomeImagem = time().'_'.uniqid().'.'.$ext;

        $destino = "../assets/img/obras/".$nomeImagem;

        move_uploaded_file($_FILES['imagem']['tmp_name'], $destino);
    }

    try {

        $stmt = $pdo->prepare("
            UPDATE obra
            SET titulo = ?, descricao = ?, preco = ?, dimensao = ?,
                material_id = ?, estado_id = ?, imagem = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $titulo,
            $descricao,
            $preco,
            $dimensao,
            $material_id,
            $estado_id,
            $nomeImagem,
            $id
        ]);

        $_SESSION['mensagem'] = ['texto' => 'Obra atualizada com sucesso!', 'tipo' => 'sucesso'];
        header("Location: obras.php");
        exit;

    } catch (Exception $e) {

        $_SESSION['mensagem'] = ['texto' => 'Erro ao atualizar obra.', 'tipo' => 'erro'];
        header("Location: editar_obra.php?id=".$id);
        exit;

    }

}

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

    <div class="form-card">
        <form action="editar_obra.php?id=<?= $id ?>" method="POST" enctype="multipart/form-data">

            <label>Título</label>
            <input type="text" name="titulo" value="<?= htmlspecialchars($obra['titulo']) ?>" required>

            <label>Descrição</label>
            <textarea name="descricao"><?= htmlspecialchars($obra['descricao']) ?></textarea>

            <label>Dimensão</label>
            <input type="text" name="dimensao" value="<?= htmlspecialchars($obra['dimensao']) ?>">

            <label>Material</label>
            <select name="material_id" required>
                <option value="">Selecione...</option>
                <?php foreach ($materiais as $mat): ?>
                    <option value="<?= $mat['id'] ?>" <?= $mat['id'] == $obra['material_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($mat['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Estado</label>
            <select name="estado_id" id="estado-select" data-disponivel-id="<?= $estado_disponivel_id ?>" required>
                <?php foreach ($estados as $est): ?>
                    <option value="<?= $est['id'] ?>" <?= $est['id'] == $obra['estado_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($est['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Preço (€)</label>
            <input type="number" step="0.01" min="0" name="preco" id="preco-input"
                   value="<?= htmlspecialchars($obra['preco']) ?>" placeholder="Preço da obra">

            <label>Imagem atual</label>
            <br>
            <?php if (!empty($obra['imagem'])): ?>
                <img src="../assets/img/obras/<?= htmlspecialchars($obra['imagem']) ?>"
                     style="max-width:150px;border-radius:6px;margin-bottom:10px;">
            <?php else: ?>
                <p>Sem imagem.</p>
            <?php endif; ?>

            <label>Nova imagem (opcional)</label>
            <input type="file" name="imagem">

            <button type="submit" class="btn-save">Guardar alterações</button>

        </form>
    </div>
</main>

<!-- Script para controlar o campo preço -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const estadoSelect = document.getElementById('estado-select');
    const precoInput = document.getElementById('preco-input');
    const estadoDisponivelId = <?= $estado_disponivel_id ?>;
    
    function verificarEstado() {
        const estadoId = parseInt(estadoSelect.value);
        
        if (estadoId === estadoDisponivelId) {
            precoInput.disabled = false;
            precoInput.required = true;
            precoInput.style.backgroundColor = '#fff';
            precoInput.style.opacity = '1';
            precoInput.title = '';
        } else {
            precoInput.disabled = true;
            precoInput.required = false;
            precoInput.value = '';
            precoInput.style.backgroundColor = '#f0f0f0';
            precoInput.style.opacity = '0.7';
            precoInput.title = 'Preço só pode ser definido para obras disponíveis';
        }
    }
    
    verificarEstado();
    estadoSelect.addEventListener('change', verificarEstado);
});
</script>

