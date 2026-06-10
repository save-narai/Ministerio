
/* =========================================================
   DATATABLE EXPORT
========================================================= */

function initExportButtons(tableId){

    const table = $(tableId).DataTable({

        dom:
        't<"datatable-footer"<"datatable-info"i><"datatable-pagination"p>>',

        responsive:true,

        autoWidth:false,

        pageLength:8,

        language:{

            search:"",

            searchPlaceholder:"Buscar joven...",

            lengthMenu:"Mostrar _MENU_",

            info:"Mostrando _START_ a _END_ de _TOTAL_ jóvenes",

            paginate:{
                previous:"‹",
                next:"›"
            },

            emptyTable:"No hay registros disponibles"
        },

        buttons:[

            {
                extend:'pdfHtml5',
                className:'buttons-pdf',
                title:'Jovenes'
            },

            {
                extend:'excelHtml5',
                className:'buttons-excel',
                title:'Jovenes'
            },

            {
                extend:'csvHtml5',
                className:'buttons-csv',
                title:'Jovenes'
            },

            {
                extend:'print',
                className:'buttons-print',
                title:'Jovenes'
            }
        ]
    });

    /* =====================================================
       PDF
    ===================================================== */

    document
    .getElementById('exportPdf')
    ?.addEventListener('click', () => {

        table.button('.buttons-pdf').trigger();
    });

    /* =====================================================
       EXCEL
    ===================================================== */

    document
    .getElementById('exportExcel')
    ?.addEventListener('click', () => {

        table.button('.buttons-excel').trigger();
    });

    /* =====================================================
       CSV
    ===================================================== */

    document
    .getElementById('exportCsv')
    ?.addEventListener('click', () => {

        table.button('.buttons-csv').trigger();
    });

    /* =====================================================
       PRINT
    ===================================================== */

    document
    .getElementById('exportPrint')
    ?.addEventListener('click', () => {

        table.button('.buttons-print').trigger();
    });

    return table;
}