<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

require_once '../includes/db.php';
$titulo = "Gerir Loja";
include 'includes/admin_header.php';

$stmt = $pdo->prepare("
    SELECT o.*, c.nome AS material_nome, e.nome AS estado_nome
    FROM obra o
    JOIN material c ON o.material_id = c.id
    JOIN estado_obra e ON o.estado_id = e.id
    WHERE o.preco IS NOT NULL
      AND o.estado_id = 1   -- 1 = Disponível
    ORDER BY o.id ASC
");
$stmt->execute();
$obras = $stmt->fetchAll();

?>

<main class="admin-content">
<div class="content-header">
        <h1><?= $titulo ?></h1>
        <button class="theme-content-btn" id="theme-toggle">
            <i class="fa-solid fa-sun" id="theme-icon"></i>
            <span id="theme-text">Modo Claro</span>
        </button>
    </div>
    <!-- MODAL PARA VER IMAGEM EM GRANDE -->
    <div class="modal-img" id="modalImg">
        <img id="modalImgSrc">
    </div>


    <?php if (isset($_SESSION['obra_msg'])): ?>
        <div class="alerta-estado">
            <?= $_SESSION['obra_msg']; unset($_SESSION['obra_msg']); ?>
        </div>
    <?php endif; ?>

    <table class="tabela-admin">
        <tr>
            <th>ID</th>
            <th>Imagem</th>
            <th>Título</th>
            <th>Categoria</th>
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
                <form method="POST" action="update_obra.php">
                    <input type="hidden" name="id" value="<?= $o['id'] ?>">

                    
                       <select name="estado_id">
                            <option value="1" <?= $o['estado_id']==1 ? "selected" : "" ?>>Disponível</option>
                            <!-- Estados não listados na loja -->
                            <option value="2" <?= $o['estado_id']==2 ? "selected" : "" ?>>Vendida</option>
                            <option value="3" <?= $o['estado_id']==3 ? "selected" : "" ?>>Reservada</option>
                            <option value="5" <?= $o['estado_id']==5 ? "selected" : "" ?>>Em Exposição</option>
                            <option value="4" <?= $o['estado_id']==4 ? "selected" : "" ?>>Arquivada</option>
                            <option value="6" <?= $o['estado_id']==6 ? "selected" : "" ?>>Indisponível</option>
                            <option value="7" <?= $o['estado_id']==7 ? "selected" : "" ?>>Pendente</option>
                        </select>


                    

                    <button type="submit" class="btn-save">Guardar</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>

    </table>

        </main>
