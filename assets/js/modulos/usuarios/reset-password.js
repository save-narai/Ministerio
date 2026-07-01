/* =====================================================
   RESET PASSWORD
===================================================== */

document.addEventListener("DOMContentLoaded", () => {

    const password = document.getElementById("password");
    const confirmar = document.getElementById("confirmarPassword");
    const error = document.getElementById("passwordError");
    const boton = document.getElementById("btnGuardar");

    if (
        !password ||
        !confirmar ||
        !error ||
        !boton
    ) {
        return;
    }

    function validar() {

        if (confirmar.value === "") {

            error.textContent = "";
            boton.disabled = false;

            return;
        }

        if (password.value !== confirmar.value) {

            error.textContent = "Las contraseñas no coinciden.";

            boton.disabled = true;

            return;
        }

        error.textContent = "";

        boton.disabled = false;

    }

    password.addEventListener(
        "input",
        validar
    );

    confirmar.addEventListener(
        "input",
        validar
    );

});