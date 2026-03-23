<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$titulo = "Criar Post";
include 'includes/admin_header.php';
?>

<<main class="admin-content">
    <div class="content-header">
        <h1><?= $titulo ?></h1>
        <button class="theme-content-btn" id="theme-toggle">
            <i class="fa-solid fa-sun" id="theme-icon"></i>
            <span id="theme-text">Modo Claro</span>
        </button>
    </div>
    
    <div class="form-card">
        <form action="guardar_post.php" method="POST" enctype="multipart/form-data" class="form-admin">

            <label>Título</label>
            <input type="text" name="titulo" required>

            <label>Descrição</label>
            <textarea name="descricao" rows="6" required></textarea>

            <label>Tipo</label>
            <select name="tipo" id="tipoSelect" required>
                <option value="imagem">Imagem</option>
                <option value="video">Vídeo</option>
                <option value="texto">Texto</option>
            </select>

            <div id="ficheiroContainer">
                <label>Ficheiro (imagem ou vídeo)</label>
                <input type="file" name="ficheiro" id="ficheiroInput" accept="image/*,video/mp4">
            </div>

            <label>Estado</label>
            <select name="estado" required>
                <option value="publicado">Publicado</option>
                <option value="rascunho">Rascunho</option>
            </select>

            <button type="submit" class="btn-save">Publicar</button>
        </form>
    </div>
</main>

<script>
document.getElementById('tipoSelect').addEventListener('change', function() {
    const container = document.getElementById('ficheiroContainer');
    const input = document.getElementById('ficheiroInput');
    
    if (this.value === 'texto') {
        container.style.display = 'none';
        input.required = false;
    } else {
        container.style.display = 'block';
        input.required = true;
    }
});

// Inicializar correto ao carregar a página
document.addEventListener('DOMContentLoaded', function() {
    const tipoSelect = document.getElementById('tipoSelect');
    const container = document.getElementById('ficheiroContainer');
    const input = document.getElementById('ficheiroInput');
    
    if (tipoSelect.value === 'texto') {
        container.style.display = 'none';
        input.required = false;
    } else {
        container.style.display = 'block';
        input.required = true;
    }
});
</script>