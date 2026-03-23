<?php
require 'includes/init_admin.php';

if (!isset($_GET['id'])) {
    header("Location: obras.php");
    exit;
}

$id = intval($_GET['id']);

// Verificar se está nos destaques
$stmt = $pdo->prepare("SELECT destaque FROM obra WHERE id = ?");
$stmt->execute([$id]);
$obra = $stmt->fetch();

if (!$obra) {
    $_SESSION['erro'] = "Obra não encontrada.";
    header("Location: obras.php");
    exit;
}

// 1️ Se estiver em destaque → impedir remoção
if ($obra['destaque'] == 1) {
    $_SESSION['erro'] = "Esta obra está nos destaques. Remova dos destaques antes de eliminar.";
    header("Location: obras.php");
    exit;
}

// 2 Agora sim, remover (estado 4 = arquivada)
$stmt = $pdo->prepare("UPDATE obra SET estado_id = 4 WHERE id = ?");
$stmt->execute([$id]);

$_SESSION['sucesso'] = "Obra removida com sucesso.";
header("Location: obras.php");
exit;
