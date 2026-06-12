/* =========================================================
   DATATABLE
========================================================= */

function initDataTable(tableId){

    /* =====================================================
       VALIDATION
    ===================================================== */

    if(
        typeof $ === "undefined" ||
        !$.fn.DataTable
    ){
        return null;
    }

    /* =====================================================
       AVOID DOUBLE INIT
    ===================================================== */

    if($.fn.DataTable.isDataTable(tableId)){

        return $(tableId).DataTable();
    }

    /* =====================================================
       INIT
    ===================================================== */

    return $(tableId).DataTable({

        /* =================================================
           CONFIG
        ================================================= */

        pageLength:8,

        ordering:true,

        searching:true,

        paging:true,

        info:true,

        lengthChange:false,

        responsive:false,

        autoWidth:false,

        processing:false,

        /* =================================================
           DOM
        ================================================= */

        dom:
        'Brt<"datatable-footer"<"datatable-info"i><"datatable-pagination"p>>',

        /* =================================================
           EXPORT BUTTONS
        ================================================= */

        buttons:[

            {
                extend:'pdfHtml5',
                className:'buttons-pdf',
                title:'Registros'
            },

            {
                extend:'excelHtml5',
                className:'buttons-excel',
                title:'Registros'
            },

            {
                extend:'csvHtml5',
                className:'buttons-csv',
                title:'Registros'
            },

            {
                extend:'print',
                className:'buttons-print',
                title:'Registros'
            }

        ],

        /* =================================================
           LANGUAGE
        ================================================= */

        language:{

            search:"",

            info:
            "Mostrando _START_ a _END_ de _TOTAL_ registros",

            infoEmpty:
            "No hay registros disponibles",

            emptyTable:
            "No hay datos disponibles",

            zeroRecords:
            "No se encontraron resultados",

            paginate:{
                previous:"‹",
                next:"›"
            }
        }
    });
}