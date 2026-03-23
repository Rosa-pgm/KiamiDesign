<?php
require_once 'includes/db.php';

$token = $_GET['token'] ?? '';
$erro = "";

// Verificar se o token é válido
$stmt = $pdo->prepare("
    SELECT pr.id, u.id AS user_id
    FROM password_reset pr
    JOIN utilizador u ON u.id = pr.user_id
    WHERE pr.token = ?
    AND pr.used = 0
    AND pr.expires_at > NOW()
");
$stmt->execute([$token]);
$reset = $stmt->fetch();

if (!$reset) {
    $erro = "Link inválido ou expirado. Solicite uma nova recuperação de password.";
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && $reset) {
    $pass = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    // Validações
    if (!$pass || !$confirm) {
        $erro = "Preencha todos os campos.";
    } elseif ($pass !== $confirm) {
        $erro = "As passwords não coincidem.";
    } elseif (strlen($pass) < 8 || !preg_match('/[A-Z]/', $pass) || !preg_match('/\d/', $pass)) {
        $erro = "A password deve ter pelo menos 8 caracteres, conter uma letra maiúscula e um número.";
    }

    if (!$erro) {
        $hash = password_hash($pass, PASSWORD_DEFAULT);

        // Atualizar password
        $pdo->prepare("UPDATE utilizador SET password = ? WHERE id = ?")
            ->execute([$hash, $reset['user_id']]);

        // Marcar token como usado
        $pdo->prepare("UPDATE password_reset SET used = 1 WHERE id = ?")
            ->execute([$reset['id']]);

        // Redirecionar com mensagem de sucesso
        header("Location: login.php?reset=ok");
        exit;
    }
}
?>

<?php 
$titulo = "Redefinir Password";
include 'includes/header.php'; 
?>

<main class="auth-wrapper">
    <div class="auth-card">
        <h2>Redefinir Password</h2>

        <?php if ($erro): ?>
            <div class="auth-error"><?= htmlspecialchars($erro) ?></div>
            <?php if (!$reset): ?>
                <p style="text-align: center; margin-top: 1rem;">
                    <a href="recuperar_password.php" class="btn" style="display: inline-block; padding: 0.8rem 2rem;">Solicitar novo link</a>
                </p>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($reset): ?>
            <form method="POST">
                <label for="password">Nova Password</label>
                <div class="password-wrapper">
                    <input type="password" 
                           name="password" 
                           id="password" 
                           placeholder="Digite a nova password" 
                           required 
                           class="auth-input">
                    <button type="button" id="togglePassword">
                        <i class="fa-solid fa-eye" id="eyeIcon"></i>
                    </button>
                </div>

                <!-- Requisitos da password (IDs específicos que o JS procura) -->
                <ul id="passwordRequirements">
                    <li id="length" class="invalid">Mínimo 8 caracteres</li>
                    <li id="uppercase" class="invalid">Uma letra maiúscula</li>
                    <li id="number" class="invalid">Um número</li>
                </ul>

                <label for="confirm_password">Confirmar Password</label>
                <div class="password-wrapper">
                    <input type="password" 
                           name="confirm_password" 
                           id="confirm_password" 
                           placeholder="Confirme a nova password" 
                           required 
                           class="auth-input">
                    <button type="button" id="toggleConfirmPassword">
                        <i class="fa-solid fa-eye" id="confirmEyeIcon"></i>
                    </button>
                </div>

                <div id="passwordMatch" class="invalid" style="display: none;">As passwords não coincidem.</div>

                <button class="btn" type="submit">Alterar Password</button>

                <p style="text-align: center; margin-top: 1rem;">
                    <a href="login.php">Voltar ao login</a>
                </p>
            </form>
        <?php endif; ?>
    </div>
</main>

<?php include 'includes/footer.php'; ?>