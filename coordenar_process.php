<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/obra_estado.php';

// ===============================
// 1. Validar sessão
// ===============================
if (!isset($_SESSION['id']) || !isset($_SESSION['temp_coordenar'])) {
    header("Location: checkout.php");
    exit;
}

$user_id  = $_SESSION['id'];
$dados    = $_SESSION['temp_coordenar'];
$carrinho = $dados['carrinho'];
$total    = $dados['total'];
$metodo   = $dados['metodo']; 
$mensagem = $_POST['mensagem'];
// ===============================
// 2ver se obra já foi vendida
// ===============================
foreach ($dados['carrinho'] as $item) {

    $stmt = $pdo->prepare("
        SELECT eo.nome
        FROM obra o
        JOIN estado_obra eo ON o.estado_id = eo.id
        WHERE o.id = ?
    ");

    $stmt->execute([$item['id']]);
    $estado = $stmt->fetchColumn();

    if ($estado !== 'Disponível') {

        echo "<p style='color:red;text-align:center'>
        A obra '{$item['titulo']}' já foi vendida ou reservada.
        </p>";

        exit;
    }
}

// ===============================
// 2. Criar venda
// ===============================
$stmt = $pdo->prepare("INSERT INTO venda (user_id) VALUES (?)");
$stmt->execute([$user_id]);
$venda_id = $pdo->lastInsertId();

// ===============================
// 3. Inserir itens + reservar obras
// ===============================
$stmtItem = $pdo->prepare("
    INSERT INTO venda_item (venda_id, obra_id, preco)
    VALUES (?, ?, ?)
");

$stmtReserva = $pdo->prepare("
    INSERT INTO reserva_cliente (user_id, obra_id, venda_id, data_reserva)
    VALUES (?, ?, ?, NOW())
");

foreach ($carrinho as $item) {

    // inserir item
    $stmtItem->execute([$venda_id, $item['id'], $item['preco']]);

    // reservar obra
    $pdo->prepare("
        UPDATE obra 
        SET estado_id = (SELECT id FROM estado_obra WHERE nome = 'Reservada')
        WHERE id = ?
    ")->execute([$item['id']]);

    // criar reserva_cliente
    $stmtReserva->execute([$user_id, $item['id'], $venda_id]);
}

// ===============================
// 4. Criar pagamento pendente
// ===============================
$stmt = $pdo->prepare("
    INSERT INTO pagamento (venda_id, metodo_pagamento, estado, valor)
    VALUES (?, ?, 'Pendente', ?)
");
$stmt->execute([$venda_id, $metodo, $total]);

// ===============================
// 5. Buscar email do cliente
// ===============================
$stmt = $pdo->prepare("SELECT nome, email FROM utilizador WHERE id = ?");
$stmt->execute([$user_id]);
$cliente = $stmt->fetch();

$cliente_nome  = $cliente['nome'];
$cliente_email = $cliente['email'];

// ===============================
// 6. Enviar emails (PHPMailer)
// ===============================

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

/* ===============================
   LISTA DE OBRAS
================================ */

$lista_obras = "";

foreach ($carrinho as $item) {
    $lista_obras .= "<li>{$item['titulo']} — {$item['preco']}€</li>";
}


/* ===============================
   EMAIL PARA O ADMIN / PINTOR
================================ */

try {

    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'seuemail@gmail.com';
    $mail->Password = 'sua_password_google';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('seuemail@gmail.com', 'Kiami Design');

    // ADMIN / PINTOR
    $mail->addAddress('seuemail@gmail.com');

    $mail->CharSet = 'UTF-8';
    $mail->isHTML(true);

    $mail->Subject = "Novo pedido de coordenação - Venda #$venda_id";

    $mail->Body = "
        <h2>Novo pedido de coordenação</h2>

        <p><strong>Cliente:</strong> $cliente_nome ($cliente_email)</p>
        <p><strong>Venda:</strong> #$venda_id</p>

        <p><strong>Mensagem do cliente:</strong></p>
        <p>$mensagem</p>

        <h3>Obras incluídas:</h3>
        <ul>$lista_obras</ul>

        <p><strong>Total:</strong> {$total}€</p>
    ";

    $mail->send();

} catch (Exception $e) {
    // não trava o processo
}


/* ===============================
   EMAIL PARA O CLIENTE
================================ */

try {

    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'seuemail@gmail.com';
    $mail->Password = 'sua_password_google';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('seuemail@gmail.com', 'Kiami Design');

    // CLIENTE
    $mail->addAddress($cliente_email, $cliente_nome);

    $mail->CharSet = 'UTF-8';
    $mail->isHTML(true);

    $mail->Subject = "Pedido enviado ao artista - Kiami Design";

    $mail->Body = "
        <h2>Pedido enviado com sucesso</h2>

        <p>Olá $cliente_nome,</p>

        <p>O seu pedido de coordenação com o artista foi enviado.</p>

        <p><strong>Número da venda:</strong> #$venda_id</p>

        <p>O artista irá entrar em contacto consigo para definir o pagamento.</p>

        <h3>Obras solicitadas:</h3>
        <ul>$lista_obras</ul>

        <p><strong>Total estimado:</strong> {$total}€</p>

        <p>Obrigado por apoiar a arte.</p>
    ";

    $mail->send();

} catch (Exception $e) {
    // não trava o processo
}

// ===============================
// 7. Limpar sessão e carrinho
// ===============================
unset($_SESSION['temp_coordenar']);
unset($_SESSION['carrinho']);

// ===============================
// 8. Redirecionar para página de sucesso
// ===============================
header("Location: coordenar_sucesso.php?venda=$venda_id");
exit;
