<?php
session_start();
require_once '../includes/db.php';

// Verificar se o utilizador está logado e é admin
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$admin_id = $_SESSION['id'];
$user_id  = $_POST['id'] ?? null;

if (!$user_id) {
    $_SESSION['mensagem'] = ['texto' => 'Utilizador inválido.', 'tipo' => 'erro'];
    header("Location: utilizadores.php");
    exit;
}

// Buscar tipo do utilizador a eliminar
$stmt = $pdo->prepare("SELECT tipo FROM utilizador WHERE id = ?");
$stmt->execute([$user_id]);
$target = $stmt->fetch();

if (!$target) {
    $_SESSION['mensagem'] = ['texto' => 'Utilizador não encontrado.', 'tipo' => 'erro'];
    header("Location: utilizadores.php");
    exit;
}

// Contar admins existentes
$stmt = $pdo->query("SELECT COUNT(*) as total FROM utilizador WHERE tipo = 'admin'");
$adminCount = $stmt->fetch()['total'];

// Caso seja o próprio admin
if ($user_id == $admin_id && $target['tipo'] === 'admin' && $adminCount <= 1) {
    $_SESSION['mensagem'] = ['texto' => 'Não pode eliminar a sua própria conta, pois é o único administrador.', 'tipo' => 'erro'];
    header("Location: utilizadores.php");
    exit;
}

// Outro admin, impedir se único restante
if ($target['tipo'] === 'admin' && $user_id != $admin_id && $adminCount <= 1) {
    $_SESSION['mensagem'] = ['texto' => 'Não é possível eliminar este administrador, pois é o único existente.', 'tipo' => 'erro'];
    header("Location: utilizadores.php");
    exit;
}

// Deletar utilizador
try {
    $pdo->beginTransaction();
    $delete = $pdo->prepare("DELETE FROM utilizador WHERE id = ?");
    $delete->execute([$user_id]);
    $pdo->commit();

    $_SESSION['mensagem'] = ['texto' => 'Utilizador eliminado com sucesso.', 'tipo' => 'sucesso'];
    header("Location: utilizadores.php");
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['mensagem'] = ['texto' => 'Erro ao eliminar utilizador.', 'tipo' => 'erro'];
    header("Location: utilizadores.php");
    exit;
}
?>