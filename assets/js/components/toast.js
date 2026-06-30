class Toast {

    static show(
        message,
        type = 'success'
    ) {

        const toast = document.createElement('div');

        toast.className =
            `toast toast-${type}`;

        toast.textContent = message;

        document.body.appendChild(toast);

        setTimeout(() => {

            toast.classList.add('show');

        }, 100);

        setTimeout(() => {

            toast.remove();

        }, 4000);
    }
}

window.Toast = Toast;