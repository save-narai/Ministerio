/* =========================================================
   MARCAR ASISTENCIA
   ========================================================= */

document.addEventListener("DOMContentLoaded", () => {

    const buscador =
        document.getElementById("buscadorJovenes");

    const filas =
        document.querySelectorAll(".attendance-card");

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

    function aplicarFiltros() {

        const texto = buscador
            ? buscador.value.trim().toLowerCase()
            : "";

        filas.forEach(fila => {

            const nombre =
                fila.querySelector("strong")
                    ?.textContent
                    .trim()
                    .toLowerCase() ?? "";

            const grupo =
                (fila.dataset.edad ?? "")
                    .trim()
                    .toLowerCase();

            let visible = true;


            /* ---------------------------------------------
               BUSCADOR
            --------------------------------------------- */

            if (
                texto &&
                !nombre.includes(texto)
            ) {

                visible = false;

            }


            /* ---------------------------------------------
               FILTRO DE GRUPO
            --------------------------------------------- */

            if (
                filtroActivo !== "todos" &&
                grupo !== filtroActivo
            ) {

                visible = false;

            }


            fila.style.display =
                visible ? "" : "none";

        });

    }


    /* =====================================================
       BUSCADOR
       ===================================================== */

    buscador?.addEventListener(
        "input",
        aplicarFiltros
    );


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
                (
                    btn.dataset.filter ||
                    "todos"
                )
                .trim()
                .toLowerCase();


            aplicarFiltros();

        });

    });


    /* =====================================================
       MARCAR TODOS
       
       IMPORTANTE:
       Marca TODOS los checklists de TODOS los jóvenes.
       
       No solamente asistencia[].
       ===================================================== */

btnCheckAll?.addEventListener(
    "click",
    () => {

        document
            .querySelectorAll(
                '.checks-grid input[name^="asistencia["]'
            )
            .forEach(check => {

                check.checked = true;

            });

    }
);

    /* =====================================================
       LIMPIAR TODOS
       
       Quita TODOS los checklists.
       ===================================================== */

 btnUncheckAll?.addEventListener(
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


    /* =====================================================
       INICIALIZAR FILTROS
       ===================================================== */

    aplicarFiltros();

});