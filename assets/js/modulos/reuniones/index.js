document.addEventListener("DOMContentLoaded", () => {

    /* ==========================================
       DATATABLE
    ========================================== */

    const tabla = initDataTable("#tablaReuniones");

    if (tabla) {

        initSearch(
            "buscador",
            tabla
        );

        initExportButtons(tabla);

    }

    /* ==========================================
       FILTRO POR MES
    ========================================== */

    const filtroMes = document.getElementById("filtroMes");

    if (filtroMes && tabla) {

        filtroMes.addEventListener("change", () => {

            const valor = filtroMes.value;

            if (!valor) {

                tabla.column(0).search("").draw();
                return;

            }

            tabla.column(0).search(valor).draw();

        });

    }

});


document.addEventListener("DOMContentLoaded", () => {

    const tabla = initDataTable("#tablaReuniones");

    if (tabla) {

        initSearch(
            "buscador",
            tabla
        );

        initExportButtons(tabla);

    }

    /* ==========================================
       FILTRO POR MES
    ========================================== */

    const filtroMes = document.getElementById("filtroMes");

    if (filtroMes && tabla) {

        filtroMes.addEventListener("change", () => {

            const valor = filtroMes.value;

            if (!valor) {

                tabla.column(0).search("").draw();
                return;

            }

            tabla.column(0).search(valor).draw();

        });

    }

    /* ==========================================
       ANIMACIÓN TABLA
    ========================================== */

    const wrapper = document.querySelector(".table-wrapper");

    if (wrapper) {

        wrapper.style.opacity = "0";
        wrapper.style.transform = "translateY(15px)";

        setTimeout(() => {

            wrapper.style.transition = "all .35s ease";

            wrapper.style.opacity = "1";

            wrapper.style.transform = "translateY(0)";

        }, 150);

    }

});