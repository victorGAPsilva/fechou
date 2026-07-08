(() => {
    const root = document.documentElement;
    const themeKey = 'fechou-theme';
    let loadingVisible = false;

    const setTheme = (theme) => {
        root.dataset.theme = theme;
        document.cookie = `theme=${theme}; path=/; max-age=31536000; samesite=lax`;
        localStorage.setItem(themeKey, theme);
    };

    const savedTheme = localStorage.getItem(themeKey) || root.dataset.theme || 'dark';
    setTheme(savedTheme);

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const nextTheme = root.dataset.theme === 'dark' ? 'light' : 'dark';
            setTheme(nextTheme);
        });
    });

    document.querySelectorAll('[data-toggle-password]').forEach((button) => {
        button.addEventListener('click', () => {
            const target = document.querySelector(button.dataset.togglePassword);

            if (!target) {
                return;
            }

            const visible = target.type === 'text';
            target.type = visible ? 'password' : 'text';
            button.textContent = visible ? 'Mostrar' : 'Ocultar';
        });
    });

    const navToggle = document.querySelector('[data-mobile-nav-toggle]');
    const navClose = document.querySelector('[data-mobile-nav-close]');
    const mobileNavLinks = document.querySelectorAll('.sidebar-nav a');

    const setMobileNav = (open) => {
        document.body.classList.toggle('nav-open', open);
        navToggle?.setAttribute('aria-expanded', open ? 'true' : 'false');
        navToggle?.setAttribute('aria-label', open ? 'Fechar menu' : 'Abrir menu');
    };

    navToggle?.addEventListener('click', () => {
        setMobileNav(!document.body.classList.contains('nav-open'));
    });

    navClose?.addEventListener('click', () => setMobileNav(false));
    mobileNavLinks.forEach((link) => link.addEventListener('click', () => setMobileNav(false)));

    window.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setMobileNav(false);
        }
    });

    window.addEventListener('resize', () => {
        if (window.matchMedia('(min-width: 64rem)').matches) {
            setMobileNav(false);
        }
    });

    const createFallbackLoader = () => {
        let loader = document.querySelector('[data-page-loader]');

        if (loader) {
            return loader;
        }

        loader = document.createElement('div');
        loader.className = 'page-loader';
        loader.dataset.pageLoader = 'true';
        loader.innerHTML = `
            <div class="page-loader-dialog" role="status" aria-live="polite">
                <span class="page-loader-spinner"></span>
                <strong data-page-loader-title>Carregando</strong>
                <small data-page-loader-text>Aguarde um instante.</small>
            </div>
        `;
        document.body.appendChild(loader);

        return loader;
    };

    const showLoading = (title = 'Carregando', text = 'Aguarde um instante.') => {
        if (loadingVisible) {
            return;
        }

        loadingVisible = true;

        if (window.Swal) {
            window.Swal.fire({
                title,
                text,
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => window.Swal.showLoading(),
                customClass: {
                    popup: 'fechou-swal-popup',
                },
            });
            return;
        }

        const loader = createFallbackLoader();
        loader.querySelector('[data-page-loader-title]').textContent = title;
        loader.querySelector('[data-page-loader-text]').textContent = text;
        loader.classList.add('is-visible');
    };

    const hideLoading = () => {
        if (loadingVisible && window.Swal && window.Swal.isVisible()) {
            window.Swal.close();
        }

        loadingVisible = false;

        const loader = document.querySelector('[data-page-loader]');

        if (loader) {
            loader.classList.remove('is-visible');
        }
    };

    const submitWithLoading = (form, title = 'Processando', text = 'Salvando as informacoes com seguranca.') => {
        showLoading(title, text);
        window.setTimeout(() => HTMLFormElement.prototype.submit.call(form), 80);
    };

    const showFlashMessage = () => {
        const flash = window.FechouFlash || {};
        const isError = Boolean(flash.error);
        const message = isError ? flash.error : flash.success;

        if (!message || !window.Swal) {
            return;
        }

        window.Swal.fire({
            title: isError ? 'Atenção' : 'Tudo certo',
            text: message,
            icon: isError ? 'error' : 'success',
            confirmButtonText: 'Ok',
            customClass: {
                popup: 'fechou-swal-popup',
                confirmButton: isError ? 'swal-button-danger' : 'swal-button-confirm',
            },
        });
    };

    showFlashMessage();

    document.querySelectorAll('form').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (form.dataset.noLoading === 'true') {
                return;
            }

            if (form.dataset.loadingHandled === 'true') {
                return;
            }

            const deleteMessage = form.dataset.confirmDelete;

            if (deleteMessage) {
                event.preventDefault();

                const confirmDelete = () => {
                    form.dataset.loadingHandled = 'true';
                    submitWithLoading(form, 'Excluindo', 'Removendo o registro selecionado.');
                };

                if (!window.Swal) {
                    return;
                }

                window.Swal.fire({
                    title: deleteMessage,
                    text: 'Essa acao nao pode ser desfeita.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sim, excluir',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true,
                    focusCancel: true,
                    customClass: {
                        popup: 'fechou-swal-popup',
                        confirmButton: 'swal-button-danger',
                        cancelButton: 'swal-button-cancel',
                    },
                }).then((result) => {
                    if (result.isConfirmed) {
                        confirmDelete();
                    }
                });

                return;
            }

            form.dataset.loadingHandled = 'true';
            showLoading('Processando', 'Aguarde enquanto concluimos a solicitacao.');
        });
    });

    document.querySelectorAll('a[href]').forEach((link) => {
        link.addEventListener('click', (event) => {
            if (event.defaultPrevented || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
                return;
            }

            if (link.target && link.target !== '_self') {
                return;
            }

            const href = link.getAttribute('href') || '';

            if (href === '' || href.startsWith('#') || href.startsWith('mailto:') || href.startsWith('tel:')) {
                return;
            }

            let url;

            try {
                url = new URL(href, window.location.href);
            } catch (error) {
                return;
            }

            if (url.origin !== window.location.origin || url.href === window.location.href) {
                return;
            }

            if (url.pathname.endsWith('/pdf')) {
                return;
            }

            showLoading('Carregando', 'Preparando a proxima tela.');
        });
    });

    window.addEventListener('pageshow', hideLoading);
})();
