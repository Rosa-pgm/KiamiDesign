// ============================================
// FUNÇÕES GLOBAIS (definidas fora do DOMContentLoaded)
// ============================================
// Estas funções precisam ser globais porque são chamadas diretamente pelo HTML
// Ex: onclick="abrirModalRemoverObra()" no botão "Remover"
// ===== DARK MODE - EXECUTAR IMEDIATAMENTE (ANTES DO DOM CARREGAR) =====
(function() {
    // Verificar preferência salva
    const savedTheme = localStorage.getItem('theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    
    // Aplicar tema imediatamente para evitar flash
    if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
        document.documentElement.classList.add('dark-mode');
        document.body.classList.add('dark-mode');
        // NÃO APLICAR FILTRO NO LOGO
    }
})();
/**
 * Mostra mensagens temporárias na interface
 * @param {string} texto - Mensagem a ser exibida
 * @param {string} tipo - 'sucesso' ou 'erro' (define a cor da mensagem)
 */
window.mostrarMensagem = function(texto, tipo) {
    const container = document.getElementById('ajax-mensagem-container');
    if (!container) return;
    
    const mensagem = document.createElement('div');
    mensagem.className = 'ajax-mensagem ' + tipo;
    mensagem.innerHTML = texto;
    
    container.innerHTML = ''; // Limpa mensagens anteriores
    container.appendChild(mensagem);
    
    // Remove a mensagem após 3 segundos
    setTimeout(() => {
        mensagem.style.transition = 'opacity 0.3s ease';
        mensagem.style.opacity = '0';
        setTimeout(() => {
            if (mensagem.parentNode) mensagem.remove();
        }, 300);
    }, 3000);
};

/**
 * Fecha o modal de erro de limite de destaques
 */
window.fecharModalErro = function() {
    const modal = document.getElementById('modal-erro-destaque');
    if (modal) modal.style.display = 'none';
};

/**
 * Abre o modal de confirmação para remover uma obra
 * @param {number} id - ID da obra
 * @param {string} titulo - Título da obra
 */
window.abrirModalRemoverObra = function(id, titulo) {
    const removerId = document.getElementById('remover-obra-id');
    const modalTitulo = document.getElementById('modal-obra-titulo');
    const modal = document.getElementById('modal-remover-obra');
    
    if (removerId) removerId.value = id;
    if (modalTitulo) modalTitulo.innerHTML = 'Tem certeza que deseja remover a obra: <strong>"' + titulo + '"</strong>?';
    if (modal) modal.style.display = 'flex';
};

/**
 * Abre modal de reembolso (versão 1)
 */
window.abrirModal = function(pagamento_id, venda_id) {
    const modalPagamento = document.getElementById('modal_pagamento_id');
    const modalVenda = document.getElementById('modal_venda_id');
    const modal = document.getElementById('modalReembolso');
    
    if (modalPagamento) modalPagamento.value = pagamento_id;
    if (modalVenda) modalVenda.value = venda_id;
    if (modal) modal.style.display = 'flex';
};

/**
 * Fecha modal de reembolso (versão 1)
 */
window.fecharModal = function() {
    const modal = document.getElementById('modalReembolso');
    if (modal) modal.style.display = 'none';
};

/**
 * Abre modal de reembolso (versão 2)
 */
window.abrirModalReembolso = function(pagamentoId, vendaId) {
    const pagamentoInput = document.getElementById('pagamento-id');
    const vendaInput = document.getElementById('venda-id');
    const texto = document.getElementById('texto-reembolso');
    const modal = document.getElementById('modal-reembolso');
    
    if (pagamentoInput) pagamentoInput.value = pagamentoId;
    if (vendaInput) vendaInput.value = vendaId;
if (texto) texto.textContent = "Tem a certeza que deseja reembolsar o pagamento #" + pagamentoId + " e já reembolsou no dashboard da stripe?";
    if (modal) modal.style.display = 'flex';
};

/**
 * Fecha o modal de aviso de destaque
 */
window.fecharModalAvisoDestaque = function() {
    const modal = document.getElementById('modal-aviso-destaque');
    if (modal) modal.style.display = 'none';
};

/**
 * Processa o formulário de remoção de obra via AJAX
 * Esta função é chamada pelo onsubmit do formulário
 */
