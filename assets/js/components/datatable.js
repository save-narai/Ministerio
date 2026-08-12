/* =========================================================
   DATATABLE GLOBAL
========================================================= */

function initDataTable(
    tableId,
    options = {}
) {

    /* =====================================================
       VALIDATION
    ===================================================== */

    if (
        typeof $ === "undefined" ||
        !$.fn ||
        !$.fn.DataTable
    ) {
        return null;
    }


    /* =====================================================
       VALIDATE TABLE
    ===================================================== */

    const $table = $(tableId);

    if (!$table.length) {
        return null;
    }


    /* =====================================================
       AVOID DOUBLE INIT
    ===================================================== */

    if (
        $.fn.DataTable.isDataTable(
            tableId
        )
    ) {

        return $table.DataTable();

    }


    /* =====================================================
       DEFAULT CONFIGURATION
    ===================================================== */

    const config = {

        /* =================================================
           PAGINATION
        ================================================= */

        pageLength: 8,

        lengthChange: false,

        paging: true,

        info: true,


        /* =================================================
           ORDERING
        ================================================= */

        ordering: true,


        /* =================================================
           SEARCH
        ================================================= */

        searching: true,


        /* =================================================
           RESPONSIVE
        ================================================= */

        responsive: false,

        autoWidth: false,


        /* =================================================
           PROCESSING
        ================================================= */

        processing: false,


        /* =================================================
           DOM
        ================================================= */

        dom:
            'Brt<"datatable-footer"<"datatable-info"i><"datatable-pagination"p>>',


        /* =================================================
           EXPORT BUTTONS
        ================================================= */

        buttons: [

            {
                extend: "pdfHtml5",

                className: "buttons-pdf",

                title: "Registros"
            },


            {
                extend: "excelHtml5",

                className: "buttons-excel",

                title: "Registros"
            },


            {
                extend: "csvHtml5",

                className: "buttons-csv",

                title: "Registros"
            },


            {
                extend: "print",

                className: "buttons-print",

                title: "Registros"
            }

        ],


        /* =================================================
           LANGUAGE
        ================================================= */

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

            lengthMenu:
                "Mostrar _MENU_ registros",

            paginate: {

                previous: "‹",

                next: "›"

            }

        }

    };


    /* =====================================================
       MERGE OPTIONS
       
       Las opciones específicas de cada tabla
       sobrescriben solamente lo que necesiten.
    ===================================================== */

    Object.assign(
        config,
        options
    );


    /* =====================================================
       INIT DATATABLE
    ===================================================== */

    return $table.DataTable(
        config
    );

}