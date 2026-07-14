/* =====================================================
   USUARIOS - INDEX
===================================================== */

document.addEventListener("DOMContentLoaded", initUsuarios);

/* =====================================================
   INICIALIZACIÓN
===================================================== */

function initUsuarios() {

    const tabla = initDataTable(
        "#tablaUsuarios"
    );

    if (!tabla) {

        console.warn(
            "No fue posible inicializar la tabla de usuarios."
        );

        return;
    }

    initBuscador(tabla);

    initExportaciones(tabla);

}

/* =====================================================
   BUSCADOR
===================================================== */

function initBuscador(tabla) {

    const buscador = document.getElementById(
        "buscador"
    );

    if (!buscador) {

        return;

    }

    initSearch(

        "buscador",

        tabla

    );

}

/* =====================================================
   EXPORTACIONES
===================================================== */

function initExportaciones(tabla) {

    if (

        typeof initExportButtons !== "function"

    ) {

        return;

    }

    initExportButtons(

        tabla

    );

}