window.enviarRemocao = function(event) {
    event.preventDefault(); // Impede o envio normal do formulário
    
    const id = document.getElementById('remover-obra-id').value;
    const modal = document.getElementById('modal-remover-obra');
    const modalAviso = document.getElementById('modal-aviso-destaque');
    const card = document.getElementById('obra-' + id);
    
    console.log('A arquivar obra ID:', id);
    
    fetch('obra_delete_ajax.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + id
    })
    .then(response => response.json())
    .then(data => {
        if (modal) modal.style.display = 'none';
        
        // 🔍 Verificar se é erro de destaque
        if (data.mensagem === 'destaque') {
            // Mostrar modal de aviso específico
            if (modalAviso) {
                modalAviso.style.display = 'flex';
            }
            return;
        }
        
        if (data.sucesso && card) {
            // Animação de fade out
            card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            card.style.opacity = '0';
            card.style.transform = 'scale(0.9)';
            
            // Remove o card após a animação
            setTimeout(() => {
                card.remove();
                window.mostrarMensagem('Obra removida com sucesso!', 'sucesso');
            }, 300);
        } else {
            window.mostrarMensagem(data.mensagem || 'Erro ao remover obra', 'erro');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        if (modal) modal.style.display = 'none';
        window.mostrarMensagem('Erro de conexão', 'erro');
    });
    
    return false;
};

// ============================================
// FUNÇÕES DE MANIPULAÇÃO DOS BOTÕES DE DESTAQUE
// ============================================

/**
 * Manipula o clique nos botões de adicionar/remover destaque
 */
function handleDestaqueClick(e) {
    const botao = e.currentTarget;
    const id = botao.dataset.id;
    const acao = botao.dataset.acao;
    const card = document.getElementById('obra-' + id);
    
    // Desabilita o botão durante o processamento
    botao.disabled = true;
    botao.style.opacity = '0.5';
    
    fetch('toggle_destaque_ajax.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + id + '&acao=' + acao
    })
    .then(response => response.json())
    .then(data => {
        if (data.sucesso) {
            window.mostrarMensagem(data.mensagem, 'sucesso');
            
            // Criar novo botão baseado na ação
            const novoBotao = document.createElement('button');
            
            if (acao === 'adicionar') {
                novoBotao.className = 'btn-remover-destaque';
                novoBotao.dataset.acao = 'remover';
                novoBotao.textContent = 'Remover dos Destaques';
            } else {
                novoBotao.className = 'btn-adicionar-destaque';
                novoBotao.dataset.acao = 'adicionar';
                novoBotao.textContent = 'Adicionar aos Destaques';
            }
            
            novoBotao.dataset.id = id;
            
            // Substituir o botão antigo pelo novo
            botao.parentNode.replaceChild(novoBotao, botao);
            
            // Adicionar evento ao novo botão
            novoBotao.addEventListener('click', handleDestaqueClick);
            
        } else {
            // Verificar se é erro de limite de destaques
            if (data.erro === 'limite') {
                const modalMsg = document.getElementById('modal-erro-mensagem');
                const modalErro = document.getElementById('modal-erro-destaque');
                if (modalMsg) modalMsg.innerHTML = data.mensagem;
                if (modalErro) modalErro.style.display = 'flex';
            } else {
                window.mostrarMensagem(data.mensagem || 'Erro ao processar', 'erro');
            }
            // Reabilita o botão original
            botao.disabled = false;
            botao.style.opacity = '1';
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        window.mostrarMensagem('Erro de conexão', 'erro');
        botao.disabled = false;
        botao.style.opacity = '1';
    });
}

/**
 * Configura todos os botões de destaque existentes na página
 */
function setupDestaqueButtons() {
    document.querySelectorAll('.btn-adicionar-destaque, .btn-remover-destaque').forEach(btn => {
        // Remove eventos antigos para evitar duplicação
        btn.removeEventListener('click', handleDestaqueClick);
        btn.addEventListener('click', handleDestaqueClick);
    });
}
// ============================================
// FUNÇÃO PARA REATIVAR OBRAS (AJAX)
// ============================================

/**
 * Configura os botões de reativar obras
 */
function setupReativarButtons() {
    document.querySelectorAll('.btn-reativar-obra').forEach(btn => {
        // Remove eventos antigos para evitar duplicação
        btn.removeEventListener('click', handleReativarClick);
        btn.addEventListener('click', handleReativarClick);
    });
}

