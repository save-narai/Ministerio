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
                    .toLowerCase() ?? "";

            const grupo =
                fila.dataset.edad ?? "";

            let visible = true;

            if (
                texto &&
                !nombre.includes(texto)
            ) {

                visible = false;

            }

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

            filtros.forEach(item =>
                item.classList.remove(
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

    /* =====================================================
       MARCAR TODOS
    ===================================================== */

    btnCheckAll?.addEventListener(
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

    /* =====================================================
       LIMPIAR SELECCIÓN
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

});