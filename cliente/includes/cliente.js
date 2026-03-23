// ===== DARK MODE - EXECUTAR IMEDIATAMENTE (ANTES DO DOM CARREGAR) =====
(function() {
    // Verificar preferência salva
    const savedTheme = localStorage.getItem('theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    
    // Aplicar tema imediatamente para evitar flash
    if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
        document.documentElement.classList.add('dark-mode');
        document.body.classList.add('dark-mode');
    }
})();

document.addEventListener("DOMContentLoaded", () => {

    // ================= DROPDOWNS =================
    const dropdowns = document.querySelectorAll(".dropdown-btn");

    dropdowns.forEach(btn => {
        btn.addEventListener("click", () => {
            dropdowns.forEach(otherBtn => {
                if (otherBtn !== btn) {
                    otherBtn.classList.remove("active");
                    const otherContent = otherBtn.nextElementSibling;
                    if (otherContent) otherContent.classList.remove("show");
                }
            });

            btn.classList.toggle("active");
            const content = btn.nextElementSibling;
            if (content) content.classList.toggle("show");
        });
    });

    // ================= MODAIS =================
    document.querySelectorAll(".perfil-btn, [data-modal]").forEach(btn => {
        btn.addEventListener("click", () => {
            const modalId = btn.getAttribute("data-modal");
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = "flex";
                
                // Inicializar telefone se for o modal de telefone
                if (modalId === 'modal-telefone') {
                    setTimeout(initTelefoneModal, 200);
                }
                
                // Inicializar password se for o modal de password
                if (modalId === 'modal-password') {
                    setTimeout(initPasswordModal, 300);
                }
            }
        });
    });

    document.querySelectorAll(".modal-close").forEach(btn => {
        btn.addEventListener("click", () => {
            const modal = btn.closest(".modal");
            if (modal) {
                modal.style.display = "none";
                
                // Limpar inputs quando fechar
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

    document.querySelectorAll(".modal").forEach(modal => {
        modal.addEventListener("click", e => {
            if (e.target === modal) {
                modal.style.display = "none";
                
                // Limpar inputs quando fechar
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

    // ================= LOGOUT MODAL =================
    const btnLogout = document.getElementById("btn-logout");
    const modalLogout = document.getElementById("modal-logout");
    
    if (btnLogout && modalLogout) {
        btnLogout.addEventListener("click", (e) => {
            e.preventDefault();
            modalLogout.style.display = "flex";
        });
    }

    // ================= RELÓGIO =================
    const clock = document.getElementById("clock");
    if (clock) updateClock();

    function updateClock() {
        const now = new Date();
        const day = now.getDate().toString().padStart(2, '0');
        const month = (now.getMonth() + 1).toString().padStart(2, '0');
        const hour = now.getHours().toString().padStart(2, '0');
        const min = now.getMinutes().toString().padStart(2, '0');
        const days = ["DOM", "SEG", "TER", "QUA", "QUI", "SEX", "SAB"];
        const dayName = days[now.getDay()];
        const states = [`${day}/${month}`, `${hour}:${min}`, dayName];

        let i = 0;
        clock.textContent = states[i];

        setInterval(() => {
            clock.classList.add("rotate");
            setTimeout(() => {
                i = (i + 1) % states.length;
                clock.textContent = states[i];
                clock.classList.remove("rotate");
            }, 600);
        }, 3000);
    }

    // ================= LIGHTBOX =================
    const lightbox = document.getElementById("portfolioLightbox");
    const lightboxImage = document.getElementById("lightboxImage");
    const lightboxTitle = document.getElementById("lightboxTitle");
    const lightboxCounter = document.getElementById("lightboxCounter");
    const btnCloseLight = document.getElementById("lightboxClose");
    const btnPrev = document.getElementById("lightboxPrev");
    const btnNext = document.getElementById("lightboxNext");
    const zoomButtons = document.querySelectorAll(".portfolio-zoom-btn");

    let currentIndex = 0;

    if (lightbox && lightboxImage && lightboxTitle && lightboxCounter && typeof portfolioObras !== 'undefined') {
        function updateLightbox() {
            const obra = portfolioObras[currentIndex];
            lightboxImage.src = "../assets/img/obras/" + obra.imagem;
            lightboxTitle.textContent = obra.titulo;
            lightboxCounter.textContent = `${currentIndex + 1} / ${portfolioObras.length}`;
        }

        function openLightbox(index) {
            currentIndex = index;
            updateLightbox();
            lightbox.classList.add("active");
            document.body.style.overflow = "hidden";
        }

        function closeLightbox() {
            lightbox.classList.remove("active");
            document.body.style.overflow = "";
        }

        function showPrev() {
            currentIndex = currentIndex === 0 ? portfolioObras.length - 1 : currentIndex - 1;
            updateLightbox();
        }

        function showNext() {
            currentIndex = currentIndex === portfolioObras.length - 1 ? 0 : currentIndex + 1;
            updateLightbox();
        }

        zoomButtons.forEach(btn => {
            btn.addEventListener("click", e => {
                e.preventDefault();
                e.stopPropagation();
                const index = parseInt(btn.dataset.index);
                openLightbox(index);
            });
        });

        if (btnCloseLight) btnCloseLight.addEventListener("click", closeLightbox);
        if (btnPrev) btnPrev.addEventListener("click", showPrev);
        if (btnNext) btnNext.addEventListener("click", showNext);

        const overlay = document.getElementById("lightboxOverlay");
        if (overlay) overlay.addEventListener("click", closeLightbox);

        document.addEventListener("keydown", e => {
            if (!lightbox?.classList.contains("active")) return;
            if (e.key === "Escape") closeLightbox();
            if (e.key === "ArrowLeft") showPrev();
            if (e.key === "ArrowRight") showNext();
        });
    }

    // ================= FAVORITOS =================
    document.querySelectorAll(".toggle-fav").forEach(btn => {
        btn.addEventListener("click", e => {
            e.preventDefault();
            const obraId = btn.dataset.obra;

            fetch("../favorito_toggle.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "obra_id=" + obraId
            })
            .then(res => res.json())
            .then(data => {
                if (data.estado === "removido") {
                    const card = btn.closest(".card");
                    if (card) {
                        card.style.transition = "opacity 0.3s ease";
                        card.style.opacity = "0";
                        setTimeout(() => card.remove(), 300);
                    }
                } else if (data.estado === "adicionado") {
                    btn.innerHTML = '❤️';
                    btn.classList.add('fav-ativo');
                }
            })
            .catch(error => console.error('Erro:', error));
        });
    });

    // ================= FUNÇÕES DO TELEFONE =================
    function initTelefoneModal() {
        const telefoneModal = document.getElementById('telefone_modal');
        if (telefoneModal && !telefoneModal.classList.contains('iti-initialized')) {
            if (typeof window.intlTelInput !== 'undefined') {
                window.intlTelInput(telefoneModal, {
                    initialCountry: "pt",
                    preferredCountries: ["pt", "ao", "br"],
                    separateDialCode: false,
                    utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@18.1.1/build/js/utils.js"
                });
                telefoneModal.classList.add('iti-initialized');
                
                telefoneModal.addEventListener('input', function(e) {
                    let value = this.value.replace(/[^0-9]/g, '');
                    this.value = value;
                });
                console.log('Telefone inicializado');
            } else {
                console.log('Aguardando carregamento do intl-tel-input...');
                setTimeout(initTelefoneModal, 500);
            }
        }
    }

    // ================= FUNÇÕES DA PASSWORD =================
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
        
        if (!novaPassword || !confirmPassword) {
            console.log('Inputs de password não encontrados');
            return;
        }
        
        console.log('Inicializando validação de password');
        
        // Resetar validação e limpar valores
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
        
        // Nova password
        novaPassword.oninput = updateSubmitButton;
        
        // Confirmação
        confirmPassword.onfocus = () => { confirmStarted = true; };
        confirmPassword.oninput = function() {
            if (confirmStarted) updateSubmitButton();
        };
        
        // Toggle password visibility
        if (toggleNova) {
            toggleNova.onclick = function() {
                const isPassword = novaPassword.type === 'password';
                novaPassword.type = isPassword ? 'text' : 'password';
                if (novaEye) {
                    novaEye.className = isPassword ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
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
        
        // Garantir que botão começa desabilitado
        if (submitBtn) submitBtn.disabled = true;
        
        console.log('Password modal inicializado com sucesso');
    }

    // ================= CARREGAR SCRIPTS EXTERNOS =================
    if (typeof window.intlTelInput === 'undefined') {
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/intl-tel-input@18.1.1/build/js/intlTelInput.min.js';
        document.head.appendChild(script);
        
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = 'https://cdn.jsdelivr.net/npm/intl-tel-input@18.1.1/build/css/intlTelInput.css';
        document.head.appendChild(link);
    }

    // ================= FUNÇÕES PARA MODAIS PERSONALIZADOS =================
    window.abrirModalReembolso = function(pagamentoId, vendaId) {
        const pagamentoInput = document.getElementById('reembolso-pagamento-id');
        const vendaInput = document.getElementById('reembolso-venda-id');
        const modal = document.getElementById('modal-reembolso');
        
        if (pagamentoInput) pagamentoInput.value = pagamentoId;
        if (vendaInput) vendaInput.value = vendaId;
        if (modal) modal.style.display = 'flex';
    };

    window.abrirModalCancelar = function(reservaId, obraId) {
        const reservaInput = document.getElementById('cancelar-reserva-id');
        const obraInput = document.getElementById('cancelar-obra-id');
        const modal = document.getElementById('modal-cancelar-reserva');
        
        if (reservaInput) reservaInput.value = reservaId;
        if (obraInput && obraId) obraInput.value = obraId;
        if (modal) modal.style.display = 'flex';
    };

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
        } else {
            document.documentElement.classList.remove('dark-mode');
            document.body.classList.remove('dark-mode');
            document.documentElement.style.backgroundColor = '#f5f5f5';
            if (themeIcon) themeIcon.className = 'fa-solid fa-sun';
            if (themeText) themeText.textContent = 'Modo Claro';
            localStorage.setItem('theme', 'light');
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

    // ================= OBSERVAR MUDANÇAS DE TEMA NO SISTEMA =================
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
        if (!localStorage.getItem('theme')) { // Só se o usuário não tiver escolhido
            setTheme(e.matches);
        }
    });

    // ================= SLIDESHOW DAS POLÍTICAS =================
function initPoliticasSlideshow() {
    const track = document.querySelector('.politicas-track');
    const cards = document.querySelectorAll('.politicas-card');
    const prevBtn = document.getElementById('politicasPrev');
    const nextBtn = document.getElementById('politicasNext');
    const dots = document.querySelectorAll('.politicas-dot');

    // 🚨 Se faltar QUALQUER elemento essencial, não inicia
    if (!track || !cards.length || !prevBtn || !nextBtn || !dots.length) {
        console.warn("Slideshow não iniciado — elementos em falta.");
        return;
    }

    
    let currentIndex = 0;
    const totalCards = cards.length;
    
    function goToCard(index) {
        if (index < 0) index = totalCards - 1;
        if (index >= totalCards) index = 0;
        
        currentIndex = index;
        track.style.transform = `translateX(-${currentIndex * 100}%)`;
        
        if (dots.length > 0) {
            dots.forEach((dot, i) => {
                dot.classList.toggle('active', i === currentIndex);
            });
        }
    }
    
    if (nextBtn) nextBtn.addEventListener('click', () => goToCard(currentIndex + 1));
    if (prevBtn) prevBtn.addEventListener('click', () => goToCard(currentIndex - 1));
    
    if (dots.length > 0) {
        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => goToCard(index));
        });
    }
    
    goToCard(0);
}


    // Inicializar slideshow das políticas se existir na página
    if (document.querySelector('.politicas-slideshow')) {
        initPoliticasSlideshow();
    }
    function alertarPintor(pedidoId) {
    fetch("alertar_pintor.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "id=" + pedidoId + "&tipo=venda"
    })
    .then(response => response.text())
    .then(() => {
        // Atualiza apenas o botão
        const btn = event.target;
        btn.outerHTML = `
            <button class="btn-alertar-enviado" disabled>
                <i class="fa-solid fa-check"></i> Pintor Alertado
            </button>
        `;
    });
}
// ================= FUNÇÕES DO TELEFONE =================
function initTelefoneModal() {
    const telefoneModal = document.getElementById('telefone_modal');
    if (telefoneModal && !telefoneModal.classList.contains('iti-initialized')) {
        if (typeof window.intlTelInput !== 'undefined') {
            // Guardar referência do objeto iti
            const iti = window.intlTelInput(telefoneModal, {
                initialCountry: "pt",
                preferredCountries: ["pt", "ao", "br"],
                separateDialCode: false,
                utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@18.1.1/build/js/utils.js"
            });
            
            telefoneModal.classList.add('iti-initialized');
            
            // NÃO filtrar apenas números - o intl-tel-input já gerencia isso
            console.log('Telefone inicializado');
            
            // No submit do formulário, usar o número completo
            const form = document.getElementById('form-telefone');
            if (form) {
                form.addEventListener('submit', function(e) {
                    // Obter o número completo com código do país
                    if (iti && typeof iti.getNumber === 'function') {
                        const numeroCompleto = iti.getNumber();
                        if (numeroCompleto) {
                            telefoneModal.value = numeroCompleto;
                        }
                    }
                });
            }
        } else {
            console.log('Aguardando carregamento do intl-tel-input...');
            setTimeout(initTelefoneModal, 500);
        }
    }
}
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
}); // Fim do DOMContentLoaded