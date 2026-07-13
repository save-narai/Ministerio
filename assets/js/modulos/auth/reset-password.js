/* ==========================================================
   RESET PASSWORD
========================================================== */

document.addEventListener("DOMContentLoaded", () => {

    initResetPassword();

});

/* ==========================================================
   INICIALIZAR
========================================================== */

function initResetPassword() {

    const password = document.getElementById("password");

    const confirmPassword =
        document.getElementById("confirm_password");

    const button =
        document.getElementById("btnResetPassword");

    if (

        !password ||

        !confirmPassword ||

        !button

    ) {

        return;

    }

    password.addEventListener(

        "input",

        validatePassword

    );

    confirmPassword.addEventListener(

        "input",

        validatePassword

    );

    validatePassword();

}

/* ==========================================================
   VALIDAR CONTRASEÑA
========================================================== */

function validatePassword() {

    const password =
        document.getElementById("password");

    const confirmPassword =
        document.getElementById("confirm_password");

    const button =
        document.getElementById("btnResetPassword");

    const value = password.value;

    const checks = {

        length : value.length >= 8,

        upper  : /[A-ZÁÉÍÓÚÑ]/.test(value),

        lower  : /[a-záéíóúñ]/.test(value),

        number : /\d/.test(value),

        symbol : /[^A-Za-z0-9]/.test(value),

        match  :

            value !== "" &&

            value === confirmPassword.value

    };

    Object.keys(checks).forEach((key) => {

        const item = document.querySelector(

            `[data-check="${key}"]`

        );

        if (!item) return;

        item.classList.toggle(

            "active",

            checks[key]

        );

    });

    let score = 0;

    Object.values(checks).forEach((ok) => {

        if (ok) {

            score++;

        }

    });

    updateStrength(score);

    button.disabled = !checks.match;

}

/* ==========================================================
   FUERZA
========================================================== */

function updateStrength(score) {

    const bar =
        document.getElementById(

            "passwordStrengthBar"

        );

    const text =
        document.getElementById(

            "passwordStrengthText"

        );

    if (

        !bar ||

        !text

    ) {

        return;

    }

    const percentage =

        (score / 6) * 100;

    bar.style.width = percentage + "%";

    bar.className =

        "password-strength-bar";

    if (score <= 2) {

        bar.classList.add("weak");

        text.textContent =

            "Contraseña débil";

    }

    else if (score <= 4) {

        bar.classList.add("medium");

        text.textContent =

            "Contraseña aceptable";

    }

    else if (score === 5) {

        bar.classList.add("strong");

        text.textContent =

            "Contraseña segura";

    }

    else {

        bar.classList.add("very-strong");

        text.textContent =

            "Contraseña muy segura";

    }
    

}