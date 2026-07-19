document.addEventListener("DOMContentLoaded", () => {

    /* ======================================
       DATATABLE
    ====================================== */

    if (
        typeof $ !== "undefined" &&
        $.fn.DataTable &&
        document.querySelector("#tablaJovenes")
    ) {

        const tabla = $("#tablaJovenes").DataTable({

            pageLength: 8,
            ordering: true,
            searching: true,
            paging: true,
            info: true,
            lengthChange: false,
            responsive: false,
            autoWidth: false,

            dom: 'Brt<"datatable-footer"<"datatable-info"i><"datatable-pagination"p>>',

            buttons: [

                {
                    extend: "pdfHtml5",
                    className: "buttons-pdf",
                    title: "Jóvenes"
                },

                {
                    extend: "excelHtml5",
                    className: "buttons-excel",
                    title: "Jóvenes"
                },

                {
                    extend: "csvHtml5",
                    className: "buttons-csv",
                    title: "Jóvenes"
                },

                {
                    extend: "print",
                    className: "buttons-print",
                    title: "Jóvenes"
                }

            ],

            language: {

                search: "",

                info:
                    "Mostrando _START_ a _END_ de _TOTAL_ registros",

                infoEmpty:
                    "No hay registros disponibles",

                emptyTable:
                    "No hay datos disponibles",

                zeroRecords:
                    "No se encontraron resultados",

                paginate: {

                    previous: "‹",

                    next: "›"

                }

            }

        });

        /* ======================================
           BUSCADOR
        ====================================== */

        const buscador = document.getElementById("buscador");

        if (buscador) {

            buscador.addEventListener("keyup", function () {

                tabla.search(this.value).draw();

            });

        }

        /* ======================================
           EXPORTACIONES
        ====================================== */

        document.getElementById("exportPdf")
            ?.addEventListener("click", () => {

                tabla.button(".buttons-pdf").trigger();

            });

        document.getElementById("exportExcel")
            ?.addEventListener("click", () => {

                tabla.button(".buttons-excel").trigger();

            });

        document.getElementById("exportCsv")
            ?.addEventListener("click", () => {

                tabla.button(".buttons-csv").trigger();

            });

        document.getElementById("exportPrint")
            ?.addEventListener("click", () => {

                tabla.button(".buttons-print").trigger();

            });

    }

    /* ======================================
       TOOLTIPS
    ====================================== */

    document
        .querySelectorAll("[data-tooltip]")
        .forEach(btn => {

            btn.title = btn.dataset.tooltip;

        });

    /* ======================================
       EVITAR DOBLE ENVÍO
    ====================================== */

    document
        .querySelectorAll("form")
        .forEach(form => {

            form.addEventListener("submit", () => {

                const boton = form.querySelector("button");

                if (boton) {

                    boton.disabled = true;

                }

            });

        });

});