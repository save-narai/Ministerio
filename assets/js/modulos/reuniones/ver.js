/* =========================================================
   VER REUNIÓN
========================================================= */

document.addEventListener("DOMContentLoaded", () => {

    const tabla =
        initDataTable("#tablaParticipantes");

    if (!tabla) {
        return;
    }

    /* =====================================================
       EXPORTAR
    ===================================================== */

    initExportButtons(tabla);

    /* =====================================================
       BUSCADOR
    ===================================================== */

    initSearch(
        "buscarParticipante",
        tabla
    );

    /* =====================================================
       FILTROS
    ===================================================== */

    initFilters(
        ".filter-chip",
        tabla,
        {
            todos: "",
            asistio: "Asistió",
            falto: "Faltó",
            teen: "Teen",
            remanente: "Remanente"
        }
    );

});