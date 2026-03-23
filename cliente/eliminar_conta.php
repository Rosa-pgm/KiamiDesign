<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['id'];
$password = $_POST['password'] ?? '';

// Buscar password real
$stmt = $pdo->prepare("SELECT password FROM utilizador WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password'])) {
    $_SESSION['alerta_erro'] = "Password incorreta.";
    header("Location: perfil_cliente.php");
    exit;
}

try {
    // Desativar verificação de chaves estrangeiras temporariamente
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    // 1. Buscar todas as vendas do utilizador
    $stmt = $pdo->prepare("SELECT id FROM venda WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $vendas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($vendas as $venda_id) {
        // 2. Apagar envios associados aos pagamentos das vendas
        $stmt = $pdo->prepare("
            DELETE FROM envio 
            WHERE pagamento_id IN (SELECT id FROM pagamento WHERE venda_id = ?)
        ");
        $stmt->execute([$venda_id]);
        
        // 3. Apagar pagamentos das vendas
        $stmt = $pdo->prepare("DELETE FROM pagamento WHERE venda_id = ?");
        $stmt->execute([$venda_id]);
        
        // 4. Apagar itens da venda
        $stmt = $pdo->prepare("DELETE FROM venda_item WHERE venda_id = ?");
        $stmt->execute([$venda_id]);
        
        // 5. Apagar reservas associadas
        $stmt = $pdo->prepare("DELETE FROM reserva_cliente WHERE venda_id = ?");
        $stmt->execute([$venda_id]);
    }
    
    // 6. Apagar as vendas
    $stmt = $pdo->prepare("DELETE FROM venda WHERE user_id = ?");
    $stmt->execute([$user_id]);
    
    // 7. Apagar encomendas personalizadas
    $stmt = $pdo->prepare("SELECT id FROM encomenda_personalizada WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $encomendas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($encomendas as $encomenda_id) {
        // 8. Apagar pagamentos das encomendas personalizadas
        $stmt = $pdo->prepare("DELETE FROM pagamento WHERE encomenda_id = ?");
        $stmt->execute([$encomenda_id]);
    }
    
    // 9. Apagar as encomendas personalizadas
    $stmt = $pdo->prepare("DELETE FROM encomenda_personalizada WHERE user_id = ?");
    $stmt->execute([$user_id]);
    
    // 10. Apagar favoritos
    $stmt = $pdo->prepare("DELETE FROM favorito WHERE user_id = ?");
    $stmt->execute([$user_id]);
    
    // 11. Apagar alertas
    $stmt = $pdo->prepare("DELETE FROM alerta_pintor WHERE user_id = ?");
    $stmt->execute([$user_id]);
    
    // 12. Finalmente, eliminar o utilizador
    $delete = $pdo->prepare("DELETE FROM utilizador WHERE id = ?");
    $delete->execute([$user_id]);
    
    // Reativar verificação de chaves estrangeiras
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    session_destroy();
    // Redirecionar com parâmetro que NÃO fica infinito
    header("Location: ../login.php?eliminada=1");
    exit;

} catch (PDOException $e) {
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    error_log("Erro ao eliminar conta: " . $e->getMessage());
    $_SESSION['alerta_erro'] = "Erro ao eliminar conta: " . $e->getMessage();
    header("Location: perfil_cliente.php");
    exit;
}
?>