/**
 * Manipula o clique no botão de reativar
 */
function handleReativarClick(e) {
    const botao = e.currentTarget;
    const id = botao.dataset.id;
    const titulo = botao.dataset.titulo;
    const card = document.getElementById('obra-removida-' + id);
    
    // Desabilitar botão durante o processamento
    botao.disabled = true;
    botao.textContent = 'Reativando...';
    
    console.log('A reativar obra ID:', id, 'Título:', titulo);
    
    fetch('reativar_obra_ajax.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + id
    })
    .then(response => response.json())
    .then(data => {
        if (data.sucesso) {
            // Mostrar mensagem de sucesso
            window.mostrarMensagem(data.mensagem, 'sucesso');
            
            // Remover o card com animação
            if (card) {
                card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                card.style.opacity = '0';
                card.style.transform = 'scale(0.9)';
                
                setTimeout(() => {
                    card.remove();
                    
                    // Verificar se não há mais cards
                    const container = document.getElementById('obras-removidas-container');
                    if (container && container.children.length === 0) {
                        container.innerHTML = '<p style="text-align:center; padding:40px;">Nenhuma obra removida encontrada.</p>';
                    }
                }, 300);
            }
        } else {
            // Mostrar mensagem de erro
            window.mostrarMensagem(data.mensagem || 'Erro ao reativar obra', 'erro');
            
            // Reabilitar botão
            botao.disabled = false;
            botao.textContent = 'Reativar';
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        window.mostrarMensagem('Erro de conexão', 'erro');
        
        // Reabilitar botão
        botao.disabled = false;
        botao.textContent = 'Reativar';
    });
}
// ===== FUNÇÃO PARA POSTS SEM REFRESH =====
window.confirmarAcaoPost = function(event, id, acao, elemento) {
    // Prevenir o link de seguir
    event.preventDefault();
    
    // Texto de confirmação
    const texto = acao === 'remover' ? 'Remover este post?' : 'Publicar este post?';
    if (!confirm(texto)) {
        return false;
    }
    
    // Desabilitar o link visualmente
    const textoOriginal = elemento.textContent;
    elemento.textContent = '...';
    elemento.style.pointerEvents = 'none';
    elemento.style.opacity = '0.7';
    
    // Fazer requisição AJAX
    fetch('post_toggle_estado_ajax.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + id + '&acao=' + acao
    })
    .then(response => response.json())
    .then(data => {
        if (data.sucesso) {
            window.mostrarMensagem(data.mensagem, 'sucesso');
            
            // Atualizar APENAS a célula do estado
            const linha = elemento.closest('tr');
            const celulaEstado = linha.querySelector('td:nth-child(5)'); // Coluna do estado
            if (celulaEstado) {
                celulaEstado.textContent = data.novo_estado;
            }
            
            // Atualizar o link (manter o de editar, trocar o de ação)
            const celulaAcoes = linha.querySelector('td:last-child');
            const linkEditar = celulaAcoes.querySelector('a[href*="post_editar.php"]');
            
            let novoSpan = '';
            if (data.novo_estado === 'publicado') {
                novoSpan = `
                    <a href="post_remover.php?id=${id}" 
                       class="btn-editar btn-danger"
                       onclick="return confirmarAcaoPost(event, ${id}, 'remover', this)">
                        Remover
                    </a>
                `;
            } else {
                novoSpan = `
                    <a href="post_publicar.php?id=${id}" 
                       class="btn-editar btn-success"
                       onclick="return confirmarAcaoPost(event, ${id}, 'publicar', this)">
                        Publicar
                    </a>
                `;
            }
            
            celulaAcoes.innerHTML = linkEditar.outerHTML + ' ' + novoSpan;
            
        } else {
            window.mostrarMensagem(data.mensagem || 'Erro ao processar', 'erro');
            elemento.textContent = textoOriginal;
            elemento.style.pointerEvents = 'auto';
            elemento.style.opacity = '1';
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        window.mostrarMensagem('Erro de conexão', 'erro');
        elemento.textContent = textoOriginal;
        elemento.style.pointerEvents = 'auto';
        elemento.style.opacity = '1';
    });
    
    return false;
};
// ===== FUNÇÕES PARA MODAIS DE POSTS =====
window.abrirModalRemoverPost = function(id, titulo) {
    const removerId = document.getElementById('remover-post-id');
    const modalTitulo = document.getElementById('modal-post-titulo');
    const modal = document.getElementById('modal-remover-post');
    
    if (removerId) removerId.value = id;
    if (modalTitulo) modalTitulo.innerHTML = 'Tem certeza que deseja remover o post: <strong>"' + titulo + '"</strong>?';
    if (modal) modal.style.display = 'flex';
};

