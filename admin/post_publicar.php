<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$id = $_GET['id'];

$stmt = $pdo->prepare("UPDATE post SET estado='publicado' WHERE id=?");
$stmt->execute([$id]);

$_SESSION['mensagem'] = ['texto' => 'Post publicado com sucesso!', 'tipo' => 'sucesso'];
header("Location: posts_admin.php");
exit;
?>
