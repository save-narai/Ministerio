document.addEventListener("DOMContentLoaded", () => {

    /* ==========================================
       DATATABLE
    ========================================== */

    const tabla = initDataTable("#tablaHistorial");

    if (tabla) {

        initSearch(
            "buscadorHistorial",
            tabla
        );

        initExportButtons(tabla);

    }

    /* ==========================================
       ANIMACIÓN STATS
    ========================================== */

    document
        .querySelectorAll(".gx-stat-card")
        .forEach((card, index) => {

            card.style.opacity = "0";
            card.style.transform = "translateY(20px)";

            setTimeout(() => {

                card.style.transition = "all .35s ease";

                card.style.opacity = "1";

                card.style.transform = "translateY(0)";

            }, index * 120);

        });

    /* ==========================================
       ANIMACIÓN TABLA
    ========================================== */

    const tablaWrapper = document.querySelector(".table-wrapper");

    if (tablaWrapper) {

        tablaWrapper.style.opacity = "0";
        tablaWrapper.style.transform = "translateY(15px)";

        setTimeout(() => {

            tablaWrapper.style.transition = "all .4s ease";

            tablaWrapper.style.opacity = "1";

            tablaWrapper.style.transform = "translateY(0)";

        }, 300);

    }

    /* ==========================================
       TOOLTIPS
    ========================================== */

    document
        .querySelectorAll("[data-tooltip]")
        .forEach(el => {

            el.title = el.dataset.tooltip;

        });

});