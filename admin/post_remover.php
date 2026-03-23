<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$id = $_GET['id'];

$stmt = $pdo->prepare("UPDATE post SET estado='removido' WHERE id=?");
$stmt->execute([$id]);

$_SESSION['mensagem'] = ['texto' => 'Post removido com sucesso!', 'tipo' => 'sucesso'];
header("Location: posts_admin.php");
exit;
?>
