<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$titulo = $_POST['titulo'];
$descricao = $_POST['descricao'];
$tipo = $_POST['tipo'];
$estado = $_POST['estado'];
$user_id = $_SESSION['id'];

// Verificar se o user_id existe na tabela utilizador
$stmtUser = $pdo->prepare("SELECT id FROM utilizador WHERE id = ?");
$stmtUser->execute([$user_id]);
if ($stmtUser->rowCount() == 0) {
    die("Erro: utilizador não existe na base de dados.");
}

// ===== LÓGICA PARA POST DE TEXTO =====
if ($tipo === 'texto') {
    // Post de texto não precisa de ficheiro
    $caminhoBD = null;
    
} else {
    // Post de imagem ou vídeo - precisa de ficheiro
    if (!isset($_FILES['ficheiro']) || $_FILES['ficheiro']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['mensagem'] = ['texto' => 'Selecione um ficheiro para imagem ou vídeo.', 'tipo' => 'erro'];
        header("Location: criar_post.php");
        exit;
    }
    
    // Validar tipo de ficheiro
    $extensoes_imagem = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $extensoes_video = ['mp4', 'mov', 'avi', 'mkv'];
    $ext = strtolower(pathinfo($_FILES['ficheiro']['name'], PATHINFO_EXTENSION));
    
    if ($tipo === 'imagem' && !in_array($ext, $extensoes_imagem)) {
        $_SESSION['mensagem'] = ['texto' => 'Formato de imagem não permitido. Use JPG, PNG, GIF ou WEBP.', 'tipo' => 'erro'];
        header("Location: criar_post.php");
        exit;
    }
    
    if ($tipo === 'video' && !in_array($ext, $extensoes_video)) {
        $_SESSION['mensagem'] = ['texto' => 'Formato de vídeo não permitido. Use MP4, MOV, AVI ou MKV.', 'tipo' => 'erro'];
        header("Location: criar_post.php");
        exit;
    }
    
    // Upload do ficheiro
    $ficheiro = $_FILES['ficheiro'];
    $nome = time() . "_" . $ficheiro['name'];
    $caminho = "../assets/posts/" . $nome; // caminho físico
    
    if (move_uploaded_file($ficheiro['tmp_name'], $caminho)) {
        $caminhoBD = $nome; // só o nome vai para a BD
    } else {
        $_SESSION['mensagem'] = ['texto' => 'Erro ao fazer upload do ficheiro.', 'tipo' => 'erro'];
        header("Location: criar_post.php");
        exit;
    }
}

// Inserir post
$stmt = $pdo->prepare("
    INSERT INTO post (titulo, descricao, tipo, caminho, estado, user_id)
    VALUES (?, ?, ?, ?, ?, ?)
");

if ($stmt->execute([$titulo, $descricao, $tipo, $caminhoBD, $estado, $user_id])) {
    $_SESSION['mensagem'] = ['texto' => 'Post criado com sucesso!', 'tipo' => 'sucesso'];
} else {
    $_SESSION['mensagem'] = ['texto' => 'Erro ao criar post.', 'tipo' => 'erro'];
}

header("Location: posts_admin.php");
exit;
?>