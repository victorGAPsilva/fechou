(function () {
    document.querySelectorAll('[data-confirm-delete]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const message = form.dataset.confirmDelete || 'Confirma a exclusão?';

            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });
    });
})();