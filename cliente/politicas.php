<?php
session_start();
$titulo = "Funcionamento do Site";
include 'includes/header_cliente.php';
?>

<main class="cliente-content">
   <div class="politicas-container"> 
    <div class="politicas-titulo">
        <h1>Como Funciona o Kiami Design?</h1>
    </div>

    <div class="politicas-slideshow">
        <div class="politicas-track">
            
            <!-- Card 1: Criar Conta e Funcionalidades -->
            <div class="politicas-card">
                <div class="politicas-card-header">
                    <span class="politicas-card-numero">1</span>
                    <h2>Criar Conta</h2>
                </div>
                <div class="politicas-card-content">
                    <p>Ao criar uma conta no Kiami Design, você tem acesso a:</p>
                    <ul>
                        <li><strong>Curtir obras</strong> e adicioná-las aos favoritos</li>
                        <li><strong>Reservar obras</strong> disponíveis na loja</li>
                        <li><strong>Comprar obras já feitas</strong> diretamente da loja</li>
                        <li><strong>Encomendar obras personalizadas</strong> criadas exclusivamente para si</li>
                        <li><strong>Perfil próprio</strong> com todas as suas informações e histórico</li>
                    </ul>
                    <div class="info">
                        <i class="fa-solid fa-circle-check"></i> 
                        <span>O seu perfil é a central de todas as suas atividades na plataforma.</span>
                    </div>
                </div>
            </div>

            <!-- Card 2: Compras na Loja -->
            <div class="politicas-card">
                <div class="politicas-card-header">
                    <span class="politicas-card-numero">2</span>
                    <h2>Compras na Loja</h2>
                </div>
                <div class="politicas-card-content">
                    <p>Ao fazer uma compra na loja, pode escolher entre 3 métodos:</p>
                    <ul>
                        <li><strong>MBWay</strong> - Pagamento automático e rápido</li>
                        <li><strong>Cartão Bancário</strong> - Pagamento automático via Stripe</li>
                        <li><strong>Coordenar com Pintor</strong> - A obra fica reservada por 5 dias</li>
                    </ul>
                    <div class="aviso">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <span><strong>Atenção:</strong> O preço das obras da loja não é negociável.</span>
                    </div>
                    <p>Se escolher "Coordenar com Pintor" e não houver contacto em 5 dias, a obra poderá ser libertada.</p>
                </div>
            </div>

            <!-- Card 3: Perfil e Ações -->
            <div class="politicas-card">
                <div class="politicas-card-header">
                    <span class="politicas-card-numero">3</span>
                    <h2>No Seu Perfil</h2>
                </div>
                <div class="politicas-card-content">
                    <p>No seu perfil, pode gerir todas as suas atividades:</p>
                    <ul>
                        <li> <i class="fa-solid fa-bell" style="color: #f39c12; margin-left: 8px;"></i> <strong>Alertar o pintor</strong> sobre encomendas personalizadas</li>
                        <li><i class="fa-solid fa-rotate-left" style="color: #27ae60; margin-left: 8px;"></i> <strong>Pedir reembolso</strong> se a obra ainda não tiver sido enviada</li>
                        <li><i class="fa-solid fa-truck" style="color: #3498db; margin-left: 8px;"></i><strong> Acompanhar o estado</strong> das suas encomendas</li>
                    </ul>
                    <div class="destaque">
                        <i class="fa-solid fa-circle-check"></i>
                        <span>Reembolsos apenas para pagamentos realizados com envio não iniciado.</span>
                    </div>
                </div>
            </div>

            <!-- Card 4: Avisos Importantes -->
            <div class="politicas-card">
                <div class="politicas-card-header">
                    <span class="politicas-card-numero">4</span>
                    <h2>Avisos Importantes</h2>
                </div>
                <div class="politicas-card-content">
                    <div class="aviso">
                        <i class="fa-solid fa-ban"></i>
                        <span><strong>Não aceitamos devoluções</strong> de encomendas já enviadas nem encomendas personalizadas.</span>
                    </div>
                    
                    <div class="info">
                        <i class="fa-solid fa-paintbrush"></i>
                        <span><strong>Encomendas Personalizadas:</strong> Pagamento acertado por email com o pintor.</span>
                    </div>
                    
                    <div class="destaque">
                        <i class="fa-solid fa-user-xmark"></i>
                        <span><strong>Eliminar Conta:</strong> Disponível perfil em "Eliminar Conta".</span>
                    </div>
                </div>
            </div>

            <!-- Card 5: Contacto e Sugestões -->
            <div class="politicas-card">
                <div class="politicas-card-header">
                    <span class="politicas-card-numero">5</span>
                    <h2>Contacto e Sugestões</h2>
                </div>
                <div class="politicas-card-content">
                    <p>A sua opinião é muito importante para nós!</p>
                    
                    <div class="info">
                        <i class="fa-solid fa-message"></i>
                        <span><strong>Aceitamos ideias e opiniões</strong> através do menu "Mensagens".</span>
                    </div>
                    
                    <p style="margin-top: 25px;">Sugestões de melhoria, ideias para novas obras ou apenas um comentário, estamos sempre disponíveis.</p>
                    
                    <div style="text-align: center; margin-top: 30px;">
                        <a href="../mensagem.php" class="btn-mensagem">
                            <i class="fa-solid fa-paper-plane"></i> Enviar Mensagem
                        </a>
                    </div>
                </div>
            </div>

        </div>

        <!-- Navegação -->
        <button class="politicas-nav politicas-prev" id="politicasPrev">
            <i class="fa-solid fa-chevron-left"></i>
        </button>
        <button class="politicas-nav politicas-next" id="politicasNext">
            <i class="fa-solid fa-chevron-right"></i>
        </button>

        <!-- Dots -->
        <div class="politicas-dots" id="politicasDots">
            <span class="politicas-dot active"></span>
            <span class="politicas-dot"></span>
            <span class="politicas-dot"></span>
            <span class="politicas-dot"></span>
            <span class="politicas-dot"></span>
        </div>
    </div>
</div>
</main>

<?php include 'includes/footer_cliente.php'; ?>