document.addEventListener("DOMContentLoaded", () => {

    /* =========================================
       FILTROS Y BUSCADOR
    ========================================= */

    const buscador =
        document.getElementById("buscarParticipante");

    const filas =
        document.querySelectorAll(
            ".reunion-table-wrapper tbody tr"
        );

    const filtros =
        document.querySelectorAll(
            ".reunion-filters .filter-chip"
        );

    let filtroActivo = "todos";

    function aplicarFiltros(){

        const texto =
            buscador
                ? buscador.value.toLowerCase()
                : "";

        filas.forEach(fila => {

            const nombre =
                fila.innerText.toLowerCase();

            const grupo =
                fila.dataset.grupo || "";

            const asistencia =
                fila.dataset.asistencia || "";

            let visible = true;

            if(
                texto &&
                !nombre.includes(texto)
            ){
                visible = false;
            }

            if(filtroActivo !== "todos"){

                if(
                    filtroActivo === "asistio" &&
                    asistencia !== "asistio"
                ){
                    visible = false;
                }

                if(
                    filtroActivo === "falto" &&
                    asistencia !== "falto"
                ){
                    visible = false;
                }

                if(
                    filtroActivo === "teen" &&
                    grupo !== "teen"
                ){
                    visible = false;
                }

                if(
                    filtroActivo === "remanente" &&
                    grupo !== "remanente"
                ){
                    visible = false;
                }
            }

            fila.style.display =
                visible ? "" : "none";
        });
    }

    if(buscador){

        buscador.addEventListener(
            "keyup",
            aplicarFiltros
        );
    }

    filtros.forEach(btn => {

        btn.addEventListener("click", () => {

            filtros.forEach(b =>
                b.classList.remove(
                    "filter-chip--active"
                )
            );

            btn.classList.add(
                "filter-chip--active"
            );

            filtroActivo =
                btn.dataset.filter;

            aplicarFiltros();

        });

    });

    /* =========================================
       DATATABLE + EXPORTS
    ========================================= */

    const table =
        initDataTable("#tablaParticipantes");

    initExportButtons(table);

});