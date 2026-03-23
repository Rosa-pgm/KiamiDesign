<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

require_once '../includes/db.php';

// Processar o upload da obra
$titulo = $_POST['titulo'] ?? '';
$descricao = $_POST['descricao'] ?? '';
$estado_id = intval($_POST['estado_id'] ?? 0);
$preco = !empty($_POST['preco']) ? floatval($_POST['preco']) : null;
$dimensao = $_POST['dimensao'] ?? '';
$material_id = $_POST['material_id'] ?? '';
$novo_material = $_POST['novo_material'] ?? '';

// Validar campos obrigatórios
if (empty($titulo)) {
    $_SESSION['mensagem'] = ['texto' => 'O título é obrigatório.', 'tipo' => 'erro'];
    header("Location: add_obras.php");
    exit;
}

if (empty($estado_id)) {
    $_SESSION['mensagem'] = ['texto' => 'Selecione um estado para a obra.', 'tipo' => 'erro'];
    header("Location: add_obras.php");
    exit;
}

// Processar material (se for "outro")
if ($material_id === 'outro') {
    if (empty($novo_material)) {
        $_SESSION['mensagem'] = ['texto' => 'Digite o nome do novo material.', 'tipo' => 'erro'];
        header("Location: add_obras.php");
        exit;
    }
    
    // Inserir novo material
    $stmt = $pdo->prepare("INSERT INTO material (nome) VALUES (?)");
    $stmt->execute([$novo_material]);
    $material_id = $pdo->lastInsertId();
} elseif (empty($material_id)) {
    $_SESSION['mensagem'] = ['texto' => 'Selecione um material.', 'tipo' => 'erro'];
    header("Location: add_obras.php");
    exit;
}

// Processar upload da imagem
if (!isset($_FILES['imagem']) || $_FILES['imagem']['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['mensagem'] = ['texto' => 'Selecione uma imagem válida.', 'tipo' => 'erro'];
    header("Location: add_obras.php");
    exit;
}

// Validar tipo de imagem
$extensoes_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$ext = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));

if (!in_array($ext, $extensoes_permitidas)) {
    $_SESSION['mensagem'] = ['texto' => 'Formato de imagem não permitido. Use JPG, PNG, GIF ou WEBP.', 'tipo' => 'erro'];
    header("Location: add_obras.php");
    exit;
}

// Gerar nome único para a imagem
$nome_imagem = uniqid() . '.' . $ext;
$caminho = '../assets/img/obras/' . $nome_imagem;

// Fazer upload
if (move_uploaded_file($_FILES['imagem']['tmp_name'], $caminho)) {
    try {
        // Inserir obra no banco
        $stmt = $pdo->prepare("
            INSERT INTO obra (titulo, descricao, estado_id, preco, dimensao, material_id, imagem) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        if ($stmt->execute([$titulo, $descricao, $estado_id, $preco, $dimensao, $material_id, $nome_imagem])) {
            $_SESSION['mensagem'] = ['texto' => 'Obra adicionada com sucesso!', 'tipo' => 'sucesso'];
            header("Location: obras.php");
            exit;
        } else {
            // Se falhar, apagar a imagem que foi enviada
            unlink($caminho);
            $_SESSION['mensagem'] = ['texto' => 'Erro ao salvar obra no banco de dados.', 'tipo' => 'erro'];
        }
    } catch (PDOException $e) {
        // Se falhar, apagar a imagem que foi enviada
        if (file_exists($caminho)) {
            unlink($caminho);
        }
        $_SESSION['mensagem'] = ['texto' => 'Erro de banco de dados: ' . $e->getMessage(), 'tipo' => 'erro'];
    }
} else {
    $_SESSION['mensagem'] = ['texto' => 'Erro ao fazer upload da imagem. Verifique as permissões da pasta.', 'tipo' => 'erro'];
}

header("Location: add_obras.php");
exit;
?>