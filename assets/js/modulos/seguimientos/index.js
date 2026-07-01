/* =====================================================
   SEGUIMIENTOS - INDEX
===================================================== */

document.addEventListener("DOMContentLoaded", () => {

    const tabla = initDataTable(
        "#tablaSeguimientos"
    );

    if (!tabla) {
        return;
    }

    /* =============================================
       BUSCADOR
    ============================================= */

    initSearch(
        "buscador",
        tabla
    );

    /* =============================================
       EXPORTACIONES
    ============================================= */

    initExportButtons(
        tabla
    );

});