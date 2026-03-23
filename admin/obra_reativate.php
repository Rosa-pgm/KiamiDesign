<?php
require 'includes/init_admin.php';

if (!isset($_GET['id'])) {
    header("Location: obras_removidas.php");
    exit;
}

$id = intval($_GET['id']);

// Reativar como "Indisponível" (ID = 6)
$sql = "UPDATE obra SET estado_id = 6 WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $id]);

header("Location: obras_removidas.php");
exit;
