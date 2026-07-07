/* ==========================================================
   REMEMBER ME
========================================================== */

document.addEventListener("DOMContentLoaded", () => {

    const form = document.getElementById("loginForm");
    const user = document.getElementById("usuario");
    const remember = document.getElementById("rememberMe");

    if (!form || !user || !remember) return;

    const STORAGE_KEY = "rememberUser";

    /* ======================================================
       LOAD
    ====================================================== */

    const savedUser = localStorage.getItem(STORAGE_KEY);

    if (savedUser) {

        user.value = savedUser;

        remember.checked = true;

    }

    /* ======================================================
       SAVE
    ====================================================== */

    form.addEventListener("submit", () => {

        if (remember.checked) {

            localStorage.setItem(
                STORAGE_KEY,
                user.value.trim()
            );

        } else {

            localStorage.removeItem(STORAGE_KEY);

        }

    });

});