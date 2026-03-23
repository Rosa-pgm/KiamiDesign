<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

require_once '../includes/db.php';

$titulo = "Adicionar Pagamento";
require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;

/* ===============================
   ENCOMENDAS sem pagamento
================================ */
$stmt = $pdo->prepare("
    SELECT 
        ep.id,
        u.nome
    FROM encomenda_personalizada ep
    JOIN utilizador u ON u.id = ep.user_id
    LEFT JOIN pagamento p ON p.encomenda_id = ep.id
    WHERE p.id IS NULL
    ORDER BY ep.id DESC
");
$stmt->execute();
$encomendas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$erro = null;

/* ===============================
   PROCESSAR FORMULÁRIO (ANTES DO HTML)
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $encomenda_id = $_POST['encomenda_id'] ?? null;

    if (!$encomenda_id) {
        $erro = "Selecione uma encomenda.";
    }

    $valor = floatval($_POST['valor']);
    if ($valor <= 0) {
        $erro = "O valor deve ser maior que zero.";
    }

    if ($_POST['metodo'] === 'Outro' && !empty($_POST['metodo_outro'])) {
        $metodo = trim($_POST['metodo_outro']);
        $metodo = ucfirst(strtolower($metodo));
    } else {
        $metodo = $_POST['metodo'];
    }

    $estado = $_POST['estado'];

    // BLOQUEAR DUPLICADOS
    if (!$erro) {
        $check = $pdo->prepare("SELECT COUNT(*) FROM pagamento WHERE encomenda_id = ?");
        $check->execute([$encomenda_id]);

        if ($check->fetchColumn() > 0) {
            $erro = "Esta encomenda já tem pagamento.";
        }
    }
    
    // Buscar dados do utilizador e da encomenda
    $stmtUser = $pdo->prepare("
        SELECT u.email, u.nome
        FROM encomenda_personalizada ep
        JOIN utilizador u ON u.id = ep.user_id
        WHERE ep.id = ?
    ");
    $stmtUser->execute([$encomenda_id]);
    $cliente = $stmtUser->fetch(PDO::FETCH_ASSOC);

    // INSERIR PAGAMENTO
    if (!$erro) {
        $stmt = $pdo->prepare("
            INSERT INTO pagamento (encomenda_id, valor, metodo_pagamento, estado)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$encomenda_id, $valor, $metodo, $estado]);

        // ===============================
        //  ENVIAR EMAIL AO CLIENTE
        // ===============================
        $mail = new PHPMailer(true);
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';

        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'seuemail@gmail.com';
        $mail->Password = 'sua_password_google';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        $mail->setFrom('seuemail@gmail.com', 'Kiami Design');
        $mail->addAddress($cliente['email'], $cliente['nome']);

        $mail->isHTML(true);
        $mail->Subject = "Pagamento registado — Encomenda Personalizada #$encomenda_id";

        $mail->Body = "
            <h2>Pagamento Recebido</h2>
            <p>Olá <strong>{$cliente['nome']}</strong>,</p>
            <p>O pagamento da sua encomenda personalizada <strong>#{$encomenda_id}</strong> foi registado com sucesso.</p>
            <p><strong>Valor pago:</strong> {$valor}€<br>
               <strong>Método:</strong> {$metodo}<br>
               <strong>Estado:</strong> {$estado}</p>
            <p>Obrigado por confiar na Kiami Design.</p>
            <p>— Kiami Design</p>
        ";

        $mail->send();

        // ===== REDIRECIONAMENTO COM MENSAGEM PADRONIZADA =====
        $_SESSION['mensagem'] = ['texto' => 'Pagamento adicionado com sucesso!', 'tipo' => 'sucesso'];
        header("Location: pagamentos.php");
        exit;
    }
}

/* ===============================
   SÓ AGORA CARREGAR O HEADER
================================ */
include 'includes/admin_header.php';
?>

<!-- ===== BLOCO PARA MENSAGENS  ===== -->
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
        <form method="POST">
            <label>Encomenda Personalizada</label>
            <select name="encomenda_id" id="encomendaSelect" required>
                <option value="">— Selecionar Encomenda —</option>
                <?php foreach ($encomendas as $e): ?>
                    <option value="<?= $e['id'] ?>">
                        Encomenda Personalizada #<?= $e['id'] ?> — <?= htmlspecialchars($e['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Valor Pago (€)</label>
            <input type="number" step="0.01" min="0" name="valor" id="valorPago" required>

            <label>Método de Pagamento</label>
<?php
// Métodos padrão do site (MESMOS QUE VÃO PARA A BD)
$metodos_padrao = [
    'Cartão Bancário',
    'MBWay',
    'Coordenar com o pintor'  
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

<select name="metodo" id="metodoSelect" required>
    <option value="">— Selecione um método —</option>
    <?php foreach ($todos_metodos as $metodo): ?>
        <option value="<?= htmlspecialchars($metodo) ?>"><?= htmlspecialchars($metodo) ?></option>
    <?php endforeach; ?>
    <option value="Outro">Outro (especificar)</option>
</select>

            <input type="text" name="metodo_outro" id="metodoOutro"
                   placeholder="Digite o método de pagamento"
                   style="display:none; margin-top:8px; width:100%; padding:0.8rem; border:2px solid var(--border-color); border-radius:10px;">

            <label>Estado</label>
            <select name="estado" required>
                <option value="Pendente">Pendente</option>
                <option value="Concluído">Concluído</option>
            </select>

            <button type="submit" class="btn-save">Adicionar Pagamento</button>
        </form>
    </div>
</main>

<script>
const metodoSelect = document.getElementById('metodoSelect');
const metodoOutro = document.getElementById('metodoOutro');

metodoSelect.addEventListener('change', () => {
    if (metodoSelect.value === 'Outro') {
        metodoOutro.style.display = 'block';
        metodoOutro.required = true;
    } else {
        metodoOutro.style.display = 'none';
        metodoOutro.required = false;
        metodoOutro.value = '';
    }
});
</script>