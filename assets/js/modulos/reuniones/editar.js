document.addEventListener("DOMContentLoaded", () => {

    const tipo = document.getElementById("tipo");
    const grupo = document.getElementById("grupoTipoPersonalizado");
    const personalizado = document.getElementById("tipoPersonalizado");

    if (tipo && grupo && personalizado) {

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

    }


    /* ==========================================================
       FASE 7 — CAMPOS DE DISCIPULADO (CICLO / CLASE / MODALIDAD)

       Mismo mecanismo sin AJAX que en crear.js. La diferencia es
       que aquí el <select> de clase ya viene prellenado desde
       PHP con las clases del ciclo actualmente vinculado (si
       existe), así que solo se reconstruye cuando el usuario
       cambia el ciclo manualmente.
    ========================================================== */

    const grupoCiclo = document.getElementById("grupoCicloDiscipulado");
    const grupoClase = document.getElementById("grupoClaseDiscipulado");
    const grupoModalidad = document.getElementById("grupoModalidadDiscipulado");
    const grupoRecuperacion = document.getElementById("grupoRecuperacionDiscipulado");
    const selectCiclo = document.getElementById("cicloDiscipulado");
    const selectClase = document.getElementById("claseDiscipulado");

    if (!tipo || !grupoCiclo || !grupoClase || !grupoModalidad || !grupoRecuperacion || !selectCiclo || !selectClase) {
        return;
    }

    function actualizarCamposDiscipulado() {

        const esDiscipulado = tipo.value === "DISCIPULADO";

        [grupoCiclo, grupoClase, grupoModalidad, grupoRecuperacion].forEach((el) => {
            el.style.display = esDiscipulado ? "block" : "none";
        });

        if (!esDiscipulado) {
            selectCiclo.value = "";
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

    /* Al cambiar el ciclo manualmente sí se reconstruye la
       lista de clases desde cero (se pierde la preselección
       del servidor, que ya cumplió su propósito al cargar). */

    selectCiclo.addEventListener("change", actualizarClasesDelCiclo);

});