"use strict";

/* ==========================================================
   CLASES DEL CICLO — DROPDOWN DE MATERIAL
   ----------------------------------------------------------
   Reutiliza el componente .discipulado-dropdown (ya definido
   en _discipulado.scss) para condensar "Ver PDF" / "Descargar"
   en un único botón de icono.
========================================================== */

(() => {

    function cerrarTodos(exceptoEste = null) {

        document
            .querySelectorAll(".discipulado-dropdown.open")
            .forEach((abierto) => {

                if (abierto !== exceptoEste) {
                    abierto.classList.remove("open");
                }

            });

    }

    document.addEventListener("click", (evento) => {

        const boton = evento.target.closest(
            "[data-dropdown-toggle]"
        );

        if (boton) {

            const contenedor = boton.closest(
                "[data-dropdown]"
            );

            const yaAbierto =
                contenedor.classList.contains("open");

            cerrarTodos();

            contenedor.classList.toggle(
                "open",
                !yaAbierto
            );

            evento.stopPropagation();

            return;

        }

        // Clic fuera de cualquier dropdown: cerrar todos.
        if (!evento.target.closest("[data-dropdown]")) {
            cerrarTodos();
        }

    });

    document.addEventListener("keydown", (evento) => {

        if (evento.key === "Escape") {
            cerrarTodos();
        }

    });

})();
