<?php
require 'includes/init_cliente.php';
$titulo = "Meu Perfil";
include 'includes/header_cliente.php';

// Buscar dados do cliente logado
$cliente_id = $_SESSION['id'];

//  REMOVER cidade e pais da query
$stmt = $pdo->prepare("SELECT nome, email, telefone, morada, newsletter 
FROM utilizador WHERE id = ?");

$stmt->execute([$cliente_id]);
$cliente = $stmt->fetch();
?>

<main class="cliente-content">

    <!-- MENSAGENS DE ERRO/SUCESSO -->
    <?php if (isset($_SESSION['alerta_erro'])): ?>
        <div class="auth-error mensagem-temporaria">
            <?= $_SESSION['alerta_erro']; unset($_SESSION['alerta_erro']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['alerta_sucesso'])): ?>
        <div class="auth-success mensagem-temporaria">
            <?= $_SESSION['alerta_sucesso']; unset($_SESSION['alerta_sucesso']); ?>
        </div>
    <?php endif; ?>

    <!-- Mensagens via GET (para outros casos) -->
    <?php if (isset($_GET['erro'])): ?>
        <div class="auth-error mensagem-temporaria">
            <?php
            switch ($_GET['erro']) {
                case 'password':
                case 'confirmacao':
                    echo "As passwords não coincidem.";
                    break;
                case 'vazia':
                    echo "A password não pode estar vazia.";
                    break;
                case 'formato':
                    echo "A password deve ter pelo menos 8 caracteres, conter uma letra maiúscula e um número.";
                    break;
                case 'foto':
                    echo "Erro ao carregar a foto.";
                    break;
                default:
                    echo "Ocorreu um erro inesperado.";
            }
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['sucesso'])): ?>
        <div class="auth-success mensagem-temporaria">
            <?php if ($_GET['sucesso'] === 'password'): ?>
                Password alterada com sucesso.
            <?php else: ?>
                <?= ucfirst($_GET['sucesso']) ?> atualizado(a) com sucesso.
            <?php endif; ?>
        </div>
        
        <script>
            setTimeout(function() {
                window.location.href = 'perfil_cliente.php';
            }, 2000);
        </script>
    <?php endif; ?>


    <section class="perfil-container">
        <div class="perfil-card">
    <!-- Foto do perfil - APENAS INICIAIS, sem link para alterar -->
    <div class="perfil-foto">
        <img src="https://ui-avatars.com/api/?name=<?= urlencode($cliente['nome']) ?>&background=041C34&color=fff&size=200" alt="Avatar com iniciais do nome">
    </div>

    <h2><?= htmlspecialchars($cliente['nome']) ?></h2>
    <p class="perfil-email"><?= htmlspecialchars($cliente['email']) ?></p>

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
        <button class="perfil-btn" data-modal="modal-newsletter">
            <i class="fa fa-envelope-open"></i> Newsletter
        </button>

        <button class="perfil-btn logout-btn" data-modal="modal-delete-account">
            <i class="fa fa-user-slash"></i> Eliminar Conta
        </button>
    </div>
</div>
    </section>
</main>

<!-- MODAIS CORRIGIDOS -->

<!-- Editar Nome -->
<div class="modal" id="modal-nome">
    <div class="modal-content">
        <h3>Editar Nome</h3>
        <form method="POST" action="perfil_update.php">
            <input type="hidden" name="campo" value="nome">
            <input type="text" name="valor" value="<?= htmlspecialchars($cliente['nome']) ?>" required>
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
            <input type="email" name="valor" value="<?= htmlspecialchars($cliente['email']) ?>" required>
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
            <label>Telefone</label>
            <br>
            <input type="tel" 
                   name="valor" 
                   id="telefone_modal" 
                   value="<?= htmlspecialchars($cliente['telefone'] ?? '') ?>"
                   class="telefone-input">
            <button type="submit" class="modal-save">Guardar</button>
            <button type="button" class="modal-close">Cancelar</button>
        </form>
    </div>
</div>

<!-- Alterar Password (CORRIGIDO COM VALIDAÇÃO) -->
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

<!-- Editar Morada (CORRIGIDO - era endereco) -->
<div class="modal" id="modal-morada">
    <div class="modal-content">
        <h3>Editar Morada</h3>
        <form method="POST" action="perfil_update.php">
            <input type="hidden" name="campo" value="morada">
            <input type="text" name="valor" value="<?= htmlspecialchars($cliente['morada'] ?? '') ?>">
            <button type="submit" class="modal-save">Guardar</button>
            <button type="button" class="modal-close">Cancelar</button>
        </form>
    </div>
</div>

<!-- Modal Newsletter -->
<div class="modal" id="modal-newsletter">
    <div class="modal-content">
        <h3>Preferências de Newsletter</h3>
        <form method="POST" action="perfil_update.php">
            <input type="hidden" name="campo" value="newsletter">
            <label class="checkbox-line">
                <input type="checkbox" name="valor" value="1" <?= $cliente['newsletter'] ? 'checked' : '' ?>>
                Quero receber novidades e promoções
            </label>
            <button type="submit" class="modal-save">Guardar</button>
            <button type="button" class="modal-close">Cancelar</button>
        </form>
    </div>
</div>

<!-- Modal de Eliminar Conta - CORRIGIDO -->
<div class="modal" id="modal-delete-account">
    <div class="modal-content">
        <h3>Tem a certeza que deseja eliminar a sua conta?</h3> 
        <p style="margin: 15px 0; color: #666; text-align: center;">
            Por segurança, insira a sua password para confirmar.
        </p>
        <form method="POST" action="eliminar_conta.php">
            <div class="password-wrapper">
<input type="password" name="password" id="delete_password" placeholder="Password" required>
                <button type="button" class="password-toggle" id="toggleDeletePassword">
                    <i class="fa-solid fa-eye" id="deleteEyeIcon"></i>
                </button>
            </div>
            <button class="modal-save" type="submit" style="background: #b91c1c;">
                Sim, eliminar conta
            </button>
            <button type="button" class="modal-close">Cancelar</button>
        </form>
    </div>
</div>

<?php include 'includes/footer_cliente.php'; ?>