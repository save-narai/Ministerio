document.addEventListener("DOMContentLoaded", () => {

    const form = document.getElementById("formEditarJoven");

    if (!form) return;

    const fecha = document.getElementById("fecha");
    const edad = document.getElementById("edad");
    const telefono = document.getElementById("telefono");
    const sinTelefono = document.getElementById("sinTelefono");

    /* ===========================================
       TOAST
    =========================================== */

    if (
        window.toastMessage &&
        typeof showToast === "function"
    ) {

        showToast(
            window.toastMessage,
            window.toastType
        );

    }

    /* ===========================================
       FECHA / EDAD
    =========================================== */

    function sincronizarEdad() {

        if (fecha.value) {

            edad.value = "";
            edad.disabled = true;

        } else {

            edad.disabled = false;

        }

        if (edad.value) {

            fecha.value = "";
            fecha.disabled = true;

        } else {

            fecha.disabled = false;

        }

    }

    fecha.addEventListener("change", sincronizarEdad);

    edad.addEventListener("input", sincronizarEdad);

    sincronizarEdad();

    /* ===========================================
       TELÉFONO
    =========================================== */

    function actualizarTelefono() {

        telefono.disabled = sinTelefono.checked;

        if (sinTelefono.checked) {

            telefono.value = "";

        }

    }

    sinTelefono.addEventListener(
        "change",
        actualizarTelefono
    );

    actualizarTelefono();

    /* ===========================================
       VALIDACIÓN
    =========================================== */

    if (typeof initPhoneValidation === "function") {

        initPhoneValidation();

    }

    /* ===========================================
       EVITAR DOBLE SUBMIT
    =========================================== */

    form.addEventListener("submit", () => {

        const boton = form.querySelector(
            'button[type="submit"]'
        );

        if (!boton) return;

        boton.disabled = true;

        boton.innerHTML = `
            <i class="fa-solid fa-spinner fa-spin"></i>
            Guardando...
        `;

    });

});