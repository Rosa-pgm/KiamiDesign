<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'includes/db.php';

use PHPMailer\PHPMailer\PHPMailer;
require 'vendor/autoload.php';

$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST['email']);

    if ($email) {

        $stmt = $pdo->prepare("SELECT id, nome FROM utilizador WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {

            $token  = bin2hex(random_bytes(32));
            $expira = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $pdo->prepare("
                INSERT INTO password_reset (user_id, token, expires_at)
                VALUES (?, ?, ?)
            ")->execute([$user['id'], $token, $expira]);

            $link = "http://localhost/PAP-14-KiamiDesign/projeto/reset_password.php?token=$token";

            try {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'seuemail@gmail.com';
                $mail->Password = 'sua_password_google';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port = 465;

                $mail->setFrom('seuemail@gmail.com', 'Kiami Design');
                $mail->addAddress($email, $user['nome']);
                $mail->CharSet = 'UTF-8';
                $mail->isHTML(false);
                $mail->Subject = 'Recuperação de password';

                $mail->Body =
                "Olá {$user['nome']},

                Foi solicitado um pedido de recuperação de password.

                Utilize o link abaixo para redefinir a sua password:
                $link

                Este link expira em 1 hora.

                Se não fez este pedido, ignore este email.

                — Kiami Design";

                $mail->send();
            } catch (Exception $e) {
                // falha silenciosa
            }
        }

        $msg = "Instruções enviadas para recuperar a password.";
    }
}
?>

<?php
$titulo = "Recuperar Password";
include 'includes/header.php';
?>
<main>
<div class="auth-wrapper">
    <form class="auth-card" method="POST">

        <h2>Recuperar password</h2>

        <?php if ($msg): ?>
            <div class="auth-error" style="background:#e6ffe6;color:#065f08;">
                <?= htmlspecialchars($msg) ?>
            </div>
        <?php endif; ?>

        <input
            type="email"
            name="email"
            placeholder="Email"
            class="auth-input"
            required
        >

        <button class="btn" type="submit">
            Enviar instruções
        </button>

        <p>
            <a href="login.php">Voltar ao login</a>
        </p>

    </form>
</div>
        </main>
<?php include 'includes/footer.php'; ?>