window.abrirModalPublicarPost = function(id, titulo) {
    const publicarId = document.getElementById('publicar-post-id');
    const modalTitulo = document.getElementById('modal-post-publicar-titulo');
    const modal = document.getElementById('modal-publicar-post');
    
    if (publicarId) publicarId.value = id;
    if (modalTitulo) modalTitulo.innerHTML = 'Tem certeza que deseja publicar o post: <strong>"' + titulo + '"</strong>?';
    if (modal) modal.style.display = 'flex';
};

window.confirmarRemocaoPost = function(event) {
    event.preventDefault();
    
    const id = document.getElementById('remover-post-id').value;
    const modal = document.getElementById('modal-remover-post');
    const botao = document.querySelector(`a[onclick*="abrirModalRemoverPost(${id})"]`);
    
    if (botao) {
        botao.style.pointerEvents = 'none';
        botao.style.opacity = '0.7';
    }
    
    fetch('post_toggle_estado_ajax.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + id + '&acao=remover'
    })
    .then(response => response.json())
    .then(data => {
        if (modal) modal.style.display = 'none';
        
        if (data.sucesso) {
            window.mostrarMensagem(data.mensagem, 'sucesso');
            
            // Atualizar a linha na tabela
            const linha = document.querySelector(`a[onclick*="abrirModalRemoverPost(${id})"]`)?.closest('tr');
            if (linha) {
                const celulaEstado = linha.querySelector('td:nth-child(5)');
                const celulaAcoes = linha.querySelector('td:last-child');
                
                if (celulaEstado) celulaEstado.textContent = 'removido';
                
                // Recriar os links (manter o editar, trocar o de ação)
                const linkEditar = celulaAcoes.querySelector('a[href*="post_editar.php"]');
                celulaAcoes.innerHTML = `
                    ${linkEditar.outerHTML}
                    <a href="post_publicar.php?id=${id}" 
                       class="btn-editar btn-success"
                       onclick="abrirModalPublicarPost(${id}, '${data.titulo || 'Post'}'); return false;">
                        Publicar
                    </a>
                `;
            }
        } else {
            window.mostrarMensagem(data.mensagem || 'Erro ao remover post', 'erro');
            if (botao) {
                botao.style.pointerEvents = 'auto';
                botao.style.opacity = '1';
            }
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        window.mostrarMensagem('Erro de conexão', 'erro');
        if (modal) modal.style.display = 'none';
        if (botao) {
            botao.style.pointerEvents = 'auto';
            botao.style.opacity = '1';
        }
    });
    
    return false;
};

