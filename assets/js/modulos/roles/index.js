/* =====================================================
   ROLES
===================================================== */

document.addEventListener("DOMContentLoaded", () => {

    const tabla = initDataTable(
        "#tablaRoles"
    );

    if (!tabla) {
        return;
    }

    initSearch(
        "buscador",
        tabla
    );

    initExportButtons(
        tabla
    );

});