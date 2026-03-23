document.addEventListener("DOMContentLoaded", () => {

    // ===== FUNÇÃO PARA ATUALIZAR BADGE DO CARRINHO =====
    function atualizarBadgeCarrinho(total) {
        const badge = document.getElementById('cart-count');
        if (badge) {
            badge.textContent = total;
            
            // Animação de pulso (mais suave que scale)
            badge.classList.add('pulse');
            setTimeout(() => {
                badge.classList.remove('pulse');
            }, 200);
            
            // O badge fica sempre visível, mesmo com 0 (opcional)
            // Se quiser esconder quando 0, descomente as linhas abaixo:
            // if (total == 0) {
            //     badge.style.display = 'none';
            // } else {
            //     badge.style.display = 'flex';
            // }
        }
    }

    // ===== ADICIONAR AO CARRINHO =====
    document.querySelectorAll(".add-carrinho").forEach(btn => {
        btn.addEventListener("click", e => {
            e.preventDefault();

            const obraId = btn.dataset.obra;

            fetch("carrinho_add_ajax.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "obra_id=" + obraId
            })
            .then(res => res.json())
            .then(data => {
                if (data.estado === "adicionado") {
                    // Feedback no botão
                    btn.innerHTML = '<i class="fa fa-check"></i> No carrinho';
                    btn.disabled = true;
                    btn.style.opacity = ".7";

                    // ATUALIZAR BADGE DO MENU (funciona mesmo na primeira adição)
                    atualizarBadgeCarrinho(data.total);
                }
            })
            .catch(error => {
                console.error('Erro ao adicionar:', error);
            });
        });
    });

    // ===== REMOVER ITEM DO CARRINHO (COM AJAX) =====
    document.querySelectorAll('.btn-remover-ajax').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();

            const obraId = this.dataset.id;
            const linha = document.getElementById('item-' + obraId);
            const linhaAlternativa = document.getElementById('linha-' + obraId);

            fetch('carrinho_remove_ajax.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + obraId
            })
            .then(res => res.json())
            .then(data => {
                // Atualizar contador no header (se existir)
const contador = document.querySelector('.contador-carrinho');
if (contador) contador.textContent = data.contador;

// Atualizar total no resumo do carrinho
const totalResumo = document.querySelector('.resumo-total strong');
if (totalResumo) totalResumo.textContent = data.totalGeral;

// Atualizar subtotal
const subtotalResumo = document.querySelector('.resumo-linha span:last-child');
if (subtotalResumo) subtotalResumo.textContent = data.totalGeral;

// Atualizar quantidade total
const totalItens = document.querySelector('.total-itens span:last-child');
if (totalItens) totalItens.textContent = data.contador + (data.contador == 1 ? ' item' : ' itens');

                if (data.sucesso) {
                    // Remover a linha com efeito visual
                    const elementoParaRemover = linha || linhaAlternativa;
                    if (elementoParaRemover) {
                        elementoParaRemover.style.transition = "opacity 0.3s";
                        elementoParaRemover.style.opacity = "0";
                        
                        setTimeout(() => {
    elementoParaRemover.remove();

    // Atualizar contador no header
    const contador = document.querySelector('.contador-carrinho');
    if (contador) contador.textContent = data.contador;

    // Atualizar total no resumo
    const totalResumo = document.querySelector('.resumo-total strong');
    if (totalResumo) totalResumo.textContent = data.totalGeral;

    // Atualizar subtotal
    const subtotalResumo = document.querySelector('.resumo-linha span:last-child');
    if (subtotalResumo) subtotalResumo.textContent = data.totalGeral;

    // Atualizar quantidade total
    const totalItens = document.querySelector('.total-itens span:last-child');
    if (totalItens) totalItens.textContent = data.contador + (data.contador == 1 ? ' item' : ' itens');

    // Se carrinho ficar vazio
    if (data.contador === 0) {
        const containerCarrinho = document.querySelector('.carrinho-content');
        if (containerCarrinho) {
            containerCarrinho.innerHTML = `
                <div class="carrinho-vazio">
                    <div class="vazio-icone">
                        <i class="fa-solid fa-cart-shopping"></i>
                    </div>
                    <h2>Seu carrinho está vazio</h2>
                    <p>Explore obras incríveis e comece sua coleção</p>
                    <a href="loja.php" class="btn">Explorar Loja</a>
                </div>
            `;
        }
    }
}, 300);

                    }

                    // ATUALIZAR BADGE DO MENU
                    atualizarBadgeCarrinho(data.contador);

                    // ATUALIZAR TOTAL GERAL (se estiver na página do carrinho)
                    const totalGeralEl = document.querySelector('.resumo-total strong');
                    if (totalGeralEl) {
                        totalGeralEl.textContent = data.totalGeral;
                    }
                }
            })
            .catch(error => {
                console.error('Erro ao remover:', error);
            });
        });
    });

    // ===== FAVORITOS =====
    document.querySelectorAll(".toggle-fav").forEach(btn => {
        btn.addEventListener("click", e => {
            e.preventDefault();

            const obraId = btn.dataset.obra;

            fetch("favorito_toggle.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "obra_id=" + obraId
            })
            .then(res => res.json())
            .then(data => {
                if (data.estado === "adicionado") {
                    btn.textContent = "❤️";
                    btn.classList.add("fav-ativo");
                } else if (data.estado === "removido") {
                    btn.textContent = "🤍";
                    btn.classList.remove("fav-ativo");
                    
                    // Se estiver na página de favoritos, remover o card
                    if (window.location.pathname.includes('favoritos.php')) {
                        const card = btn.closest('.card');
                        if (card) {
                            card.style.transition = "opacity 0.3s";
                            card.style.opacity = "0";
                            setTimeout(() => card.remove(), 300);
                        }
                    }
                }
            });
        });
    });

    // ===== INICIALIZAÇÃO: GARANTIR QUE BADGE APARECE =====
    // Na primeira carga, verificar se há itens no carrinho
    fetch('carrinho_total_ajax.php')
        .then(res => res.json())
        .then(data => {
            atualizarBadgeCarrinho(data.total);
        })
        .catch(() => {
            // Se falhar, manter o valor que veio do PHP
        });

});