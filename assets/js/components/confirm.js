document.addEventListener(
    'click',
    e => {

        const btn =
            e.target.closest(
                '[data-confirm]'
            );

        if (!btn) return;

        const mensaje =
            btn.dataset.confirm;

        if (!confirm(mensaje)) {

            e.preventDefault();
        }
    }
);