<?php
require 'includes/init_cliente.php';
$cliente_id = $_SESSION['id'];

function validarPassword($pass) {
    return strlen($pass) >= 8 && 
           preg_match('/[A-Z]/', $pass) && 
           preg_match('/[0-9]/', $pass);
}

if (!isset($_POST['campo'])) {
    header("Location: perfil_cliente.php");
    exit;
}

$campo = $_POST['campo'];

/* ===============================
   ALTERAR PASSWORD
================================ */
if ($campo === 'password') {

    $nova = $_POST['password_nova'] ?? '';
    $confirmacao = $_POST['password_confirmacao'] ?? '';

    if ($nova !== $confirmacao) {
        header("Location: perfil_cliente.php?erro=confirmacao");
        exit;
    }

    if (!validarPassword($nova)) {
        header("Location: perfil_cliente.php?erro=formato");
        exit;
    }

    $hash = password_hash($nova, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("UPDATE utilizador SET password = ? WHERE id = ?");
    $stmt->execute([$hash, $cliente_id]);

    header("Location: perfil_cliente.php?sucesso=password");
    exit;
}

/* ===============================
   NEWSLETTER
================================ */
if ($campo === 'newsletter') {

    $valor = isset($_POST['valor']) ? 1 : 0;

    $stmt = $pdo->prepare("UPDATE utilizador SET newsletter = ? WHERE id = ?");
    $stmt->execute([$valor, $cliente_id]);

    header("Location: perfil_cliente.php?sucesso=newsletter");
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
        header("Location: perfil_cliente.php?erro=telefone_invalido");
        exit;
    }
}

$stmt = $pdo->prepare("UPDATE utilizador SET $campo = ? WHERE id = ?");
$stmt->execute([$valor, $cliente_id]);

header("Location: perfil_cliente.php?sucesso=$campo");
exit;