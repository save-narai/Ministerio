document.addEventListener("DOMContentLoaded", () => {

    const tipo = document.getElementById("tipo");
    const grupo = document.getElementById("grupoTipoPersonalizado");
    const personalizado = document.getElementById("tipoPersonalizado");

    function actualizarCampo() {

        const mostrar = tipo.value === "OTRO";

        grupo.style.display = mostrar ? "block" : "none";

        personalizado.required = mostrar;

        if (!mostrar) {
            personalizado.value = "";
        }

    }

    actualizarCampo();

    tipo.addEventListener(
        "change",
        actualizarCampo
    );


    /* ==========================================================
       FASE 7 — CAMPOS DE DISCIPULADO (CICLO / CLASE / MODALIDAD)

       Sin AJAX: las clases de cada ciclo ya vienen embebidas
       en `clasesPorCicloDiscipulado` (impreso por la vista).
       Este script solo muestra/oculta los grupos y filtra el
       <select> de clase según el ciclo elegido.
    ========================================================== */

    const grupoCiclo = document.getElementById("grupoCicloDiscipulado");
    const grupoClase = document.getElementById("grupoClaseDiscipulado");
    const grupoModalidad = document.getElementById("grupoModalidadDiscipulado");
    const grupoRecuperacion = document.getElementById("grupoRecuperacionDiscipulado");
    const selectCiclo = document.getElementById("cicloDiscipulado");
    const selectClase = document.getElementById("claseDiscipulado");

    function actualizarCamposDiscipulado() {

        const esDiscipulado = tipo.value === "DISCIPULADO";

        [grupoCiclo, grupoClase, grupoModalidad, grupoRecuperacion].forEach((el) => {
            el.style.display = esDiscipulado ? "block" : "none";
        });

        if (!esDiscipulado) {
            selectCiclo.value = "";
            actualizarClasesDelCiclo();
        }

    }

    function actualizarClasesDelCiclo() {

        const cicloId = selectCiclo.value;

        const clases =
            (typeof clasesPorCicloDiscipulado !== "undefined" && cicloId)
                ? (clasesPorCicloDiscipulado[cicloId] || [])
                : [];

        selectClase.innerHTML = "";

        if (!cicloId) {

            selectClase.innerHTML = '<option value="">Selecciona primero un ciclo</option>';
            return;

        }

        if (clases.length === 0) {

            selectClase.innerHTML = '<option value="">Este ciclo no tiene clases configuradas</option>';
            return;

        }

        selectClase.innerHTML = '<option value="">Sin clase asociada</option>';

        clases.forEach((clase) => {

            const opt = document.createElement("option");
            opt.value = clase.id;
            opt.textContent = clase.nombre;
            selectClase.appendChild(opt);

        });

    }

    actualizarCamposDiscipulado();

    tipo.addEventListener("change", actualizarCamposDiscipulado);

    selectCiclo.addEventListener("change", actualizarClasesDelCiclo);

});