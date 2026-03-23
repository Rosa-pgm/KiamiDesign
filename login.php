<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'includes/db.php';

$erro = "";
$email = "";

// Verificar se veio da eliminação de conta
$mostrar_mensagem_eliminada = false;
if (isset($_GET['eliminada']) && $_GET['eliminada'] == 1) {
    $mostrar_mensagem_eliminada = true;
    // Limpar o parâmetro da URL (opcional, mas bom para UX)
    echo "<script>history.replaceState({}, '', 'login.php');</script>";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if (!$email || !$pass) {
        $erro = "Preencha todos os campos.";
    } else {
        // Verificar email (case insensitive) e apenas contas ativas
        $stmt = $pdo->prepare("SELECT * FROM utilizador WHERE LOWER(email) = LOWER(?)");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            $erro = "Não existe conta com esse email. Registe-se.";
        } elseif ($user['estado_conta'] === 'eliminada') {
            $erro = "Esta conta foi eliminada. Não é possível fazer login.";
        } elseif (!password_verify($pass, $user['password'])) {
            $erro = "Password incorreta.";
        } else {
            // Login OK
            $_SESSION['id']    = $user['id'];
            $_SESSION['nome']  = $user['nome'];
            $_SESSION['tipo']  = $user['tipo'];
            $_SESSION['email'] = $user['email'];
            
            if (isset($_SESSION['login_redirect'])) {
                $destino = $_SESSION['login_redirect'];
                unset($_SESSION['login_redirect']);
                header("Location: $destino");
                exit;
            }
            
            if ($user['tipo'] === 'admin' && $user['alterar_password']) {
                $_SESSION['alterar_password'] = $user['id'];
                header("Location: admin/alterar_password.php");
                exit;
            }
            
            if ($user['tipo'] === 'admin') {
                header("Location: admin/dashboard.php");
                exit;
            } else {
                header("Location: cliente/perfil_cliente.php");
                exit;
            }
        }
    }
}
?>

<?php 
$titulo = "Login";
include 'includes/header.php'; 
?>

<div class="auth-wrapper">
    <form class="auth-card" method="POST">

        <h2>Login</h2>

        <?php if (isset($_GET['registo'])): ?>
            <div class="auth-success">Conta criada com sucesso. Faça login.</div>
        <?php endif; ?>

        <?php if ($mostrar_mensagem_eliminada): ?>
            <div class="auth-success">Conta eliminada com sucesso.</div>
        <?php endif; ?>

        <?php if ($erro): ?>
            <div class="auth-error"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['auth_msg'])): ?>
            <div class="auth-error"><?= $_SESSION['auth_msg']; unset($_SESSION['auth_msg']); ?></div>
        <?php endif; ?>

        <label for="email">Email</label>
        <input 
            type="email" 
            name="email" 
            id="email"
            placeholder="Digite seu email" 
            required 
            class="auth-input"
            value="<?= htmlspecialchars($email) ?>"
        >

        <label for="password">Password</label>
        <div class="password-wrapper">
            <input 
                type="password" 
                name="password" 
                id="password"  
                placeholder="Digite sua password" 
                required 
                class="auth-input"
            >
            <button type="button" id="togglePassword">
                <i class="fa-solid fa-eye" id="eyeIcon"></i> 
            </button>
        </div>

        <button class="btn" type="submit">Entrar</button>

        <p><a href="recuperar_password.php">Esqueceu a password?</a></p>
        <p>Não tem conta? <a href="registo.php">Criar conta</a></p>

    </form>
</div>

<?php include 'includes/footer.php'; ?>