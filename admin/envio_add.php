<?php
require 'includes/init_admin.php';
require_once '../includes/db.php';
require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;

$titulo = "Criar Envio";

/* ============================================================
   BUSCAR PAGAMENTOS CONCLUÍDOS QUE AINDA NÃO TÊM ENVIO
============================================================ */
$vendas = $pdo->query("
    SELECT 
        p.id AS pagamento_id,
        u.id AS user_id,
        u.nome,
        u.email,
        u.morada,
        u.telefone,
        v.id AS venda_id,
        ep.id AS encomenda_id
    FROM pagamento p
    LEFT JOIN venda v ON v.id = p.venda_id
    LEFT JOIN encomenda_personalizada ep ON ep.id = p.encomenda_id
    LEFT JOIN utilizador u 
        ON u.id = v.user_id 
        OR u.id = ep.user_id
    LEFT JOIN envio e ON e.pagamento_id = p.id
    WHERE e.id IS NULL
      AND (p.estado = 'Concluído' OR p.estado = 'Pago')
    ORDER BY p.id DESC
")->fetchAll(PDO::FETCH_ASSOC);

$erro = null;

/* ============================================================
   PROCESSAR FORMULÁRIO DE CRIAÇÃO DE ENVIO
============================================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $pagamento_id = intval($_POST['pagamento_id'] ?? 0);

    // Buscar dados do cliente + obra
    $stmt = $pdo->prepare("
        SELECT 
            u.nome,
            u.email,
            u.telefone,
            u.morada AS endereco,
            COALESCE(o.titulo, 'Encomenda Personalizada') AS obra_titulo
        FROM pagamento p
        LEFT JOIN venda v ON v.id = p.venda_id
        LEFT JOIN encomenda_personalizada ep ON ep.id = p.encomenda_id
        LEFT JOIN utilizador u 
            ON u.id = v.user_id 
            OR u.id = ep.user_id
        LEFT JOIN venda_item vi ON vi.venda_id = v.id
        LEFT JOIN obra o ON o.id = vi.obra_id
        WHERE p.id = ?
    ");
    $stmt->execute([$pagamento_id]);
    $info = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$info) {
        $erro = "Pagamento não encontrado.";
    }

    // Pegar dados do formulário ou do cliente
    $transportadora    = trim($_POST['transportadora'] ?? '');
    $numero_rastreio   = trim($_POST['numero_rastreio'] ?? '');
    $nome_destinatario = $info['nome'] ?? '';
    $endereco          = $info['endereco'] ?? '';
    $telefone          = $info['telefone'] ?? '';
    $estado            = trim($_POST['estado'] ?? '');
    $data_envio        = $_POST['data_envio'] ?: null;
    $data_entrega      = $_POST['data_entrega'] ?: null;

    // Validar campos obrigatórios
    if (!$pagamento_id || !$transportadora || !$numero_rastreio || !$nome_destinatario || !$endereco || !$telefone || !$estado) {
        $erro = "Preencha todos os campos obrigatórios do envio.";
    }

    // Impedir criação duplicada
    if (!$erro) {
        $check = $pdo->prepare("SELECT id FROM envio WHERE pagamento_id = ?");
        $check->execute([$pagamento_id]);
        if ($check->fetch()) {
            $erro = "Este pagamento já tem um envio criado.";
        }
    }

    // Verificar estado do pagamento
    if (!$erro) {
        $stmt = $pdo->prepare("SELECT estado FROM pagamento WHERE id = ?");
        $stmt->execute([$pagamento_id]);
        $pagamento = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$pagamento || $pagamento['estado'] !== 'Concluído') {
            $erro = "Pagamento ainda não autorizado para envio.";
        }
    }

    // Validar datas
    $hoje = date('Y-m-d');
    if ($data_envio && $data_envio < $hoje) {
        $erro = "A data de envio não pode ser no passado.";
    }
    if (!$erro && $data_entrega && $data_entrega < $data_envio) {
        $erro = "A data de entrega não pode ser antes da data de envio.";
    }

    // Criar envio se não houver erros
    if (!$erro) {
        $stmt = $pdo->prepare("
            INSERT INTO envio 
            (pagamento_id, transportadora, numero_rastreio, nome_destinatario, endereco, telefone, estado, data_envio, data_entrega)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $pagamento_id,
            $transportadora,
            $numero_rastreio,
            $nome_destinatario,
            $endereco,
            $telefone,
            $estado,
            $data_envio,
            $data_entrega
        ]);

        // Enviar email ao cliente
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
        $mail->addAddress($info['email'], $info['nome']);
        $mail->isHTML(true);
        $mail->Subject = "A sua encomenda foi enviada! (Pagamento #$pagamento_id)";
        $mail->Body = "
            <h2>A sua encomenda está a caminho!</h2>
            <p>Olá <strong>{$info['nome']}</strong>,</p>
            <p>A sua obra <strong>{$info['obra_titulo']}</strong> já foi enviada.</p>
            <h3>Detalhes do Envio</h3>
            <p><strong>Transportadora:</strong> {$transportadora}</p>
            <p><strong>Número de rastreio:</strong> {$numero_rastreio}</p>
            <p><strong>Estado:</strong> {$estado}</p>
            <h3>Morada de Entrega</h3>
            <p>{$endereco}</p>
            <h3>Contacto</h3>
            <p>{$telefone}</p>

            <p>Se algum dos dados estiver errado, entre em contato o mais rápido possível.</p>
            <p>Obrigado por comprar na Kiami Design.</p>
            <p>— Kiami Design</p>
        ";
        $mail->send();

        // Enviar email ao admin
        $mailAdmin = new PHPMailer(true);
        $mailAdmin->CharSet = 'UTF-8';
        $mailAdmin->isSMTP();
        $mailAdmin->Host = 'smtp.gmail.com';
        $mailAdmin->SMTPAuth = true;
        $mailAdmin->Username = 'seuemail@gmail.com';
        $mailAdmin->Password = 'sua_senha_google';
        $mailAdmin->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mailAdmin->Port = 465;
        $mailAdmin->setFrom('seuemail@gmail.com', 'Kiami Design');
        $mailAdmin->addAddress('seuemail@gmail.com', 'Admin Kiami'); 
        $mailAdmin->isHTML(true);
        $mailAdmin->Subject = "Novo envio criado - Pagamento #$pagamento_id";
        $mailAdmin->Body = "
            <h2>Novo envio criado</h2>
            <p><strong>Cliente:</strong> {$info['nome']}</p>
            <p><strong>Email:</strong> {$info['email']}</p>
            <p><strong>Telefone:</strong> {$telefone}</p>
            <p><strong>Morada:</strong> {$endereco}</p>
            <p><strong>Obra:</strong> {$info['obra_titulo']}</p>
            <p><strong>Transportadora:</strong> {$transportadora}</p>
            <p><strong>Número de rastreio:</strong> {$numero_rastreio}</p>
            <p><strong>Estado do envio:</strong> {$estado}</p>
        ";
        $mailAdmin->send();

        // Redirecionar com sucesso
        $_SESSION['mensagem'] = ['texto' => 'Envio criado com sucesso!', 'tipo' => 'sucesso'];
header("Location: envios.php");
exit;
    }
}

include 'includes/admin_header.php';
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
<?php if ($erro): ?>
    <div class="alerta-erro"><?= htmlspecialchars($erro) ?></div>
<?php endif; ?>

<form method="POST">

    <label>Pagamento associado</label>
    <select name="pagamento_id" id="vendaSelect" required>
        <option value="">Selecione...</option>
        <?php foreach ($vendas as $v): ?>
            <option 
                value="<?= $v['pagamento_id'] ?>"
                data-nome="<?= htmlspecialchars($v['nome']) ?>"
                data-endereco="<?= htmlspecialchars($v['morada']) ?>"
                data-telefone="<?= htmlspecialchars($v['telefone']) ?>"
            >
                Pagamento #<?= $v['pagamento_id'] ?> — <?= $v['nome'] ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>Transportadora</label>
    <input type="text" name="transportadora" required>

    <label>Número de rastreio</label>
    <input type="text" name="numero_rastreio" required>

    <label>Nome do destinatário</label>
    <input type="text" name="nome_destinatario" id="nome_destinatario" required>

    <label>Morada completa</label>
    <textarea name="endereco" id="endereco" required></textarea>

    <label>Telefone</label>
    <input type="text" name="telefone" id="telefone" required>

    <label>Estado do envio</label>
    <select name="estado" required>
        <option value="preparando">Preparando</option>
        <option value="enviado">Enviado</option>
        <option value="entregue">Entregue</option>
    </select>

    <label>Data de envio</label>
    <input type="date" name="data_envio" id="data_envio"
       min="<?= date('Y-m-d') ?>"
       value="<?= date('Y-m-d') ?>" required>

    <label>Data de entrega</label>
    <input type="date" name="data_entrega" id="data_entrega"
       min="<?= date('Y-m-d') ?>" required>

    <button class="btn-save">Guardar Envio</button>
</form>
</div>
</main>

<script>
// Preenche automaticamente os campos ao selecionar o pagamento
document.getElementById('vendaSelect').addEventListener('change', function() {
    var opt = this.options[this.selectedIndex];
    document.getElementById('nome_destinatario').value = opt.dataset.nome || '';
    document.getElementById('endereco').value = opt.dataset.endereco || '';
    document.getElementById('telefone').value = opt.dataset.telefone || '';
});
</script>