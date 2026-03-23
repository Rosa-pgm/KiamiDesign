<?php
require 'includes/init_admin.php';

$id = $_POST['id'];
$nome = $_POST['nome'];
$bio = $_POST['bio'];
$instagram = $_POST['instagram'];

// Upload da imagem (se enviada)
if (!empty($_FILES['imagem']['name'])) {

    $imagem = time() . "_" . basename($_FILES['imagem']['name']);
    $destino = "../assets/img/sobre_artista/" . $imagem;

    if (move_uploaded_file($_FILES['imagem']['tmp_name'], $destino)) {

        $stmt = $pdo->prepare("
            UPDATE sobre_mim 
            SET imagem = ?, nome = ?, bio = ?, instagram = ?
            WHERE id = ?
        ");
        $stmt->execute([$imagem, $nome, $bio, $instagram, $id]);

    }

} else {

    $stmt = $pdo->prepare("
        UPDATE sobre_mim 
        SET nome = ?, bio = ?, instagram = ?
        WHERE id = ?
    ");
    $stmt->execute([$nome, $bio, $instagram, $id]);
}

       $_SESSION['mensagem'] = ['texto' => 'Sobre mim atualizado com sucesso!', 'tipo' => 'sucesso'];
header("Location: sobre_mim.php");
exit;
