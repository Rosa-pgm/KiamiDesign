<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$admin_id = $_SESSION['id'];
$password = $_POST['password'] ?? '';

// Buscar password real
$stmt = $pdo->prepare("SELECT password FROM utilizador WHERE id = ?");
$stmt->execute([$admin_id]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password'])) {
    $_SESSION['mensagem'] = ['texto' => 'Password incorreta.', 'tipo' => 'erro'];
    header("Location: perfil_admin.php");
    exit;
}

// Verificar quantos admins existem
$count = $pdo->query("SELECT COUNT(*) FROM utilizador WHERE tipo = 'admin'")->fetchColumn();

if ($count <= 1) {
    $_SESSION['mensagem'] = ['texto' => 'Não pode eliminar a única conta de administrador.', 'tipo' => 'erro'];
    header("Location: perfil_admin.php");
    exit;
}

try {
    $pdo->beginTransaction();

    // ON DELETE CASCADE trata do resto
    $delete = $pdo->prepare("DELETE FROM utilizador WHERE id = ?");
    $delete->execute([$admin_id]);

    $pdo->commit();

    session_destroy();
   header("Location: ../login.php?eliminada=1");
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['mensagem'] = ['texto' => 'Erro ao eliminar conta.', 'tipo' => 'erro'];
    header("Location: perfil_admin.php");
    exit;
}
?>