/* =====================================================
   USUARIOS - INDEX
===================================================== */

document.addEventListener("DOMContentLoaded", () => {

    const tabla = initDataTable(
        "#tablaUsuarios"
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