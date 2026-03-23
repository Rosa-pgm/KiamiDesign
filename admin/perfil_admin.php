<?php
$titulo = "Perfil";
require 'includes/init_admin.php';
include 'includes/admin_header.php';

// Buscar dados do admin logado
$admin_id = $_SESSION['id'];

$stmt = $pdo->prepare("
    SELECT nome, email, telefone, morada
    FROM utilizador 
    WHERE id = ?
");

$stmt->execute([$admin_id]);
$admin = $stmt->fetch();
?>

<!-- ===== BLOCO PARA MENSAGENS (NOVO) ===== -->
<!-- ===== BLOCO PARA MENSAGENS (CORRIGIDO) ===== -->
<?php if (isset($_SESSION['mensagem'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.mostrarMensagem(
                "<?= addslashes($_SESSION['mensagem']['texto']) ?>", 
                "<?= $_SESSION['mensagem']['tipo'] ?>"
            );
        });
    </script>
    <?php unset($_SESSION['mensagem']); ?>
<?php endif; ?>
<!-- ===== FIM DO BLOCO DE MENSAGENS ===== -->
<!-- ===== FIM DO BLOCO DE MENSAGENS ===== -->

<main class="admin-content">
    <div class="content-header">
        <h1><?= $titulo ?></h1>
        <button class="theme-content-btn" id="theme-toggle">
            <i class="fa-solid fa-sun" id="theme-icon"></i>
            <span id="theme-text">Modo Claro</span>
        </button>
    </div>

    <section class="perfil-container">
        <!-- CARTÃO DO PERFIL -->
        <div class="perfil-card">
            <!-- Foto do perfil - APENAS INICIAIS -->
            <div class="perfil-foto">
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($admin['nome']) ?>&background=041C34&color=fff&size=200" alt="Avatar com iniciais do nome">
            </div>

            <h2><?= htmlspecialchars($admin['nome']) ?></h2>
            <p class="perfil-email"><?= htmlspecialchars($admin['email']) ?></p>

            <div class="perfil-opcoes">
                <button class="perfil-btn" data-modal="modal-nome">
                    <i class="fa fa-user"></i> Editar Nome
                </button>
                <button class="perfil-btn" data-modal="modal-email">
                    <i class="fa fa-envelope"></i> Editar Email
                </button>
                <button class="perfil-btn" data-modal="modal-telefone">
                    <i class="fa fa-phone"></i> Editar Telefone
                </button>
                <button class="perfil-btn" data-modal="modal-password">
                    <i class="fa fa-lock"></i> Alterar Password
                </button>
                <button class="perfil-btn" data-modal="modal-morada">
                    <i class="fa fa-location-dot"></i> Editar Morada
                </button>
                <button class="perfil-btn logout-btn" data-modal="modal-delete-account">                   
                <i class="fa fa-user-slash"></i> Eliminar Conta
                </button>
            </div>
        </div>
    </section>
</main>
<!-- ===================== MODAIS ===================== -->

<!-- Editar Nome -->
<div class="modal" id="modal-nome">
    <div class="modal-content">
        <h3>Editar Nome</h3>
        <form method="POST" action="perfil_update.php">
            <input type="hidden" name="campo" value="nome">
            <input type="text" name="valor" value="<?= htmlspecialchars($admin['nome']) ?>" required>
            <button type="submit" class="modal-save">Guardar</button>
            <button type="button" class="modal-close">Cancelar</button>
        </form>
    </div>
</div>

<!-- Editar Email -->
<div class="modal" id="modal-email">
    <div class="modal-content">
        <h3>Editar Email</h3>
        <form method="POST" action="perfil_update.php">
            <input type="hidden" name="campo" value="email">
            <input type="email" name="valor" value="<?= htmlspecialchars($admin['email']) ?>" required>
            <button type="submit" class="modal-save">Guardar</button>
            <button type="button" class="modal-close">Cancelar</button>
        </form>
    </div>
</div>

<!-- Editar Telefone (CORRIGIDO) -->
<div class="modal" id="modal-telefone">
    <div class="modal-content">
        <h3>Editar Telefone</h3>
        <form method="POST" action="perfil_update.php" id="form-telefone">
            <input type="hidden" name="campo" value="telefone">
            <input type="tel" 
                   name="valor" 
                   id="telefone_modal" 
                   value="<?= htmlspecialchars($admin['telefone'] ?? '') ?>"
                   class="telefone-input">
            <button type="submit" class="modal-save">Guardar</button>
            <button type="button" class="modal-close">Cancelar</button>
        </form>
    </div>
</div>

<!-- Editar Morada -->
<div class="modal" id="modal-morada">
    <div class="modal-content">
        <h3>Editar Morada</h3>
        <p>(Rua,Código Postal, Cidade e País)</p>
        <form method="POST" action="perfil_update.php">
            <input type="hidden" name="campo" value="morada">
            <input type="text" name="valor" value="<?= htmlspecialchars($admin['morada'] ?? '') ?>">
            <br>
            <button type="submit" class="modal-save">Guardar</button>
            <button type="button" class="modal-close">Cancelar</button>
        </form>
    </div>
</div>

<!-- Alterar Password COM VALIDAÇÃO -->
<div class="modal" id="modal-password">
    <div class="modal-content">
        <h3>Alterar Password</h3>
        <form method="POST" action="perfil_update.php" id="form-password">
            <input type="hidden" name="campo" value="password">
            
            <div class="password-wrapper">
                <input type="password" name="password_nova" id="nova_password" placeholder="Nova password" required>
                <button type="button" class="password-toggle" id="toggleNovaPassword">
                    <i class="fa-solid fa-eye" id="novaEyeIcon"></i>
                </button>
            </div>
             <!-- Requisitos da password -->
            <ul id="passwordRequirementsModal" style="list-style: none; padding: 0; margin: 10px 0; font-size: 13px;">
                <li id="lengthModal" class="invalid">✗ Mínimo 8 caracteres</li>
                <li id="uppercaseModal" class="invalid">✗ Pelo menos uma letra maiúscula</li>
                <li id="numberModal" class="invalid">✗ Pelo menos um número</li>
            </ul>
            
            <div class="password-wrapper">
                <input type="password" name="password_confirmacao" id="confirm_password_modal" placeholder="Confirmar nova password" required>
                <button type="button" class="password-toggle" id="toggleConfirmPasswordModal">
                    <i class="fa-solid fa-eye" id="confirmEyeIconModal"></i>
                </button>
            </div>
            
           
            <!-- Mensagem de confirmação -->
            <div id="passwordMatchModal" style="color: red; font-size: 13px; margin-bottom: 10px; display: none;">
                As passwords não coincidem
            </div>
            
            <button type="submit" class="modal-save" id="btn-submit-password">Guardar</button>
            <button type="button" class="modal-close">Cancelar</button>
        </form>
    </div>
</div>
            <!-- eliminar conta -->

<div class="modal" id="modal-delete-account">
    <div class="modal-content">
        <h3>Tem a certeza que deseja eliminar a sua conta?</h3> 
        <p style="margin-bottom:10px;">Por segurança, por favor insira a sua password.</p>

        <form method="POST" action="eliminar_conta_admin.php">
            <div class="password-wrapper">
                <input type="password" name="password" id="delete_password" placeholder="Digite a password" required class="auth-input">
                <button type="button" class="password-toggle" id="toggleDeletePassword">
                    <i class="fa-solid fa-eye" id="deleteEyeIcon"></i>
                </button>
            </div>

            <button class="modal-save" type="submit">Sim, eliminar conta</button>
            <button type="button" class="modal-close">Cancelar</button>
        </form>
    </div>
</div>
