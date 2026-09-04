window.normalizarNombrePersona = function (valor) {
    return valor
        .trim()
        .replace(/\s+/gu, ' ')
        .toLocaleLowerCase('es-CO')
        .replace(/(^|[\s'-])\p{L}/gu, (letra) => letra.toLocaleUpperCase('es-CO'));
};

window.inicializarNormalizacionNombre = function (formulario) {
    if (!formulario) return;

    const campo = formulario.querySelector('[name="nombre_completo"]');
    if (!campo) return;

    const normalizar = () => {
        campo.value = window.normalizarNombrePersona(campo.value);
    };

    campo.addEventListener('blur', normalizar);
    formulario.addEventListener('submit', normalizar);
};
