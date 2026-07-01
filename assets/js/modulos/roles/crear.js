/* =====================================================
   CREAR ROL
===================================================== */

document.addEventListener("DOMContentLoaded", () => {

    const checks = document.querySelectorAll(
        '.permission-card input[type="checkbox"]'
    );

    const seleccionar =
        document.getElementById("seleccionarTodos");

    const limpiar =
        document.getElementById("limpiarTodos");

    if (seleccionar) {

        seleccionar.addEventListener("click", () => {

            checks.forEach(check => {

                check.checked = true;

            });

        });

    }

    if (limpiar) {

        limpiar.addEventListener("click", () => {

            checks.forEach(check => {

                check.checked = false;

            });

        });

    }

});