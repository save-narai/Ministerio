
/* =========================================================
   SEARCH
========================================================= */

function initSearch(inputId, table){

    const buscador =
    document.getElementById(inputId);

    if(!buscador || !table){
        return;
    }

    buscador.addEventListener('keyup', function(){

        table.search(this.value).draw();

    });
}