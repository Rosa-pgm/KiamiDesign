document.addEventListener("DOMContentLoaded", () => {

    // ===== ELEMENTOS =====
    const passwordInput = document.getElementById("password");
    const confirmInput = document.getElementById("confirm_password");
    const togglePassword = document.getElementById("togglePassword");
    const toggleConfirmPassword = document.getElementById("toggleConfirmPassword");
    const eyeIcon = document.getElementById("eyeIcon");
    const confirmEyeIcon = document.getElementById("confirmEyeIcon");
    const passwordMatch = document.getElementById("passwordMatch");
    const telefoneInput = document.getElementById("telefone");

    // Variável para controlar se já começou a digitar a confirmação
    let confirmStarted = false;

    // ===== INICIALIZAÇÃO DO TELEFONE =====
    if (telefoneInput) {
        const iti = window.intlTelInput(telefoneInput, {
            initialCountry: "ao",
            preferredCountries: ["ao", "pt", "br"],
            separateDialCode: false,
            utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@18.1.1/build/js/utils.js"
        });

        telefoneInput.addEventListener('input', function(e) {
            let value = this.value.replace(/[^0-9]/g, '');
            this.value = value;
        });
    }

    // ===== VALIDAÇÃO DE PASSWORD =====
    if (passwordInput) {
        passwordInput.addEventListener("input", function() {
            const value = passwordInput.value;
            
            const lengthValid = value.length >= 8;
            const uppercaseValid = /[A-Z]/.test(value);
            const numberValid = /\d/.test(value);
            
            const lengthEl = document.getElementById("length");
            const uppercaseEl = document.getElementById("uppercase");
            const numberEl = document.getElementById("number");
            
            if (lengthEl) lengthEl.className = lengthValid ? "valid" : "invalid";
            if (uppercaseEl) uppercaseEl.className = uppercaseValid ? "valid" : "invalid";
            if (numberEl) numberEl.className = numberValid ? "valid" : "invalid";

            if (confirmStarted && confirmInput) {
                checkPasswordsMatch();
            }
        });
    }

    // ===== CONFIRMAÇÃO DE PASSWORD =====
    if (confirmInput) {
        confirmInput.addEventListener("focus", function() {
            confirmStarted = true;
        });

        confirmInput.addEventListener("input", function() {
            confirmStarted = true;
            if (passwordInput) {
                checkPasswordsMatch();
            }
        });

        confirmInput.addEventListener("blur", function() {
            if (confirmInput.value.length > 0 && passwordInput) {
                checkPasswordsMatch();
            }
        });
    }

    function checkPasswordsMatch() {
        if (!passwordInput || !confirmInput || !passwordMatch) return;
        
        if (passwordInput.value !== confirmInput.value) {
            passwordMatch.style.display = "block";
        } else {
            passwordMatch.style.display = "none";
        }
    }

    // ===== MOSTRAR/ESCONDER PASSWORD =====
    if (togglePassword && passwordInput) {
        togglePassword.addEventListener("click", function() {
            const isPassword = passwordInput.type === "password";
            passwordInput.type = isPassword ? "text" : "password";
            if (eyeIcon) {
                eyeIcon.className = isPassword ? "fa-solid fa-eye-slash" : "fa-solid fa-eye";
            }
        });
    }

    if (toggleConfirmPassword && confirmInput) {
        toggleConfirmPassword.addEventListener("click", function() {
            const isPassword = confirmInput.type === "password";
            confirmInput.type = isPassword ? "text" : "password";
            if (confirmEyeIcon) {
                confirmEyeIcon.className = isPassword ? "fa-solid fa-eye-slash" : "fa-solid fa-eye" ;
            }
        });
    }

    // ===== LOGÓTIPO =====
    const logo = document.querySelector(".logo");
    if (logo) {
        logo.addEventListener("mouseenter", () => logo.classList.add("logo-hover"));
        logo.addEventListener("mouseleave", () => logo.classList.remove("logo-hover"));
        logo.addEventListener("click", () => window.location.href = "index.php");
    }

    // ===== MENU ATIVO =====
    const links = document.querySelectorAll("nav a");
    const atual = window.location.pathname.split("/").pop();
    links.forEach(link => {
        if (link.getAttribute("href")?.includes(atual)) {
            link.classList.add("ativo");
        }
    });

    // ===== ANIMAÇÃO DOS CARDS =====
    const cards = document.querySelectorAll(".card, .post-card");
    if (cards.length) {
        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting)
                    entry.target.classList.add("card-show", "show");
            });
        }, { threshold: 0.2 });

        cards.forEach(card => observer.observe(card));
    }

    // ===== VALIDAÇÃO DE FORMULÁRIOS =====
    document.querySelectorAll(".auth-card").forEach(form => {
        form.addEventListener("submit", e => {
            const email = form.querySelector("input[type=email]");
            const msg = form.querySelector("textarea");
            let erro = "";

            if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
                erro = "Email inválido.";
            }

            if (msg && msg.value.trim().length < 15) {
                erro = "A mensagem deve ter pelo menos 15 caracteres.";
            }

            if (erro) {
                e.preventDefault();
                let box = form.querySelector(".form-error");
                if (!box) {
                    box = document.createElement("div");
                    box.className = "form-error auth-error";
                    form.prepend(box);
                }
                box.textContent = erro;
            }
        });
    });

    // ===== INPUT FOCUS =====
    document.querySelectorAll("input, textarea").forEach(el => {
        el.addEventListener("focus", () => el.classList.add("input-focus"));
        el.addEventListener("blur", () => el.classList.remove("input-focus"));
    });

    // ===== DARK MODE =====
    const themeBtn = document.getElementById("theme-toggle");
    const icon = themeBtn?.querySelector("i");

    const savedTheme = localStorage.getItem("theme");
    const prefersDark = window.matchMedia("(prefers-color-scheme: dark)").matches;

    if (savedTheme === "dark" || (!savedTheme && prefersDark)) {
        document.body.classList.add("dark");
        icon?.classList.replace("fa-sun", "fa-moon");
    }

    themeBtn?.addEventListener("click", () => {
        document.body.classList.toggle("dark");
        const dark = document.body.classList.contains("dark");
        localStorage.setItem("theme", dark ? "dark" : "light");
        icon?.classList.toggle("fa-moon");
        icon?.classList.toggle("fa-sun");
    });

    // ===== RELÓGIO =====
    const clock = document.getElementById("clock");

    function updateClock() {
        const now = new Date();
        const day = now.getDate().toString().padStart(2, '0');
        const month = (now.getMonth() + 1).toString().padStart(2, '0');
        const hour = now.getHours().toString().padStart(2, '0');
        const min = now.getMinutes().toString().padStart(2, '0');

        const days = ["DOM", "SEG", "TER", "QUA", "QUI", "SEX", "SAB"];
        const dayName = days[now.getDay()];

        const states = [
            `${day}/${month}`,
            `${hour}:${min}`,
            dayName
        ];

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

    if (clock) updateClock();

    // ===== LIGHTBOX DO PORTFÓLIO =====
    const lightbox = document.getElementById("portfolioLightbox");
    const lightboxImage = document.getElementById("lightboxImage");
    const lightboxTitle = document.getElementById("lightboxTitle");
    const lightboxCounter = document.getElementById("lightboxCounter");
    const btnClose = document.getElementById("lightboxClose");
    const btnPrev = document.getElementById("lightboxPrev");
    const btnNext = document.getElementById("lightboxNext");
    const zoomButtons = document.querySelectorAll(".portfolio-zoom-btn");

    let currentIndex = 0;

    if (lightbox && lightboxImage && lightboxTitle && lightboxCounter) {
        function updateLightbox() {
            if (!portfolioObras || !portfolioObras[currentIndex]) return;
            const obra = portfolioObras[currentIndex];
            lightboxImage.src = "assets/img/obras/" + obra.imagem;
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

        btnClose?.addEventListener("click", closeLightbox);
        btnPrev?.addEventListener("click", showPrev);
        btnNext?.addEventListener("click", showNext);

        document.getElementById("lightboxOverlay")?.addEventListener("click", closeLightbox);

        document.addEventListener("keydown", e => {
            if (!lightbox.classList.contains("active")) return;
            if (e.key === "Escape") closeLightbox();
            if (e.key === "ArrowLeft") showPrev();
            if (e.key === "ArrowRight") showNext();
        });
    }

    // ===== REMOVER ITEM DO CARRINHO =====
    document.querySelectorAll('.btn-remover-ajax').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();

            const idObra = this.dataset.id;
            const linha = document.getElementById('item-' + idObra);

            fetch('carrinho_remove_ajax.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + idObra
            })
            .then(res => res.json())
            .then(data => {
                if (data.sucesso) {
                    if (linha) {
                        linha.style.opacity = '0';
                        linha.style.transition = '0.3s';
                        
                        setTimeout(() => {
                            linha.remove();
                            
                            const menuCount = document.getElementById('cart-count');
                            if (menuCount) {
                                menuCount.textContent = data.contador;
                                if (data.contador === 0) {
                                    menuCount.classList.add('zero');
                                } else {
                                    menuCount.classList.remove('zero');
                                }
                            }

                            const totalGeral = document.querySelector('.resumo-total strong');
                            if (totalGeral) {
                                totalGeral.textContent = data.totalGeral;
                            }

                            if (data.contador === 0) {
                                setTimeout(() => location.reload(), 500);
                            }
                        }, 300);
                    }
                }
            })
            .catch(error => {
                console.error('Erro:', error);
            });
        });
    });

    // ===== SLIDESHOW EM DESTAQUE - CORRIGIDO =====
    const slides = document.querySelectorAll('.slideshow-slide');
    const track = document.querySelector('.slideshow-track');
    const prevBtn = document.getElementById('slideshowPrev');
    const nextBtn = document.getElementById('slideshowNext');
    const dots = document.querySelectorAll('.slideshow-dot');
    
    if (slides.length && track) {
        let currentIndex = 0;
        const totalSlides = slides.length;
        
        function goToSlide(index) {
            if (index < 0) index = totalSlides - 1;
            if (index >= totalSlides) index = 0;
            
            currentIndex = index;
            track.style.transform = `translateX(-${currentIndex * 100}%)`;
            
            dots.forEach((dot, i) => {
                if (i === currentIndex) {
                    dot.classList.add('active');
                } else {
                    dot.classList.remove('active');
                }
            });
        }
        
        if (nextBtn) {
            nextBtn.addEventListener('click', function(e) {
                e.preventDefault();
                goToSlide(currentIndex + 1);
            });
        }
        
        if (prevBtn) {
            prevBtn.addEventListener('click', function(e) {
                e.preventDefault();
                goToSlide(currentIndex - 1);
            });
        }
        
        dots.forEach((dot, index) => {
            dot.addEventListener('click', function(e) {
                e.preventDefault();
                goToSlide(index);
            });
        });
        
        goToSlide(0);
    }
// ===== VALIDAÇÃO DE DESCRIÇÃO (só executa se os elementos existirem) =====
const descricao = document.getElementById('descricao');
const contador   = document.getElementById('contador');
const erroDiv    = document.getElementById('erro-descricao');

if (descricao && contador && erroDiv) {
    function validar() {
        const len = descricao.value.length;
        contador.textContent = `${len}/1000 caracteres`;
        
        if (len > 0 && len < 20) {
            erroDiv.textContent = `Faltam ${20 - len} caracteres para poder enviar o pedido.`;
            erroDiv.style.display = 'block';
            descricao.style.borderColor = '#dc3545';
        } else {
            erroDiv.style.display = 'none';
            descricao.style.borderColor = '';
        }
    }

    descricao.addEventListener('input', validar);

    document.querySelector('form')?.addEventListener('submit', (e) => {
        if (descricao.value.length < 20) {
            e.preventDefault();
            alert('A descrição deve ter pelo menos 20 caracteres');
        }
    });
}

});