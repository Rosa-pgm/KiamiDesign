<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/obra_estado.php';
require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;

if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['id'];
$venda_id = $_POST['venda_id'] ?? null;
$acao = $_POST['acao'] ?? null;

/* ==============================
   VALIDAR VENDA DO CLIENTE
================================ */
if (!$venda_id) {
    $_SESSION['alerta_erro'] = "Venda não identificada.";
    header("Location: obras_reservadas.php");
    exit;
}

// Verificar se a venda pertence ao cliente
$stmt = $pdo->prepare("
    SELECT v.id 
    FROM venda v
    WHERE v.id = ? AND v.user_id = ?
");
$stmt->execute([$venda_id, $user_id]);
$venda = $stmt->fetch();

if (!$venda) {
    $_SESSION['alerta_erro'] = "Venda não encontrada.";
    header("Location: obras_reservadas.php");
    exit;
}

/* ==============================
   FUNÇÃO PARA ENVIAR EMAILS
================================ */
function enviarEmailsCancelamento($pdo, $venda_id, $user_id) {
    
    // Buscar dados do cliente
    $stmt = $pdo->prepare("SELECT nome, email FROM utilizador WHERE id = ?");
    $stmt->execute([$user_id]);
    $cliente = $stmt->fetch();
    
    // Buscar obras da venda
    $stmt = $pdo->prepare("
        SELECT o.titulo, o.preco 
        FROM reserva_cliente r
        JOIN obra o ON r.obra_id = o.id
        WHERE r.venda_id = ?
    ");
    $stmt->execute([$venda_id]);
    $obras = $stmt->fetchAll();
    
    // Calcular total
    $total = 0;
    $listaObras = "";
    foreach ($obras as $obra) {
        $total += $obra['preco'];
        $listaObras .= "<li>{$obra['titulo']} — " . number_format($obra['preco'], 2, ',', '.') . "€</li>";
    }
    
    // ===== EMAIL PARA O CLIENTE =====
    try {
        $mailCliente = new PHPMailer(true);
        $mailCliente->CharSet = 'UTF-8';
        $mailCliente->Encoding = 'base64';
        
        $mailCliente->isSMTP();
        $mailCliente->Host = 'smtp.gmail.com';
        $mailCliente->SMTPAuth = true;
        $mailCliente->Username = 'seuemail@gmail.com';
        $mailCliente->Password = 'sua_password_google';
        $mailCliente->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mailCliente->Port = 465;
        
        $mailCliente->setFrom('seuemail@gmail.com', 'Kiami Design');
        $mailCliente->addAddress($cliente['email'], $cliente['nome']);
        $mailCliente->isHTML(true);
        $mailCliente->Subject = 'Cancelamento de reserva - Kiami Design';
        
        $mailCliente->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2 style='color: #a67b5b;'>Cancelamento de Reserva</h2>
                
                <p>Olá <strong>{$cliente['nome']}</strong>,</p>
                
                <p>A sua reserva da venda <strong>#{$venda_id}</strong> foi cancelada com sucesso.</p>
                
                <h3 style='color: #a67b5b;'>Obras canceladas:</h3>
                <ul>
                    {$listaObras}
                </ul>
                
                <p><strong>Valor total:</strong> " . number_format($total, 2, ',', '.') . "€</p>
                
                <p>Todas as obras voltaram a ficar disponíveis na loja.</p>
                
                <p>Se não foi você que realizou este cancelamento, entre em contacto connosco imediatamente.</p>
                
                <p>— Kiami Design</p>
            </div>
        ";
        
        $mailCliente->send();
        
    } catch (Exception $e) {
        // Log do erro mas não interrompe o processo
        error_log("Erro ao enviar email para cliente: " . $e->getMessage());
    }
    
    // ===== EMAIL PARA O ADMIN =====
    try {
        $mailAdmin = new PHPMailer(true);
        $mailAdmin->CharSet = 'UTF-8';
        $mailAdmin->Encoding = 'base64';
        
        $mailAdmin->isSMTP();
        $mailAdmin->Host = 'smtp.gmail.com';
        $mailAdmin->SMTPAuth = true;
        $mailAdmin->Username = 'seuemail@gmail.com';
        $mailAdmin->Password = 'sua_password_google';
        $mailAdmin->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mailAdmin->Port = 465;
        
        $mailAdmin->setFrom('seuemail@gmail.com', 'Kiami Design');
        $mailAdmin->addAddress('seuemail@gmail.com', 'Admin Kiami');
        $mailAdmin->isHTML(true);
        $mailAdmin->Subject = 'Cancelamento de reserva - Notificação Admin';
        
        $mailAdmin->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2 style='color: #a67b5b;'>Cancelamento de Reserva</h2>
                
                <p><strong>Cliente:</strong> {$cliente['nome']} ({$cliente['email']})</p>
                <p><strong>Venda:</strong> #{$venda_id}</p>
                
                <h3 style='color: #a67b5b;'>Obras canceladas:</h3>
                <ul>
                    {$listaObras}
                </ul>
                
                <p><strong>Valor total:</strong> " . number_format($total, 2, ',', '.') . "€</p>
                
                <p>As obras foram libertadas e voltaram a ficar disponíveis na loja.</p>
            </div>
        ";
        
        $mailAdmin->send();
        
    } catch (Exception $e) {
        error_log("Erro ao enviar email para admin: " . $e->getMessage());
    }
}

/* ==============================
   CANCELAR RESERVA (VENDA INTEIRA)
================================ */
if ($acao === 'cancelar' || $acao === 'cancelar_venda') {

    try {
        $pdo->beginTransaction();

        // 1. Buscar TODAS as obras desta venda
        $stmt = $pdo->prepare("SELECT obra_id FROM reserva_cliente WHERE venda_id = ? AND user_id = ?");
        $stmt->execute([$venda_id, $user_id]);
        $obras = $stmt->fetchAll();

        if (empty($obras)) {
            throw new Exception("Nenhuma obra encontrada para esta venda.");
        }

        // 2. Deixar TODAS as obras disponíveis novamente
        foreach ($obras as $obra) {
            atualizarEstadoObra($pdo, $obra['obra_id'], 'Disponível');
        }

        // 3. Remover TODAS as reservas desta venda
        $stmt = $pdo->prepare("DELETE FROM reserva_cliente WHERE venda_id = ? AND user_id = ?");
        $stmt->execute([$venda_id, $user_id]);

        // 4. Atualizar estado da venda para "Cancelada"
        $stmt = $pdo->prepare("UPDATE venda SET estado = 'Cancelada' WHERE id = ?");
        $stmt->execute([$venda_id]);

        // 5. Buscar pagamento associado à venda
        $stmt = $pdo->prepare("SELECT id FROM pagamento WHERE venda_id = ?");
        $stmt->execute([$venda_id]);
        $pagamento = $stmt->fetch();

        if ($pagamento) {
            // Atualizar estado do pagamento para "Cancelado"
            $stmt = $pdo->prepare("UPDATE pagamento SET estado = 'Cancelado' WHERE id = ?");
            $stmt->execute([$pagamento['id']]);
        }

        $pdo->commit();

        // ENVIAR EMAILS DE CONFIRMAÇÃO
        enviarEmailsCancelamento($pdo, $venda_id, $user_id);

        $_SESSION['alerta_sucesso'] = "Venda cancelada com sucesso.";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Erro ao cancelar venda: " . $e->getMessage());
        $_SESSION['alerta_erro'] = "Erro ao cancelar venda.";
    }

    header("Location: obras_reservadas.php");
    exit;
}

/* ==============================
   ALERTAR PINTOR (PARA A VENDA INTEIRA)
================================ */
if ($acao === 'alertar' || $acao === 'alertar_venda') {

    // Buscar todas as obras da venda para a mensagem
    $stmt = $pdo->prepare("
        SELECT o.titulo 
        FROM reserva_cliente r
        JOIN obra o ON r.obra_id = o.id
        WHERE r.venda_id = ?
    ");
    $stmt->execute([$venda_id]);
    $obras = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $listaObras = implode(", ", $obras);

    // Registar alerta na BD (um único alerta para a venda inteira)
    $stmt = $pdo->prepare("
        INSERT INTO alerta_pintor (user_id, venda_id, mensagem)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([
        $user_id,
        $venda_id,
        'Cliente solicita resposta sobre a reserva da venda #' . $venda_id . ' (obras: ' . $listaObras . ')'
    ]);

    $_SESSION['alerta_sucesso'] = "O pintor foi alertado com sucesso.";
    header("Location: obras_reservadas.php");
    exit;
}

/* ==============================
   AÇÃO INVÁLIDA
================================ */
$_SESSION['alerta_erro'] = "Ação inválida. Valor recebido: " . $acao;
header("Location: obras_reservadas.php");
exit;
?>