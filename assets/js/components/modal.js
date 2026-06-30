class Modal {

    static open(id) {

        const modal =
            document.getElementById(id);

        if (!modal) return;

        modal.classList.add('show');
    }

    static close(id) {

        const modal =
            document.getElementById(id);

        if (!modal) return;

        modal.classList.remove('show');
    }
}

window.Modal = Modal;