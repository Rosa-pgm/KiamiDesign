<?php
require 'includes/init_admin.php';
require_once '../includes/db.php';

$admin_id = $_SESSION['id'];

function validarPassword($pass) {
    return !(
        strlen($pass) < 8 ||
        !preg_match('/^[A-Z]/', $pass) ||
        !preg_match('/\d/', $pass)
    );
}

if (!isset($_POST['campo'])) {
    header("Location: perfil_admin.php");
    exit;
}

$campo = $_POST['campo'];

/* ===============================
   ALTERAR PASSWORD
================================ */
if ($campo === 'password') {

    $nova = $_POST['password_nova'] ?? '';
    $conf = $_POST['password_confirmacao'] ?? '';

    if ($nova !== $conf) {
        $_SESSION['mensagem'] = ['texto' => 'As passwords não coincidem.', 'tipo' => 'erro'];
        header("Location: perfil_admin.php");
        exit;
    }

    if (!validarPassword($nova)) {
        $_SESSION['mensagem'] = ['texto' => 'A password deve ter 8+ caracteres, começar com maiúscula e conter um número.', 'tipo' => 'erro'];
        header("Location: perfil_admin.php");
        exit;
    }

    $hash = password_hash($nova, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("UPDATE utilizador SET password = ? WHERE id = ?");
    $stmt->execute([$hash, $admin_id]);

    $_SESSION['mensagem'] = ['texto' => 'Password alterada com sucesso!', 'tipo' => 'sucesso'];
    header("Location: perfil_admin.php");
    exit;
}

/* ===============================
   CAMPOS SIMPLES
================================ */
$valor = $_POST['valor'] ?? null;

// Se for telefone, pode validar (opcional)
if ($campo === 'telefone') {
    // Remove espaços extras mas mantém o + e números
    $valor = trim($valor);
    
    // Validar se tem pelo menos 8 dígitos (incluindo indicativo)
    $digitos = preg_replace('/\D/', '', $valor);
    if (strlen($digitos) < 8) {
        $_SESSION['mensagem'] = ['texto' => 'Número de telefone inválido.', 'tipo' => 'erro'];
        header("Location: perfil_admin.php");
        exit;
    }
}

try {
    $stmt = $pdo->prepare("UPDATE utilizador SET $campo = ? WHERE id = ?");
    $stmt->execute([$valor, $admin_id]);
    
    $_SESSION['mensagem'] = ['texto' => ucfirst($campo) . ' atualizado com sucesso!', 'tipo' => 'sucesso'];
} catch (Exception $e) {
    $_SESSION['mensagem'] = ['texto' => 'Erro ao atualizar. Tente novamente.', 'tipo' => 'erro'];
}

header("Location: perfil_admin.php");
exit;
?>