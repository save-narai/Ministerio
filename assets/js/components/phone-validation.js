/* =========================================================
   PHONE VALIDATION
========================================================= */

function initPhoneValidation(){

    const telefono =
        document.getElementById("telefono");

    const checkbox =
        document.getElementById("sinTelefono");

    const error =
        document.getElementById("telefonoError");

    if(!telefono){
        return;
    }

    /* =====================================================
       VALID PHONE
    ===================================================== */

    function telefonoValido(numero){

        return /^3\d{9}$/.test(numero)
            &&
            !/^(\d)\1+$/.test(numero);
    }

    /* =====================================================
       INPUT EVENT
    ===================================================== */

    telefono.addEventListener("input", () => {

        telefono.value =
            telefono.value.replace(/\D/g,'');

        if(telefono.value.length === 10){

            if(telefonoValido(telefono.value)){

                error.textContent =
                    "Número válido";

                error.className =
                    "telefono-error success";

                telefono.classList.remove(
                    "input-error"
                );

                telefono.classList.add(
                    "input-success"
                );

            }else{

                error.textContent =
                    "Número inválido";

                error.className =
                    "telefono-error error";

                telefono.classList.remove(
                    "input-success"
                );

                telefono.classList.add(
                    "input-error"
                );
            }

        }else{

            error.textContent = "";

            telefono.classList.remove(
                "input-success",
                "input-error"
            );
        }

    });

    /* =====================================================
       NO PHONE
    ===================================================== */

    if(checkbox){

        checkbox.addEventListener("change", () => {

            telefono.disabled =
                checkbox.checked;

            if(checkbox.checked){

                telefono.value = "";

                error.textContent = "";

                telefono.classList.remove(
                    "input-success",
                    "input-error"
                );
            }

        });

    }

}