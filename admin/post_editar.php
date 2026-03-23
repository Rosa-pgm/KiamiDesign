<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) exit("Post não encontrado.");

$stmt = $pdo->prepare("SELECT * FROM post WHERE id = ?");
$stmt->execute([$id]);
$post = $stmt->fetch();

if (!$post) exit("Post não encontrado.");

$titulo = "Editar Post";
include 'includes/admin_header.php';
?>

<main class="admin-content">
<div class="content-header">
        <h1><?= $titulo ?></h1>
        <button class="theme-content-btn" id="theme-toggle">
            <i class="fa-solid fa-sun" id="theme-icon"></i>
            <span id="theme-text">Modo Claro</span>
        </button>
    </div>
    <div class="form-card">

<form action="post_editar_guardar.php" method="POST" enctype="multipart/form-data" class="form-admin">

    <input type="hidden" name="id" value="<?= $post['id'] ?>">

    <label>Título</label>
    <input type="text" name="titulo" value="<?= htmlspecialchars($post['titulo']) ?>" required>

    <label>Descrição</label>
    <textarea name="descricao" rows="6" required><?= htmlspecialchars($post['descricao']) ?></textarea>

    <label>Estado</label>
    <select name="estado">
        <option value="publicado" <?= $post['estado']=="publicado"?"selected":"" ?>>Publicado</option>
        <option value="removido" <?= $post['estado']=="removido"?"selected":"" ?>>Removido</option>
    </select>

    <label>Ficheiro atual:</label>
    <?php if ($post['tipo'] === 'imagem'): ?>
        <img src="../assets/posts/<?= htmlspecialchars($post['caminho']) ?>" class="thumb">
    <?php else: ?>
        <video width="120" controls>
            <source src="../assets/posts/<?= htmlspecialchars($post['caminho']) ?>">
        </video>
    <?php endif; ?>

    <label>Alterar ficheiro (opcional)</label>
    <input type="file" name="ficheiro" accept="image/*,video/mp4">

    <button type="submit" class="btn-save">Guardar alterações</button>
</form>
    </div>
    </main>
