<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

require_once '../includes/db.php';

$sql = "SELECT * FROM mensagem ORDER BY data_envio DESC";
$stmt = $pdo->query($sql);
$mensagens = $stmt->fetchAll();

$titulo = "Todas as Mensagens";
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

    <table class="tabela-admin">
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Email</th>
            <th>Mensagem</th>
            <th>Resposta</th>
            <th>Data</th>
        </tr>

        <?php foreach ($mensagens as $c): ?>
        <tr>
            <td><?= $c['id'] ?></td>
            <td><?= htmlspecialchars($c['nome']) ?></td>
            <td><?= htmlspecialchars($c['email']) ?></td>
            <td><?= nl2br(htmlspecialchars($c['mensagem'])) ?></td>

           <td>
    <?php if ($c['resposta']): ?>
        <strong>Respondido:</strong><br>
        <?= nl2br(htmlspecialchars($c['resposta'])) ?><br>
        <small><em><?= date('d/m/Y H:i', strtotime($c['data_resposta'])) ?></em></small>
    <?php else: ?>
        <form method="POST" action="mensagem_responder.php">
            <input type="hidden" name="id" value="<?= $c['id'] ?>">
            <textarea name="resposta" required 
                style="width:100%; height:80px; padding:10px; font-family:inherit; border:2px solid var(--border-color); border-radius:8px; margin-bottom:8px; background:var(--bg-primary); color:var(--text-primary);"></textarea>
            <button class="btn-responder-verde" style="margin-top:5px;">Enviar Resposta</button>
        </form>
    <?php endif; ?>
</td>

            <td><?= date('d/m/Y H:i', strtotime($c['data_envio'])) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
                </main>
