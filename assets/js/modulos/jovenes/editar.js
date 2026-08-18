document.addEventListener("DOMContentLoaded", () => {

    const form =
        document.getElementById("formEditarJoven");

    if (!form) {
        return;
    }


    const fecha =
        document.getElementById("fecha");

    const edad =
        document.getElementById("edad");

    const telefono =
        document.getElementById("telefono");

    const sinTelefono =
        document.getElementById("sinTelefono");


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

        if (!fecha || !edad) {
            return;
        }


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


    if (fecha) {

        fecha.addEventListener(
            "change",
            sincronizarEdad
        );

    }


    if (edad) {

        edad.addEventListener(
            "input",
            sincronizarEdad
        );

    }


    sincronizarEdad();


    /* ===========================================
       TELÉFONO
    =========================================== */

    function actualizarTelefono() {

        if (!telefono || !sinTelefono) {
            return;
        }


        if (sinTelefono.checked) {

            telefono.value = "";

            telefono.disabled = true;

            telefono.removeAttribute("required");

        } else {

            telefono.disabled = false;

        }

    }


    if (sinTelefono) {

        sinTelefono.addEventListener(
            "change",
            actualizarTelefono
        );

    }


    actualizarTelefono();


    /* ===========================================
       VALIDACIÓN DEL TELÉFONO
    =========================================== */

    if (
        typeof initPhoneValidation ===
        "function"
    ) {

        initPhoneValidation();

    }


    /* ===========================================
       EVITAR DOBLE SUBMIT
    =========================================== */

    form.addEventListener(
        "submit",
        () => {

            const boton =
                form.querySelector(
                    'button[type="submit"]'
                );


            if (!boton) {
                return;
            }


            /*
             * Si está marcado "No tiene teléfono",
             * mantenemos el campo bloqueado.
             *
             * El backend deberá interpretar
             * sinTelefono=1 como teléfono NULL.
             */

            boton.disabled = true;

            boton.innerHTML = `
                <i class="fa-solid fa-spinner fa-spin"></i>
                Guardando...
            `;

        }
    );

});