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

});