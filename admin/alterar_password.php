<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['alterar_password'])) {
    header("Location: ../login.php");
    exit;
}

$admin_id = $_SESSION['alterar_password'];
$erro = "";
$sucesso = "";

// Processar formulário
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $pass = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    // Validações
    if (!$pass || !$confirm) {
        $erro = "Preencha ambos os campos de senha.";
    } elseif ($pass !== $confirm) {
        $erro = "As passwords não coincidem.";
    } elseif (strlen($pass) < 8 || !preg_match('/[A-Z]/', $pass) || !preg_match('/\d/', $pass)) {
        $erro = "A password deve ter pelo menos 8 caracteres, conter uma letra maiúscula e um número.";
    }

    if (!$erro) {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE utilizador SET password = ?, alterar_password = 0 WHERE id = ?");
        if ($stmt->execute([$hash, $admin_id])) {
            unset($_SESSION['alterar_password']);
            $_SESSION['mensagem'] = ['texto' => 'Password alterada com sucesso! Agora pode navegar normalmente.', 'tipo' => 'sucesso'];
            header("Location: perfil_admin.php");
            exit;
        } else {
            $erro = "Erro ao atualizar a senha. Tente novamente.";
        }
    }
}
?>

<?php $titulo = "Alterar Password"; ?>
<?php include 'includes/admin_header.php'; ?>

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

<!-- ===== BLOCO PARA MENSAGENS DE ERRO LOCAL ===== -->
<?php if ($erro): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.mostrarMensagem("<?= addslashes($erro) ?>", "erro");
        });
    </script>
<?php endif; ?>
<!-- ===== FIM DO BLOCO DE ERRO LOCAL ===== -->

<main class="admin-content">
    <div class="content-header">
        <h1><?= $titulo ?></h1>
        <button class="theme-content-btn" id="theme-toggle">
            <i class="fa-solid fa-sun" id="theme-icon"></i>
            <span id="theme-text">Modo Claro</span>
        </button>
    </div>
    
    <div class="auth-wrapper">
        <div style="max-width: 600px; margin: 0 auto;">
            <form class="auth-card" method="POST">
                <h2>Alterar Password</h2>

                <?php if (!$erro): ?>
                    <label for="password">Nova Password</label>
                    <div class="password-wrapper">
                        <input type="password" name="password" id="password" placeholder="Digite a nova password" required class="auth-input">
                        <button type="button" id="togglePassword">
                            <i class="fa-solid fa-eye-slash" id="eyeIcon"></i>
                        </button>
                    </div>

                    <ul id="passwordRequirements">
                        <li id="length" class="invalid">Mínimo 8 caracteres</li>
                        <li id="uppercase" class="invalid">Uma letra maiúscula</li>
                        <li id="number" class="invalid">Um número</li>
                    </ul>

                    <label for="confirm_password">Confirmar Password</label>
                    <div class="password-wrapper">
                        <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirme a password" required class="auth-input">
                        <button type="button" id="toggleConfirmPassword">
                            <i class="fa-solid fa-eye-slash" id="confirmEyeIcon"></i>
                        </button>
                    </div>

                    <div id="passwordMatch" class="invalid" style="display: none;">As passwords não coincidem.</div>

                    <button class="btn-save" type="submit" style="width: 50%; margin: 20px auto 0; display: block;">Alterar Password</button>
                <?php endif; ?>
            </form>
        </div>
    </div>
</main>
<script>
const password = document.getElementById("password");
const confirmPassword = document.getElementById("confirm_password");
const togglePassword = document.getElementById("togglePassword");
const toggleConfirmPassword = document.getElementById("toggleConfirmPassword");
const passwordMatch = document.getElementById("passwordMatch");

const lengthReq = document.getElementById("length");
const uppercaseReq = document.getElementById("uppercase");
const numberReq = document.getElementById("number");

// alternar visibilidade da password principal
if (togglePassword && password) {
    togglePassword.addEventListener("click", () => {
        const isPassword = password.type === "password";
        password.type = isPassword ? "text" : "password";

        const eyeIcon = document.getElementById("eyeIcon");
        if (eyeIcon) {
            eyeIcon.className = isPassword ? "fa-solid fa-eye" : "fa-solid fa-eye-slash";
        }
    });
}

// alternar visibilidade da confirmação
if (toggleConfirmPassword && confirmPassword) {
    toggleConfirmPassword.addEventListener("click", () => {
        const isPassword = confirmPassword.type === "password";
        confirmPassword.type = isPassword ? "text" : "password";

        const confirmEyeIcon = document.getElementById("confirmEyeIcon");
        if (confirmEyeIcon) {
            confirmEyeIcon.className = isPassword ? "fa-solid fa-eye" : "fa-solid fa-eye-slash";
        }
    });
}

// validar requisitos da password
if (password) {
    password.addEventListener("input", () => {
        const value = password.value;

        const lengthValid = value.length >= 8;
        const uppercaseValid = /[A-Z]/.test(value);
        const numberValid = /\d/.test(value);

        if (lengthReq) {
            lengthReq.className = lengthValid ? "valid" : "invalid";
            lengthReq.innerHTML = (lengthValid ? "✓" : "✗") + " Mínimo 8 caracteres";
        }
        if (uppercaseReq) {
            uppercaseReq.className = uppercaseValid ? "valid" : "invalid";
            uppercaseReq.innerHTML = (uppercaseValid ? "✓" : "✗") + " Uma letra maiúscula";
        }
        if (numberReq) {
            numberReq.className = numberValid ? "valid" : "invalid";
            numberReq.innerHTML = (numberValid ? "✓" : "✗") + " Um número";
        }
    });
}

// validar se as passwords coincidem
if (confirmPassword) {
    confirmPassword.addEventListener("input", () => {
        if (confirmPassword.value !== password.value) {
            passwordMatch.style.display = "block";
        } else {
            passwordMatch.style.display = "none";
        }
    });
}
</script>
