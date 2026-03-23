<?php
session_start();
require_once 'includes/db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$erro = "";
$nome = $email = $telefone = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome     = trim($_POST['nome'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $pass     = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    // Validações
    if (!$nome || !$email || !$pass || !$confirm) {
        $erro = "Preencha todos os campos obrigatórios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "Email inválido.";
    } elseif ($pass !== $confirm) {
        $erro = "As passwords não coincidem.";
    } elseif (strlen($pass) < 8 || !preg_match('/[A-Z]/', $pass) || !preg_match('/\d/', $pass)) {
        $erro = "A password deve ter pelo menos 8 caracteres, conter uma letra maiúscula e um número.";
    }

    // Verificação de email duplicado - APENAS contas ATIVAS
    if (!$erro) {
        $checkEmail = $pdo->prepare("SELECT id FROM utilizador WHERE LOWER(email) = LOWER(?) AND estado_conta = 'ativa'");
        $checkEmail->execute([$email]);

        if ($checkEmail->rowCount() > 0) {
            $erro = "Este email já está registado.";
        }
    }

    // Inserção no banco de dados
    if (!$erro) {
        $hash = password_hash($pass, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
            INSERT INTO utilizador (nome, email, telefone, password, tipo, estado_conta)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $ok = $stmt->execute([$nome, $email, $telefone ?: null, $hash, 'cliente', 'ativa']);

        if ($ok) {
            // Envio do email 
            try {
                $mail = new PHPMailer(true);
                $mail->CharSet = 'UTF-8';
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'seuemail@gmail.com';
                $mail->Password = 'sua_password_google';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port = 465;

                $mail->setFrom('seuemail@gmail.com', 'Kiami Design');
                $mail->addAddress($email, $nome);
                $mail->isHTML(true);
                $mail->Subject = 'Bem-vindo a Kiami Design';

                $nomeSeguro = htmlspecialchars($nome, ENT_QUOTES, 'UTF-8');
                $mail->Body = "
                <div style='font-family:Arial,sans-serif; max-width:600px; margin:auto;'>
                    <h2>Olá {$nomeSeguro},</h2>
                    <p>Bem-vindo(a) ao universo artístico do Kiami.</p>
                    <p>A sua conta foi criada com sucesso.</p>
                    <p>Faça o seu <a href='http://localhost/PAP-14-KiamiDesign/projeto/login.php'>login</a>.</p>
                    <p style='font-size:12px;color:#777'>Se não criou esta conta, ignore este email.</p>
                    <p>— Kiami Design</p>
                </div>";
                $mail->send();
            } catch (Exception $e) {
                error_log("Erro ao enviar email: " . $e->getMessage());
            }

            header("Location: login.php?registo=ok");
            exit;
        } else {
            $erro = "Erro ao criar conta. Tente novamente.";
        }
    }
}
?>
<?php 
$titulo = "Registo";
include 'includes/header.php'; 
?>

<main class="auth-wrapper">
    <form class="auth-card" method="POST">
        <h2>Registo</h2>

        <?php if ($erro): ?>
            <div class="auth-error"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <label for="nome">Nome</label>
        <input type="text" name="nome" id="nome" placeholder="Digite seu nome" required 
               value="<?= htmlspecialchars($nome) ?>" class="auth-input">

        <label for="email">Email</label>
        <input type="email" name="email" id="email" placeholder="Digite seu email" required 
               value="<?= htmlspecialchars($email) ?>" class="auth-input">

        <label for="telefone">Telefone (opcional)</label>
        <input type="tel" name="telefone" id="telefone" placeholder="Digite seu telefone" 
               value="<?= htmlspecialchars($telefone) ?>" class="auth-input">

        <label for="password">Password</label>
        <div class="password-wrapper">
            <input type="password" name="password" id="password" placeholder="Digite sua password" required class="auth-input">
            <button type="button" id="togglePassword">
                <i class="fa-solid fa-eye" id="eyeIcon"></i>
            </button>
        </div>

        <ul id="passwordRequirements">
            <li id="length" class="invalid">Mínimo 8 caracteres</li>
            <li id="uppercase" class="invalid">Uma letra maiúscula</li>
            <li id="number" class="invalid">Um número</li>
        </ul>

        <label for="confirm_password">Confirmar Password</label>
        <div class="password-wrapper">
            <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirme sua password" required class="auth-input">
            <button type="button" id="toggleConfirmPassword">
                <i class="fa-solid fa-eye" id="confirmEyeIcon"></i>
            </button>
        </div>

        <div id="passwordMatch" class="invalid" style="display: none;">As passwords não coincidem.</div>

        <button class="btn" type="submit">Criar Conta</button>

        <p>Já tem conta? <a href="login.php">Entrar</a></p>
    </form>
        </main>



<?php include 'includes/footer.php'; ?>