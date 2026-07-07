(() => {
    const root = document.documentElement;
    const themeKey = 'fechou-theme';

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
})();