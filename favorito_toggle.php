<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['id'])) {
    echo json_encode(["erro" => "not_logged"]);
    exit;
}

$user_id = $_SESSION['id'];
$obra_id = $_POST['obra_id'] ?? null;

$stmt = $pdo->prepare("SELECT id FROM favorito WHERE user_id = ? AND obra_id = ?");
$stmt->execute([$user_id, $obra_id]);
$existe = $stmt->fetch();

if ($existe) {
    $pdo->prepare("DELETE FROM favorito WHERE id = ?")->execute([$existe['id']]);
    echo json_encode(["estado" => "removido"]);
} else {
    $pdo->prepare("INSERT INTO favorito (user_id, obra_id) VALUES (?, ?)")->execute([$user_id, $obra_id]);
    echo json_encode(["estado" => "adicionado"]);
}
