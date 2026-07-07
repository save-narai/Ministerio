/* ==========================================================
   LOGIN
========================================================== */

document.addEventListener("DOMContentLoaded", () => {

    const form = document.getElementById("loginForm");

    const user = document.getElementById("usuario");

    const password = document.getElementById("password");

    const toggle = document.getElementById("togglePassword");

    const toggleIcon = document.getElementById("togglePasswordIcon");

    const button = document.getElementById("btnLogin");

    if (!form) return;

    /* ======================================================
       AUTOFOCUS
    ====================================================== */

    if (user && user.value.trim() === "") {

        user.focus();

    }

    /* ======================================================
       SHOW / HIDE PASSWORD
    ====================================================== */

    if (toggle && password && toggleIcon) {

        toggle.addEventListener("click", () => {

            const visible = password.type === "text";

            password.type = visible
                ? "password"
                : "text";

            toggleIcon.className = visible
                ? "fa-solid fa-eye"
                : "fa-solid fa-eye-slash";

        });

    }

    /* ======================================================
       SUBMIT
    ====================================================== */

    form.addEventListener("submit", (event) => {

        if (!form.checkValidity()) {

            return;

        }

        if (button) {

            button.disabled = true;

            button.classList.add("loading");

        }

    });

    /* ======================================================
       RESTORE BUTTON
    ====================================================== */

    window.addEventListener("pageshow", () => {

        if (!button) return;

        button.disabled = false;

        button.classList.remove("loading");

    });

});