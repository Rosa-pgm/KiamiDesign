<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$id = $_POST['id'];
$titulo = $_POST['titulo'];
$descricao = $_POST['descricao'];
$estado = $_POST['estado'];

// Buscar o post atual
$stmt = $pdo->prepare("SELECT * FROM post WHERE id = ?");
$stmt->execute([$id]);
$post = $stmt->fetch();

if (!$post) {
    $_SESSION['mensagem'] = ['texto' => 'Post não encontrado.', 'tipo' => 'erro'];
    header("Location: posts_admin.php");
    exit;
}

// Caminho atual
$caminhoBD = $post['caminho'];

// Se o tipo for texto, não precisa de ficheiro
if ($post['tipo'] === 'texto') {
    // Post de texto - caminho permanece NULL
    $caminhoBD = null;
}

// Se o admin enviou um novo ficheiro
if (!empty($_FILES['ficheiro']['name'])) {
    $ficheiro = $_FILES['ficheiro'];
    $ext = strtolower(pathinfo($ficheiro['name'], PATHINFO_EXTENSION));
    
    // Validar extensões conforme o tipo
    $extensoes_imagem = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $extensoes_video = ['mp4', 'mov', 'avi', 'mkv'];
    
    if ($post['tipo'] === 'imagem' && !in_array($ext, $extensoes_imagem)) {
        $_SESSION['mensagem'] = ['texto' => 'Formato de imagem não permitido.', 'tipo' => 'erro'];
        header("Location: post_editar.php?id=$id");
        exit;
    }
    
    if ($post['tipo'] === 'video' && !in_array($ext, $extensoes_video)) {
        $_SESSION['mensagem'] = ['texto' => 'Formato de vídeo não permitido.', 'tipo' => 'erro'];
        header("Location: post_editar.php?id=$id");
        exit;
    }
    
    $nome = time() . "_" . $ficheiro['name'];
    $caminho = "../assets/posts/" . $nome;
    
    if (move_uploaded_file($ficheiro['tmp_name'], $caminho)) {
        // Apagar ficheiro antigo se existir
        if ($post['caminho'] && file_exists("../assets/posts/" . $post['caminho'])) {
            unlink("../assets/posts/" . $post['caminho']);
        }
        $caminhoBD = $nome;
    } else {
        $_SESSION['mensagem'] = ['texto' => 'Erro ao fazer upload do ficheiro.', 'tipo' => 'erro'];
        header("Location: post_editar.php?id=$id");
        exit;
    }
}

// Atualizar
$stmt = $pdo->prepare("
    UPDATE post
    SET titulo=?, descricao=?, estado=?, caminho=?
    WHERE id=?
");

if ($stmt->execute([$titulo, $descricao, $estado, $caminhoBD, $id])) {
    $_SESSION['mensagem'] = ['texto' => 'Post atualizado com sucesso!', 'tipo' => 'sucesso'];
} else {
    $_SESSION['mensagem'] = ['texto' => 'Erro ao atualizar post.', 'tipo' => 'erro'];
}

header("Location: posts_admin.php");
exit;
?>