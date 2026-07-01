document.addEventListener("DOMContentLoaded", () => {

    /* ===============================
       DATATABLE
    =============================== */

    const tabla = initDataTable("#tablaJovenes");

    if (tabla) {

        initSearch("buscador", tabla);

        initExportButtons(tabla);

    }

    /* ===============================
       TOOLTIPS
    =============================== */

    document
        .querySelectorAll("[data-tooltip]")
        .forEach(btn => {

            btn.title = btn.dataset.tooltip;

        });

    /* ===============================
       DOBLE CLICK
    =============================== */

    document
        .querySelectorAll("form")
        .forEach(form => {

            form.addEventListener("submit", () => {

                const boton =
                    form.querySelector("button");

                if (!boton) return;

                boton.disabled = true;

            });

        });

});