<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

require_once '../includes/db.php';
$titulo = 'Editar Pagamento';
$pagamento = null;
$erro = null;

/* ============================================================
   1. EDITAR PAGAMENTO DA LOJA (via pagamento_id)
============================================================ */
if (isset($_GET['id'])) {

    $pagamento_id = (int)$_GET['id'];

    $stmt = $pdo->prepare("
        SELECT 
            p.*,
            v.id AS venda_id,
            u.nome AS cliente
        FROM pagamento p
        LEFT JOIN venda v ON v.id = p.venda_id
        LEFT JOIN utilizador u ON u.id = v.user_id
        WHERE p.id = ?
    ");
    $stmt->execute([$pagamento_id]);
    $pagamento = $stmt->fetch(PDO::FETCH_ASSOC);
}

/* ============================================================
   2. EDITAR PAGAMENTO PERSONALIZADO (via encomenda_id)
============================================================ */
if (!$pagamento && isset($_GET['encomenda_id'])) {

    $encomenda_id = (int)$_GET['encomenda_id'];

    $stmt = $pdo->prepare("
        SELECT 
            p.*,
            ep.id AS encomenda_id,
            u.nome AS cliente
        FROM pagamento p
        JOIN encomenda_personalizada ep ON ep.id = p.encomenda_id
        JOIN utilizador u ON u.id = ep.user_id
        WHERE p.encomenda_id = ?
        ORDER BY p.id DESC
        LIMIT 1
    ");
    $stmt->execute([$encomenda_id]);
    $pagamento = $stmt->fetch(PDO::FETCH_ASSOC);
}

/* ============================================================
   3. ATUALIZAR PAGAMENTO
============================================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $pagamento_id = $_POST['pagamento_id'];
    $valor = $_POST['valor'];
    $metodo = ($_POST['metodo'] === "Outro") ? $_POST['metodo_outro'] : $_POST['metodo'];
    $estado = $_POST['estado'];

    $stmt = $pdo->prepare("
        UPDATE pagamento
        SET valor = ?, metodo_pagamento = ?, estado = ?, data_pagamento = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$valor, $metodo, $estado, $pagamento_id]);

    $_SESSION['mensagem'] = ['texto' => 'Pagamento atualizado com sucesso!', 'tipo' => 'sucesso'];
    header("Location: pagamentos.php");
    exit;
}

include 'includes/admin_header.php';
?>

<!-- ===== BLOCO PARA MENSAGENS DE ERRO ===== -->
<?php if ($erro): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.mostrarMensagem("<?= addslashes($erro) ?>", "erro");
        });
    </script>
<?php endif; ?>

<main class="admin-content">
    <div class="content-header">
        <h1><?= $titulo ?></h1>
        <button class="theme-content-btn" id="theme-toggle">
            <i class="fa-solid fa-sun" id="theme-icon"></i>
            <span id="theme-text">Modo Claro</span>
        </button>
    </div>
    
    <div class="form-card">
        <?php if (!$pagamento): ?>
            <div class="alert-error" style="text-align:center; padding:2rem;">
                <p>Pagamento não encontrado.</p>
                <a href="pagamentos.php" class="btn-save" style="margin-top:1rem;">Voltar</a>
            </div>
        <?php else: ?>

        <form method="POST">
            <input type="hidden" name="pagamento_id" value="<?= $pagamento['id'] ?>">

            <label>ID do Pagamento</label>
            <input type="text" value="<?= $pagamento['id'] ?>" disabled>

            <label>Cliente</label>
            <input type="text" value="<?= htmlspecialchars($pagamento['cliente']) ?>" disabled>

            <label>Valor Pago (€)</label>
            <input type="number" step="0.01" name="valor" value="<?= $pagamento['valor'] ?>" required>

           <label>Método de Pagamento</label>
<select name="metodo" id="metodoSelect" required>
    <?php
    // Métodos padrão do site (MESMOS QUE VÃO PARA A BD)
    $metodos_padrao = [
        'Cartão Bancário',
        'MBWay',
        'Coordenar com o pintor'  // ← CORRIGIDO (com "o")
    ];

    // Buscar métodos existentes na BD (excluindo os padrão)
    $stmt = $pdo->query("
        SELECT DISTINCT metodo_pagamento 
        FROM pagamento 
        WHERE metodo_pagamento NOT IN ('Cartão Bancário', 'MBWay', 'Coordenar com o pintor')
        ORDER BY metodo_pagamento ASC
    ");
    $outros_metodos = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Combinar tudo
    $todos_metodos = array_merge($metodos_padrao, $outros_metodos);
    ?>

    <?php foreach ($todos_metodos as $metodo_opcao): ?>
        <option value="<?= htmlspecialchars($metodo_opcao) ?>" 
            <?= $pagamento['metodo_pagamento'] === $metodo_opcao ? 'selected' : '' ?>>
            <?= htmlspecialchars($metodo_opcao) ?>
        </option>
    <?php endforeach; ?>
    
    <option value="Outro" <?= !in_array($pagamento['metodo_pagamento'], $todos_metodos) ? 'selected' : '' ?>>Outro</option>
</select>

            <div id="outroMetodoBox" style="display:<?= !in_array($pagamento['metodo_pagamento'], $todos_metodos) ? 'block' : 'none' ?>;">
                <label>Especificar método</label>
                <input type="text" name="metodo_outro" id="metodoOutroInput"
                       value="<?= !in_array($pagamento['metodo_pagamento'], $todos_metodos) ? htmlspecialchars($pagamento['metodo_pagamento']) : '' ?>"
                       placeholder="Digite o método de pagamento"
                       style="width:100%; padding:0.8rem; border:2px solid var(--border-color); border-radius:10px;">
            </div>

            <label>Estado</label>
            <select name="estado" required>
                <option value="Pendente" <?= $pagamento['estado'] === 'Pendente' ? 'selected' : '' ?>>Pendente</option>
                <option value="Concluído" <?= $pagamento['estado'] === 'Concluído' ? 'selected' : '' ?>>Concluído</option>
                <option value="Falhado" <?= $pagamento['estado'] === 'Falhado' ? 'selected' : '' ?>>Falhado</option>
                <option value="Reembolsado" <?= $pagamento['estado'] === 'Reembolsado' ? 'selected' : '' ?>>Reembolsado</option>
                <option value="Cancelado" <?= $pagamento['estado'] === 'Cancelado' ? 'selected' : '' ?>>Cancelado</option>
            </select>

            <button type="submit" class="btn-save">Guardar Alterações</button>
        </form>

        <?php endif; ?>
    </div>
</main>

<script>
document.getElementById('metodoSelect')?.addEventListener('change', function() {
    const box = document.getElementById('outroMetodoBox');
    const input = document.getElementById('metodoOutroInput');
    
    if (this.value === "Outro") {
        box.style.display = "block";
        input.required = true;
    } else {
        box.style.display = "none";
        input.required = false;
        input.value = '';
    }
});
</script>