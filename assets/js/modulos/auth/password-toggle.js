/* ==========================================================
   PASSWORD TOGGLE
========================================================== */

document.addEventListener("DOMContentLoaded", () => {

    initPasswordToggle();

});

/* ==========================================================
   MOSTRAR / OCULTAR CONTRASEÑA
========================================================== */

function initPasswordToggle() {

    const botones = document.querySelectorAll(".password-toggle");

    if (!botones.length) {
        return;
    }

    botones.forEach((boton) => {

        const selector = boton.dataset.toggle;

        const input = document.querySelector(selector);

        if (!input) {
            return;
        }

        boton.addEventListener("click", () => {

            const visible = input.type === "text";

            input.type = visible
                ? "password"
                : "text";

            const icono = boton.querySelector("i");

            if (!icono) {
                return;
            }

            icono.classList.toggle(
                "fa-eye",
                visible
            );

            icono.classList.toggle(
                "fa-eye-slash",
                !visible
            );

        });

    });

}