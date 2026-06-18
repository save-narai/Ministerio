/* =========================================================
   MARCAR ASISTENCIA
========================================================= */

document.addEventListener("DOMContentLoaded", () => {

    const buscador =
        document.getElementById("buscador");

    const filas =
        document.querySelectorAll(
            ".attendance-card"
        );

    const filtros =
        document.querySelectorAll(
            ".marcar-filters .filter-chip"
        );

    const btnCheckAll =
        document.getElementById("checkAll");

    const btnUncheckAll =
        document.getElementById("uncheckAll");

    let filtroActivo = "todos";

    /* =====================================================
       FILTROS + BUSCADOR
    ===================================================== */

    function aplicarFiltros(){

        const texto =
            buscador
                ? buscador.value.toLowerCase()
                : "";

        filas.forEach(fila => {

            const nombre =
                fila.innerText.toLowerCase();

            const grupo =
                fila.dataset.edad || "";

            let visible = true;

            /* BUSCADOR */

            if(
                texto &&
                !nombre.includes(texto)
            ){
                visible = false;
            }

            /* FILTRO */

            if(
                filtroActivo !== "todos" &&
                grupo !== filtroActivo
            ){
                visible = false;
            }

            fila.style.display =
                visible ? "" : "none";
        });
    }

    /* =====================================================
       BUSCADOR
    ===================================================== */

    if(buscador){

        buscador.addEventListener(
            "keyup",
            aplicarFiltros
        );

    }

    /* =====================================================
       FILTROS
    ===================================================== */

    filtros.forEach(btn => {

        btn.addEventListener("click", () => {

            filtros.forEach(item => {

                item.classList.remove(
                    "filter-chip--active"
                );

            });

            btn.classList.add(
                "filter-chip--active"
            );

            filtroActivo =
                btn.dataset.filter;

            aplicarFiltros();

        });

    });

    /* =====================================================
       MARCAR TODOS
    ===================================================== */

    if(btnCheckAll){

        btnCheckAll.addEventListener(
            "click",
            () => {

                document
                .querySelectorAll(
                    'input[name="asistencia[]"]'
                )
                .forEach(check => {

                    check.checked = true;

                });

            }
        );

    }

    /* =====================================================
       LIMPIAR TODO
    ===================================================== */

    if(btnUncheckAll){

        btnUncheckAll.addEventListener(
            "click",
            () => {

                document
                .querySelectorAll(
                    '.checks-grid input[type="checkbox"]'
                )
                .forEach(check => {

                    check.checked = false;

                });

            }
        );

    }

});