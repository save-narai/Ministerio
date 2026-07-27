/* =====================================================
   SEGUIMIENTOS - INDEX
===================================================== */

document.addEventListener("DOMContentLoaded", () => {

<<<<<<< HEAD
=======
    /* =============================================
       TABLA HISTORIAL
    ============================================= */

    const tablaSeguimientos = document.querySelector(
        "#tablaSeguimientos"
    );

    if (!tablaSeguimientos) {
        return;
    }

>>>>>>> 3e2d89c (Actualización del proyecto)
    const tabla = initDataTable(
        "#tablaSeguimientos"
    );

    if (!tabla) {
        return;
    }

    /* =============================================
       BUSCADOR
    ============================================= */

<<<<<<< HEAD
    initSearch(
        "buscador",
        tabla
    );

=======
    const buscador = document.getElementById(
        "buscador"
    );

    if (buscador) {

        initSearch(
            "buscador",
            tabla
        );

    }

>>>>>>> 3e2d89c (Actualización del proyecto)
    /* =============================================
       EXPORTACIONES
    ============================================= */

<<<<<<< HEAD
    initExportButtons(
        tabla
    );
=======
    if (typeof initExportButtons === "function") {

        initExportButtons(
            tabla
        );

    }
>>>>>>> 3e2d89c (Actualización del proyecto)

});