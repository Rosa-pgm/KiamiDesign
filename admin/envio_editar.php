<?php
require 'includes/init_admin.php';
require_once '../includes/db.php';
require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;

if (!isset($_GET['id'])) {
    header("Location: envios.php");
    exit;
}

$envio_id = $_GET['id'];

/* Buscar envio + venda + cliente */
$stmt = $pdo->prepare("
    SELECT 
        e.*,
        p.id AS pagamento_id,
        v.id AS venda_id,
        ep.id AS encomenda_id,
        u.nome AS cliente_nome,
        u.email AS cliente_email,
        COALESCE(o.titulo, 'Encomenda Personalizada') AS obra_titulo
    FROM envio e
    JOIN pagamento p ON p.id = e.pagamento_id
    LEFT JOIN venda v ON v.id = p.venda_id
    LEFT JOIN encomenda_personalizada ep ON ep.id = p.encomenda_id
    LEFT JOIN utilizador u 
        ON u.id = v.user_id 
        OR u.id = ep.user_id
    LEFT JOIN venda_item vi ON vi.venda_id = v.id
    LEFT JOIN obra o ON o.id = vi.obra_id
    WHERE e.id = ?
");
$stmt->execute([$envio_id]);
$envio = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$envio) {
    die("Envio não encontrado.");
}

/* Atualizar envio */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $stmt = $pdo->prepare("
        UPDATE envio SET
            transportadora = ?,
            numero_rastreio = ?,
            nome_destinatario = ?,
            endereco = ?,
            estado = ?,
            data_envio = ?,
            data_entrega = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $_POST['transportadora'],
        $_POST['numero_rastreio'],
        $_POST['nome_destinatario'],
        $_POST['endereco'],
        $_POST['estado'],
        $_POST['data_envio'] ?: null,
        $_POST['data_entrega'] ?: null,
        $envio_id
    ]);

    /* Enviar email ao cliente (opcional) */
    if (!empty($_POST['enviar_email'])) {

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
        $mail->addAddress($envio['cliente_email'], $envio['cliente_nome']);

        $mail->isHTML(true);
        $mail->Subject = "Atualização do envio da sua encomenda (Venda #{$envio['venda_id']})";

        $mail->Body = "
            <h2>Atualização do Envio</h2>
            <p>Olá <strong>{$envio['cliente_nome']}</strong>,</p>
            <p>O estado do envio da sua encomenda foi atualizado para:</p>
            <h3>{$_POST['estado']}</h3>

            <h3>Detalhes</h3>
            <p><strong>Transportadora:</strong> {$_POST['transportadora']}</p>
            <p><strong>Número de rastreio:</strong> {$_POST['numero_rastreio']}</p>

            <h3>Morada de Entrega</h3>
            <p>{$_POST['endereco']}</p>
            <p>Se algum dos dados estiver errado, entre em contato o mais rápido possível.</p>
            <p>Obrigado por comprar na Kiami Design.</p>
            <p>— Kiami Design</p>
        ";

        $mail->send();
    }

        $_SESSION['mensagem'] = ['texto' => 'Envio criado com sucesso!', 'tipo' => 'sucesso'];
header("Location: envios.php");
exit;
}
$titulo = "Editar Envio";

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

<form method="POST">

    <label>Encomenda / Venda associada</label>
    <input type="text" 
       value="<?= $envio['venda_id'] ? 'Venda #'.$envio['venda_id'] : 'Encomenda Personalizada #'.$envio['encomenda_id'] ?>" 
       disabled>


    <label>Transportadora</label>
    <input type="text" name="transportadora" value="<?= htmlspecialchars($envio['transportadora']) ?>" required>

    <label>Número de rastreio</label>
    <input type="text" name="numero_rastreio" value="<?= htmlspecialchars($envio['numero_rastreio']) ?>">

    <label>Nome do destinatário</label>
    <input type="text" name="nome_destinatario" value="<?= htmlspecialchars($envio['nome_destinatario']) ?>" required>

    <label>Morada completa</label>
    <textarea name="endereco" required><?= htmlspecialchars($envio['endereco']) ?></textarea>

    <label>Estado do envio</label>
    <select name="estado" required>
        <option <?= $envio['estado']=="Enviado" ? "selected" : "" ?>>Enviado</option>
        <option <?= $envio['estado']=="Em trânsito" ? "selected" : "" ?>>Em trânsito</option>
        <option <?= $envio['estado']=="Entregue" ? "selected" : "" ?>>Entregue</option>
    </select>

    <label>Data de envio</label>
<input type="date" name="data_envio"
       value="<?= $envio['data_envio'] ? substr($envio['data_envio'], 0, 10) : '' ?>">

<label>Data de entrega</label>
<input type="date" name="data_entrega"
       value="<?= $envio['data_entrega'] ? substr($envio['data_entrega'], 0, 10) : '' ?>">

    <label>
        <input type="checkbox" name="enviar_email"> Enviar email ao cliente sobre esta atualização
    </label>

    <button class="btn-save">Guardar Alterações</button>

</form>

</div>

</main>