window.confirmarPublicacaoPost = function(event) {
    event.preventDefault();
    
    const id = document.getElementById('publicar-post-id').value;
    const modal = document.getElementById('modal-publicar-post');
    const botao = document.querySelector(`a[onclick*="abrirModalPublicarPost(${id})"]`);
    
    if (botao) {
        botao.style.pointerEvents = 'none';
        botao.style.opacity = '0.7';
    }
    
    fetch('post_toggle_estado_ajax.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + id + '&acao=publicar'
    })
    .then(response => response.json())
    .then(data => {
        if (modal) modal.style.display = 'none';
        
        if (data.sucesso) {
            window.mostrarMensagem(data.mensagem, 'sucesso');
            
            // Atualizar a linha na tabela
            const linha = document.querySelector(`a[onclick*="abrirModalPublicarPost(${id})"]`)?.closest('tr');
            if (linha) {
                const celulaEstado = linha.querySelector('td:nth-child(5)');
                const celulaAcoes = linha.querySelector('td:last-child');
                
                if (celulaEstado) celulaEstado.textContent = 'publicado';
                
                // Recriar os links
                const linkEditar = celulaAcoes.querySelector('a[href*="post_editar.php"]');
                celulaAcoes.innerHTML = `
                    ${linkEditar.outerHTML}
                    <a href="post_remover.php?id=${id}" 
                       class="btn-editar btn-danger"
                       onclick="abrirModalRemoverPost(${id}, '${data.titulo || 'Post'}'); return false;">
                        Remover
                    </a>
                `;
            }
        } else {
            window.mostrarMensagem(data.mensagem || 'Erro ao publicar post', 'erro');
            if (botao) {
                botao.style.pointerEvents = 'auto';
                botao.style.opacity = '1';
            }
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        window.mostrarMensagem('Erro de conexão', 'erro');
        if (modal) modal.style.display = 'none';
        if (botao) {
            botao.style.pointerEvents = 'auto';
            botao.style.opacity = '1';
        }
    });
    
    return false;
};
// ============================================
// INICIALIZAÇÃO PRINCIPAL
// ============================================
// Tudo dentro daqui só executa quando o DOM estiver completamente carregado
document.addEventListener("DOMContentLoaded", () => {

    /* ================= DROPDOWNS ================= */
    const dropdowns = document.querySelectorAll(".dropdown-btn");

    dropdowns.forEach(btn => {
        btn.addEventListener("click", () => {
            // Fecha todos os outros dropdowns
            dropdowns.forEach(otherBtn => {
                if (otherBtn !== btn) {
                    otherBtn.classList.remove("active");
                    const otherContent = otherBtn.nextElementSibling;
                    if (otherContent) otherContent.classList.remove("show");
                }
            });

            // Alterna o dropdown clicado
            btn.classList.toggle("active");
            const content = btn.nextElementSibling;
            if (content) content.classList.toggle("show");
        });
    });

    /* ================= MODAIS GENÉRICOS ================= */
    // Abre modais via atributo data-modal
    document.querySelectorAll("[data-modal]").forEach(el => {
        el.addEventListener("click", () => {
            const modalId = el.getAttribute("data-modal");
            const modal = document.getElementById(modalId);
            if (modal) modal.style.display = "flex";
        });
    });

    // Fecha modais com botão .modal-close
    document.querySelectorAll(".modal-close").forEach(btn => {
        btn.addEventListener("click", () => {
            const modal = btn.closest(".modal");
            if (modal) {
                modal.style.display = "none";
                
                // Limpeza específica para modal de password
                if (modal.id === 'modal-password') {
                    const novaPassword = document.getElementById('nova_password');
                    const confirmPassword = document.getElementById('confirm_password_modal');
                    if (novaPassword) novaPassword.value = '';
                    if (confirmPassword) confirmPassword.value = '';
                    resetPasswordValidation();
                }
            }
        });
    });

    // Fecha modal ao clicar fora (no fundo escuro)
    document.querySelectorAll(".modal").forEach(modal => {
        modal.addEventListener("click", e => {
            if (e.target === modal) {
                modal.style.display = "none";
                
                // Limpeza específica para modal de password
                if (modal.id === 'modal-password') {
                    const novaPassword = document.getElementById('nova_password');
                    const confirmPassword = document.getElementById('confirm_password_modal');
                    if (novaPassword) novaPassword.value = '';
                    if (confirmPassword) confirmPassword.value = '';
                    resetPasswordValidation();
                }
            }
        });
    });

    // ===== MODO CLARO/ESCURO - VERSÃO SIMPLIFICADA =====
    const themeToggle = document.getElementById('theme-toggle');
    const themeIcon = document.getElementById('theme-icon');
    const themeText = document.getElementById('theme-text');

    function setTheme(isDark) {
        if (isDark) {
            document.documentElement.classList.add('dark-mode');
            document.body.classList.add('dark-mode');
            document.documentElement.style.backgroundColor = '#1a1a1a';
            if (themeIcon) themeIcon.className = 'fa-solid fa-moon';
            if (themeText) themeText.textContent = 'Modo Escuro';
            localStorage.setItem('theme', 'dark');
            // NÃO MEXER NO LOGO
        } else {
            document.documentElement.classList.remove('dark-mode');
            document.body.classList.remove('dark-mode');
            document.documentElement.style.backgroundColor = '#f5f5f5';
            if (themeIcon) themeIcon.className = 'fa-solid fa-sun';
            if (themeText) themeText.textContent = 'Modo Claro';
            localStorage.setItem('theme', 'light');
            // NÃO MEXER NO LOGO
        }
    }

    // Sincronizar UI com o estado atual
    const savedTheme = localStorage.getItem('theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    const isDark = savedTheme === 'dark' || (!savedTheme && prefersDark);

    // Atualizar ícones sem mudar o tema (já está aplicado)
    if (isDark) {
        if (themeIcon) themeIcon.className = 'fa-solid fa-moon';
        if (themeText) themeText.textContent = 'Modo Escuro';
        // NÃO MEXER NO LOGO
    } else {
        if (themeIcon) themeIcon.className = 'fa-solid fa-sun';
        if (themeText) themeText.textContent = 'Modo Claro';
    }

    // Toggle ao clicar
    if (themeToggle) {
        themeToggle.addEventListener('click', (e) => {
            e.preventDefault();
            const isDark = document.body.classList.contains('dark-mode');
            setTheme(!isDark);
        });
    }

    // ===== OBSERVAR MUDANÇAS DE TEMA NO SISTEMA =====
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
        if (!localStorage.getItem('theme')) { // Só se o usuário não tiver escolhido
            setTheme(e.matches);
        }
    });

    /* ================= LOGÓTIPO ================= */
    const logo = document.querySelector(".logo");
    if (logo) {
        logo.addEventListener("mouseenter", () => logo.classList.add("logo-hover"));
        logo.addEventListener("mouseleave", () => logo.classList.remove("logo-hover"));
        logo.addEventListener("click", () => window.location.href = "../index.php");
    }

    /* ================= VENDA SELECT ================= */
    const vendaSelect = document.getElementById('vendaSelect');
    if (vendaSelect) {
        vendaSelect.addEventListener('change', function() {
            const option = this.options[this.selectedIndex];
            
            // Preenche campos baseado na opção selecionada
            const clienteInput = document.getElementById('cliente');
            const totalInput = document.getElementById('total');
            if (clienteInput) clienteInput.value = option.dataset.cliente || "";
            if (totalInput) totalInput.value = option.dataset.total || "";
            
            const nomeInput = document.getElementById('nome_destinatario');
            const moradaInput = document.getElementById('morada');
            const telefoneInput = document.getElementById('telefone');
            
            if (nomeInput) nomeInput.value = option.dataset.nome || "";
            if (telefoneInput) telefoneInput.value = option.dataset.telefone || "";
            
            if (moradaInput) {
                const endereco = option.dataset.endereco || "";
                const cidade = option.dataset.cidade || "";
                const pais = option.dataset.pais || "";
                moradaInput.value = endereco + "\n" + cidade + "\n" + pais;
            }
            
            const obraInput = document.getElementById('obra');
            const precoObraInput = document.getElementById('preco_obra');
            const precoVendaInput = document.getElementById('preco_venda');
            
            if (obraInput) obraInput.value = option.dataset.obra || "";
            if (precoObraInput) precoObraInput.value = option.dataset.precoObra || "";
            if (precoVendaInput) precoVendaInput.value = option.dataset.precoVenda || "";
        });
    }

    /* ================= MÉTODO DE PAGAMENTO ================= */
    const metodoSelect = document.getElementById('metodoSelect');
    const metodoOutro = document.getElementById('metodoOutro');
    
    if (metodoSelect && metodoOutro) {
        metodoSelect.addEventListener('change', () => {
            if (metodoSelect.value === 'Outro') {
                metodoOutro.style.display = 'block';
                metodoOutro.required = true;
            } else {
                metodoOutro.style.display = 'none';
                metodoOutro.required = false;
            }
        });
    }

    /* ================= TELEFONE MODAL ================= */
    function initTelefoneModal(){
        const telefone = document.getElementById("telefone_modal");

        if(!telefone || telefone.classList.contains("iti-initialized")) return;

        if(typeof window.intlTelInput === "undefined"){
            setTimeout(initTelefoneModal,500);
            return;
        }

        const iti = window.intlTelInput(telefone,{
            initialCountry:"pt",
            preferredCountries:["pt","ao","br"],
            separateDialCode:false,
            utilsScript:"https://cdn.jsdelivr.net/npm/intl-tel-input@18.1.1/build/js/utils.js"
        });

        telefone.classList.add("iti-initialized");

        const form = document.getElementById("form-telefone");

        if(form){
            form.addEventListener("submit",()=>{
                telefone.value = iti.getNumber();
            });
        }
    }

    /* ================= TELEFONE REGISTO ================= */
    function initTelefoneRegisto() {
        const telefoneInput = document.getElementById('telefone_registo');
        if (telefoneInput && !telefoneInput.classList.contains('iti-initialized')) {
            if (typeof window.intlTelInput !== 'undefined') {
                const iti = window.intlTelInput(telefoneInput, {
                    initialCountry: "pt",
                    preferredCountries: ["pt", "ao", "br"],
                    separateDialCode: false,
                    utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@18.1.1/build/js/utils.js"
                });
                telefoneInput.classList.add('iti-initialized');
                
                const form = document.querySelector('form');
                if (form) {
                    form.addEventListener('submit', function() {
                        const numeroCompleto = iti.getNumber();
                        if (numeroCompleto) {
                            telefoneInput.value = numeroCompleto;
                        }
                    });
                }
                console.log('Telefone registo inicializado');
            }
        }
    }

    if (document.getElementById('telefone_registo')) {
        setTimeout(initTelefoneRegisto, 200);
    }

    /* ================= VALIDAÇÃO DE PASSWORD (MODAL) ================= */
    function resetPasswordValidation() {
        const lengthEl = document.getElementById('lengthModal');
        const uppercaseEl = document.getElementById('uppercaseModal');
        const numberEl = document.getElementById('numberModal');
        const matchMsg = document.getElementById('passwordMatchModal');
        const submitBtn = document.getElementById('btn-submit-password');
        
        if (lengthEl) {
            lengthEl.className = 'invalid';
            lengthEl.innerHTML = '✗ Mínimo 8 caracteres';
        }
        if (uppercaseEl) {
            uppercaseEl.className = 'invalid';
            uppercaseEl.innerHTML = '✗ Pelo menos uma letra maiúscula';
        }
        if (numberEl) {
            numberEl.className = 'invalid';
            numberEl.innerHTML = '✗ Pelo menos um número';
        }
        if (matchMsg) matchMsg.style.display = 'none';
        if (submitBtn) submitBtn.disabled = true;
    }

    function initPasswordModal() {
        const novaPassword = document.getElementById('nova_password');
        const confirmPassword = document.getElementById('confirm_password_modal');
        const toggleNova = document.getElementById('toggleNovaPassword');
        const toggleConfirm = document.getElementById('toggleConfirmPasswordModal');
        const novaEye = document.getElementById('novaEyeIcon');
        const confirmEye = document.getElementById('confirmEyeIconModal');
        const lengthEl = document.getElementById('lengthModal');
        const uppercaseEl = document.getElementById('uppercaseModal');
        const numberEl = document.getElementById('numberModal');
        const matchMsg = document.getElementById('passwordMatchModal');
        const submitBtn = document.getElementById('btn-submit-password');
        
        if (!novaPassword || !confirmPassword) return;
        
        console.log('Inicializando validação de password');
        
        resetPasswordValidation();
        novaPassword.value = '';
        confirmPassword.value = '';
        
        let confirmStarted = false;
        
        function checkPasswordStrength() {
            const value = novaPassword.value;
            
            const lengthValid = value.length >= 8;
            const uppercaseValid = /[A-Z]/.test(value);
            const numberValid = /\d/.test(value);
            
            if (lengthEl) {
                lengthEl.className = lengthValid ? 'valid' : 'invalid';
                lengthEl.innerHTML = lengthValid ? '✓ Mínimo 8 caracteres' : '✗ Mínimo 8 caracteres';
            }
            if (uppercaseEl) {
                uppercaseEl.className = uppercaseValid ? 'valid' : 'invalid';
                uppercaseEl.innerHTML = uppercaseValid ? '✓ Pelo menos uma letra maiúscula' : '✗ Pelo menos uma letra maiúscula';
            }
            if (numberEl) {
                numberEl.className = numberValid ? 'valid' : 'invalid';
                numberEl.innerHTML = numberValid ? '✓ Pelo menos um número' : '✗ Pelo menos um número';
            }
            
            return lengthValid && uppercaseValid && numberValid;
        }
        
        function checkPasswordsMatch() {
            if (!novaPassword || !confirmPassword) return false;
            
            if (confirmPassword.value.length > 0 && novaPassword.value !== confirmPassword.value) {
                matchMsg.style.display = 'block';
                return false;
            } else {
                matchMsg.style.display = 'none';
                return true;
            }
        }
        
        function updateSubmitButton() {
            if (!submitBtn) return;
            
            const strengthValid = checkPasswordStrength();
            const matchValid = checkPasswordsMatch();
            const hasConfirm = confirmPassword.value.length > 0;
            
            submitBtn.disabled = !(strengthValid && matchValid && hasConfirm);
        }
        
        novaPassword.oninput = updateSubmitButton;
        
        confirmPassword.onfocus = () => { confirmStarted = true; };
        confirmPassword.oninput = function() {
            if (confirmStarted) updateSubmitButton();
        };
        
        if (toggleNova) {
            toggleNova.onclick = function() {
                const isPassword = novaPassword.type === 'password';
                novaPassword.type = isPassword ? 'text' : 'password';
                if (novaEye) {
                    novaEye.className = isPassword ?  'fa-solid fa-eye-slash' : 'fa-solid fa-eye' ;
                }
            };
        }
        
        if (toggleConfirm) {
            toggleConfirm.onclick = function() {
                const isPassword = confirmPassword.type === 'password';
                confirmPassword.type = isPassword ? 'text' : 'password';
                if (confirmEye) {
                    confirmEye.className = isPassword ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
                }
            };
        }
        
        if (submitBtn) submitBtn.disabled = true;
    }
    
    /* ================= ABRIR MODAIS ESPECÍFICOS ================= */
    document.querySelectorAll("[data-modal]").forEach(btn => {
        btn.addEventListener("click", () => {
            const modalId = btn.getAttribute("data-modal");
            
            if (modalId === 'modal-telefone') {
                setTimeout(initTelefoneModal, 200);
            }
            
            if (modalId === 'modal-password') {
                setTimeout(initPasswordModal, 300);
            }
        });
    });

    /* ================= TOGGLE PASSWORD (ELIMINAR CONTA) ================= */
    const toggleDeletePassword = document.getElementById("toggleDeletePassword");
    if (toggleDeletePassword) {
        toggleDeletePassword.addEventListener("click", function () {
            const input = document.getElementById("delete_password");
            const icon = document.getElementById("deleteEyeIcon");

            if (input && icon) {
                if (input.type === "password") {
                    input.type = "text";
                    icon.classList.remove("fa-eye");
                    icon.classList.add("fa-eye-slash");
                } else {
                    input.type = "password";
                    icon.classList.remove("fa-eye-slash");
                    icon.classList.add("fa-eye");
                }
            }
        });
    }

    /* ================= INICIALIZAR BOTÕES DE DESTAQUE ================= */
    setupDestaqueButtons();
    /* ================= INICIALIZAR BOTÕES DE REATIVAR ================= */
    setupReativarButtons();
    /* NOTA: O formulário de remoção NÃO tem evento aqui porque
       usamos onsubmit="return enviarRemocao(event)" diretamente no HTML
       e a função enviarRemocao já está definida globalmente acima */
// ===== MENU MOBILE =====
const menuToggle = document.getElementById('menu-toggle');
const header = document.querySelector('header');
const overlay = document.getElementById('menu-overlay');

if (menuToggle && header && overlay) {
    menuToggle.addEventListener('click', function() {
        header.classList.toggle('active');
        overlay.classList.toggle('active');
        
        const icon = this.querySelector('i');
        if (header.classList.contains('active')) {
            icon.className = 'fa fa-times';
        } else {
            icon.className = 'fa fa-bars';
        }
    });

    overlay.addEventListener('click', function() {
        header.classList.remove('active');
        overlay.classList.remove('active');
        if (menuToggle.querySelector('i')) {
            menuToggle.querySelector('i').className = 'fa fa-bars';
        }
    });

    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            header.classList.remove('active');
            overlay.classList.remove('active');
            if (menuToggle.querySelector('i')) {
                menuToggle.querySelector('i').className = 'fa fa-bars';
            }
        }
    });
}
}); // Fim do DOMContentLoaded