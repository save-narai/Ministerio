/* ==========================================================
   LOGIN VALIDATION
========================================================== */

document.addEventListener("DOMContentLoaded", () => {

    const form = document.getElementById("loginForm");

    const usuario = document.getElementById("usuario");

    const password = document.getElementById("password");

    if (!form || !usuario || !password) return;

    /* ======================================================
       VALIDATE FIELD
    ====================================================== */

    function validateField(input) {

        const value = input.value.trim();

        input.classList.remove(
            "is-error",
            "is-success"
        );

        if (value === "") {

            input.classList.add("is-error");

            return false;

        }

        input.classList.add("is-success");

        return true;

    }

    /* ======================================================
       USERNAME
    ====================================================== */

    usuario.addEventListener("input", () => {

        validateField(usuario);

    });

    /* ======================================================
       PASSWORD
    ====================================================== */

    password.addEventListener("input", () => {

        validateField(password);

    });

    /* ======================================================
       SUBMIT
    ====================================================== */

    form.addEventListener("submit", (event) => {

        const validUser =
            validateField(usuario);

        const validPassword =
            validateField(password);

        if (!validUser || !validPassword) {

            event.preventDefault();

            const firstError = form.querySelector(".is-error");

            if (firstError) {

                firstError.focus();

            }

        }

    });

    /* ======================================================
       ENTER
    ====================================================== */

    form.addEventListener("keydown", (event) => {

        if (

            event.key === "Enter" &&

            document.activeElement === usuario

        ) {

            event.preventDefault();

            password.focus();

        }

    });

});