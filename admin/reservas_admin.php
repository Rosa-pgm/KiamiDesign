<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

require_once '../includes/db.php';
$titulo = "Obras Reservadas";
include 'includes/admin_header.php';

// Buscar apenas obras reservadas
$stmt = $pdo->prepare("
    SELECT o.*, c.nome AS material_nome, e.nome AS estado_nome
    FROM obra o
    JOIN material c ON o.material_id = c.id
    JOIN estado_obra e ON o.estado_id = e.id
    WHERE o.estado_id = 3   -- 3 = Reservada
    ORDER BY o.id ASC
");
$stmt->execute();
$obras = $stmt->fetchAll();


// BLOCO PARA MENSAGENS (PADRONIZADO) 
 if (isset($_SESSION['mensagem'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.mostrarMensagem(
                "<?= addslashes($_SESSION['mensagem']['texto']) ?>", 
                "<?= $_SESSION['mensagem']['tipo'] ?>"
            );
        });
    </script>
    <?php unset($_SESSION['mensagem']); ?>
<?php endif; ?>
<!-- ===== FIM DO BLOCO DE MENSAGENS ===== -->

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
            <th>Imagem</th>
            <th>Título</th>
            <th>Material</th>
            <th>Preço</th>
            <th>Estado</th>
            <th>Atualizar</th>
        </tr>

        <?php foreach ($obras as $o): ?>
        <tr>
            <td><?= $o['id'] ?></td>

            <td>
                <img 
                src="../assets/img/obras/<?= htmlspecialchars($o['imagem']) ?>"
                class="thumb"
                data-img="../assets/img/obras/<?= htmlspecialchars($o['imagem']) ?>">
            </td>

            <td><?= htmlspecialchars($o['titulo']) ?></td>
            <td><?= htmlspecialchars($o['material_nome']) ?></td>
            <td><?= number_format($o['preco'], 2) ?> €</td>

            <td><?= $o['estado_nome'] ?></td>

            <td>
                <form method="POST" action="update_reserva.php">
                    <input type="hidden" name="id" value="<?= $o['id'] ?>">

                    <select name="estado_id">
                        <option value="1">Disponível</option>
                        <option value="2">Vendida</option>
                        <option value="3" selected>Reservada</option>
                    </select>

                    <button type="submit" class="btn-save">Guardar</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>

    </table>

        </main>
