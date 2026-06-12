
/* =========================================================
   DATATABLE EXPORT
========================================================= */

function initExportButtons(table){

    /* =====================================================
       VALIDATION
    ===================================================== */

    if(!table){
        return;
    }

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

}

/* =====================================================
       volt doc
    ===================================================== */



document
.getElementById('exportWord')
?.addEventListener('click', () => {

    const tablaHtml =
    table.table().node();

    const contenido = `
        <html>
        <head>
            <meta charset="utf-8">
            <title>Registros</title>
        </head>
        <body>
            ${tablaHtml.outerHTML}
        </body>
        </html>
    `;

    const blob = new Blob(
        [contenido],
        {
            type:'application/msword'
        }
    );

    const enlace =
    document.createElement('a');

    enlace.href =
    URL.createObjectURL(blob);

    enlace.download =
    'registros.doc';

    enlace.click();

});