document.addEventListener("DOMContentLoaded", () => {

    const form = document.getElementById("formJoven");

    if (!form) return;

    inicializarNormalizacionNombre(form);

    const fecha = document.getElementById("fecha");
    const edad = document.getElementById("edad");
    const telefono = document.getElementById("telefono");
    const sinTelefono = document.getElementById("sinTelefono");

    /* ============================================
       FECHA / EDAD
    ============================================ */

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

    /* ============================================
       TELÉFONO
    ============================================ */

    function actualizarTelefono() {

        if (!telefono || !sinTelefono) return;

        telefono.disabled = sinTelefono.checked;

        if (sinTelefono.checked) {

            telefono.value = "";

        }

    }

    if (telefono && sinTelefono) {

        sinTelefono.addEventListener(
            "change",
            actualizarTelefono
        );

        actualizarTelefono();

    }

    /* ============================================
       VALIDACIÓN TELÉFONO
    ============================================ */

    if (typeof initPhoneValidation === "function") {

        initPhoneValidation();

    }

    /* ============================================
       EVITAR DOBLE ENVÍO
    ============================================ */

    form.addEventListener("submit", () => {

        const boton =
            form.querySelector(
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
