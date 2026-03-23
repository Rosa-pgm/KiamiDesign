<?php
session_start();

require_once 'config.php';
require_once 'includes/db.php';
include 'includes/obra_estado.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$titulo = "Pagamento confirmado";
include 'includes/header.php';

$session_id = $_GET['session_id'] ?? null;
$user_id = $_SESSION['id'] ?? null;
$dados = $_SESSION['temp_compra'] ?? null;

if (!$session_id || !$user_id || !$dados) {
    echo "<p style='text-align:center;color:red;'>Pagamento inválido.</p>";
    include 'includes/footer.php';
    exit;
}

$mensagem = "Erro ao confirmar pagamento.";

try {

    $session = \Stripe\Checkout\Session::retrieve($session_id);

    if ($session->payment_status === 'paid') {

        /* ===============================
           1. Criar venda
        =============================== */
        $stmt = $pdo->prepare("INSERT INTO venda (user_id) VALUES (?)");
        $stmt->execute([$user_id]);
        $venda_id = $pdo->lastInsertId();


        /* ===============================
           2. Buscar cliente
        =============================== */
        $stmt = $pdo->prepare("SELECT nome, email FROM utilizador WHERE id = ?");
        $stmt->execute([$user_id]);
        $cliente = $stmt->fetch();

        $cliente_nome  = $cliente['nome'];
        $cliente_email = $cliente['email'];
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

        /* ===============================
           3. Inserir itens
        =============================== */
        $stmtItem = $pdo->prepare("
            INSERT INTO venda_item (venda_id, obra_id, preco)
            VALUES (?, ?, ?)
        ");

        $valorTotal = 0;
        $lista_obras = "";

        foreach ($dados['carrinho'] as $item) {

            $stmtItem->execute([
                $venda_id,
                $item['id'],
                $item['preco']
            ]);

            $valorTotal += $item['preco'];

            $lista_obras .= "<li>{$item['titulo']} — {$item['preco']}€</li>";

            atualizarEstadoObra($pdo, $item['id'], 'Vendida');
        }


        /* ===============================
           4. Criar pagamento
        =============================== */
        $stmt = $pdo->prepare("
            INSERT INTO pagamento (venda_id, metodo_pagamento, estado, valor)
            VALUES (?, ?, 'Concluído', ?)
        ");

        $stmt->execute([
            $venda_id,
            $dados['metodo'],
            $valorTotal
        ]);


        /* ===============================
           5. Enviar EMAILS
        =============================== */

        try {

            /* ========= EMAIL ADMIN ========= */

            $mail = new PHPMailer(true);

            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'seuemail@gmail.com';
            $mail->Password = 'sua_password_google';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('seuemail@gmail.com', 'Kiami Design');

            $mail->addAddress('seuemail@gmail.com');

            $mail->CharSet = 'UTF-8';

            $mail->isHTML(true);
            $mail->Subject = "Nova compra no site - Venda #$venda_id";

            $mail->Body = "
                <h2>Nova compra realizada</h2>

                <p><strong>Cliente:</strong> $cliente_nome ($cliente_email)</p>
                <p><strong>Venda:</strong> #$venda_id</p>
                <p><strong>Método:</strong> {$dados['metodo']}</p>

                <h3>Obras compradas:</h3>
                <ul>$lista_obras</ul>

                <p><strong>Total:</strong> {$valorTotal}€</p>
            ";

            $mail->send();


            /* ========= EMAIL CLIENTE ========= */

            $mail = new PHPMailer(true);

            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'seuemail@gmail.com';
            $mail->Password = 'sua_password_google';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('seuemail@gmail.com', 'Kiami Design');

            $mail->addAddress($cliente_email, $cliente_nome);

            $mail->CharSet = 'UTF-8';

            $mail->isHTML(true);
            $mail->Subject = "Confirmação da sua compra - Kiami Design";

            $mail->Body = "
                <h2>Obrigado pela sua compra!</h2>

                <p>Olá $cliente_nome,</p>

                <p>A sua compra foi confirmada com sucesso.</p>

                <p><strong>Compra:</strong> #$venda_id</p>

                <h3>Obras adquiridas:</h3>
                <ul>$lista_obras</ul>

                <p><strong>Total pago:</strong> {$valorTotal}€</p>

                <p>Entraremos em contacto relativamente ao envio.</p>

                <p>Obrigado por apoiar a arte.</p>
            ";

            $mail->send();

        } catch (Exception $e) {
            // não trava a compra se o email falhar
        }


        /* ===============================
           6. Limpar sessão
        =============================== */

        unset($_SESSION['carrinho'], $_SESSION['temp_compra']);

        $mensagem = "Pagamento confirmado! A sua obra foi adquirida com sucesso.";

    } else {

        $mensagem = "O pagamento ainda não foi confirmado.";

    }

} catch (Exception $e) {

    $mensagem = "Erro ao conectar à Stripe: " . $e->getMessage();

}
?>

<main class="checkout sucesso">

<div class="checkout-titulo linha-decorativa">
<h1>Pagamento Bem Sucedido</h1>
</div>

<div class="checkout-box">
<p><?= htmlspecialchars($mensagem) ?></p>

<a href="cliente/encomendas.php" class="btn-finalizar">
Ver encomendas
</a>

</div>

</main>

<?php include 'includes/footer.php'; ?>