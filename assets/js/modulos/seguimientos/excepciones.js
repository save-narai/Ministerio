document.addEventListener("DOMContentLoaded", () => {

    /* =====================================================
       ELEMENTOS DEL MODAL
    ===================================================== */

    const modal = document.getElementById(
        "modalExcepcionSeguimiento"
    );

    if (!modal) {
        return;
    }


    const nombre = document.getElementById(
        "excepcionJovenNombre"
    );

    const jovenId = document.getElementById(
        "excepcionJovenId"
    );

    const formulario = document.getElementById(
        "formExcepcionSeguimiento"
    );

    const motivo = document.getElementById(
        "motivoExcepcion"
    );

    const observaciones = document.getElementById(
        "observacionesExcepcion"
    );

    const cerrar = document.getElementById(
        "cerrarModalExcepcion"
    );

    const cancelar = document.getElementById(
        "cancelarExcepcion"
    );

    const overlay = modal.querySelector(
        ".gx-modal__overlay"
    );

    const titulo = document.getElementById(
        "modalExcepcionTitulo"
    );

    const descripcion = modal.querySelector(
        ".gx-modal__header p"
    );

    const botonSubmit = formulario?.querySelector(
        'button[type="submit"]'
    );


    /* =====================================================
       ESTADO
    ===================================================== */

    let modo = "crear";


    /* =====================================================
       RUTA DEL CONTROLADOR
    ===================================================== */

    const controladorExcepcion =
        window.excepcionSeguimientoUrl
        ||
        "../../controllers/excepcionSeguimientoController.php";


    /* =====================================================
       OBTENER ACTION
    ===================================================== */

    function obtenerActionInput() {

        if (!formulario) {
            return null;
        }

        let inputAction =
            formulario.querySelector(
                'input[name="action"]'
            );

        if (!inputAction) {

            inputAction =
                document.createElement("input");

            inputAction.type = "hidden";
            inputAction.name = "action";

            formulario.appendChild(
                inputAction
            );
        }

        return inputAction;
    }


    /* =====================================================
       OBTENER INPUT ID EXCEPCIÓN
    ===================================================== */

    function obtenerInputExcepcion() {

        if (!formulario) {
            return null;
        }

        let inputExcepcion =
            formulario.querySelector(
                'input[name="id"]'
            );

        if (!inputExcepcion) {

            inputExcepcion =
                document.createElement("input");

            inputExcepcion.type = "hidden";
            inputExcepcion.name = "id";

            formulario.appendChild(
                inputExcepcion
            );
        }

        return inputExcepcion;
    }


    /* =====================================================
       ABRIR MODAL
    ===================================================== */

    function abrirModal() {

        modal.setAttribute(
            "aria-hidden",
            "false"
        );

        modal.classList.add(
            "is-open"
        );

        document.body.classList.add(
            "modal-open"
        );
    }


    /* =====================================================
       CERRAR MODAL
    ===================================================== */

    function cerrarModal() {

        modal.setAttribute(
            "aria-hidden",
            "true"
        );

        modal.classList.remove(
            "is-open"
        );

        document.body.classList.remove(
            "modal-open"
        );

        modo = "crear";

        limpiarFormulario();

        configurarModoCrear();
    }


    /* =====================================================
       LIMPIAR FORMULARIO
    ===================================================== */

    function limpiarFormulario() {

        formulario?.reset();

        if (jovenId) {
            jovenId.value = "";
        }

        if (nombre) {
            nombre.textContent = "—";
        }

        const inputExcepcion =
            formulario?.querySelector(
                'input[name="id"]'
            );

        if (inputExcepcion) {
            inputExcepcion.value = "";
        }
    }


    /* =====================================================
       MODO CREAR
    ===================================================== */

    function configurarModoCrear() {

        modo = "crear";

        if (titulo) {

            titulo.textContent =
                "Registrar excepción";
        }

        if (descripcion) {

            descripcion.textContent =
                "Indica por qué este joven no tendrá seguimiento este mes.";
        }

        if (botonSubmit) {

            botonSubmit.disabled = false;

            botonSubmit.innerHTML = `
                <i class="fa-solid fa-check"></i>
                Registrar excepción
            `;
        }

        if (formulario) {

            formulario.action =
                controladorExcepcion;
        }

        const inputAction =
            obtenerActionInput();

        if (inputAction) {

            inputAction.value =
                "crear_excepcion_seguimiento";
        }
    }


    /* =====================================================
       MODO EDITAR
    ===================================================== */

    function configurarModoEditar() {

        modo = "editar";

        if (titulo) {

            titulo.textContent =
                "Editar excepción";
        }

        if (descripcion) {

            descripcion.textContent =
                "Actualiza la información de la excepción registrada.";
        }

        if (botonSubmit) {

            botonSubmit.disabled = false;

            botonSubmit.innerHTML = `
                <i class="fa-solid fa-floppy-disk"></i>
                Guardar cambios
            `;
        }

        if (formulario) {

            formulario.action =
                controladorExcepcion;
        }

        const inputAction =
            obtenerActionInput();

        if (inputAction) {

            inputAction.value =
                "actualizar_excepcion_seguimiento";
        }
    }


    /* =====================================================
       OBTENER EXCEPCIÓN DESDE EL SERVIDOR
    ===================================================== */

    async function obtenerExcepcion(id) {

        if (!id) {

            throw new Error(
                "No se recibió el ID de la excepción."
            );
        }

        const url =
            controladorExcepcion
            + "?action=obtener_excepcion_seguimiento"
            + "&id="
            + encodeURIComponent(id);

        console.log(
            "Consultando excepción:",
            url
        );

        const respuesta =
            await fetch(
                url,
                {
                    method: "GET",

                    headers: {
                        "Accept": "application/json"
                    },

                    credentials: "same-origin"
                }
            );

        const texto =
            await respuesta.text();

        let resultado;

        try {

            resultado =
                JSON.parse(texto);

        } catch (error) {

            console.error(
                "Respuesta del servidor:",
                texto
            );

            throw new Error(
                "El servidor no devolvió JSON válido."
            );
        }

        if (
            !respuesta.ok ||
            !resultado.success
        ) {

            throw new Error(
                resultado.message
                ||
                "No se pudo obtener la excepción."
            );
        }

        return resultado.data;
    }


    /* =====================================================
       CARGAR DATOS EN EL FORMULARIO
    ===================================================== */

    function cargarDatosExcepcion(data) {

        if (!data) {
            return;
        }

        /* ---------------------------------------------
           JOVEN
        --------------------------------------------- */

        if (jovenId) {

            jovenId.value =
                data.joven_id ?? "";
        }

        if (nombre) {

            nombre.textContent =
                data.joven_nombre || "—";
        }


        /* ---------------------------------------------
           MOTIVO
        --------------------------------------------- */

        if (motivo) {

            motivo.value =
                data.motivo ?? "";

            motivo.dispatchEvent(
                new Event(
                    "change",
                    {
                        bubbles: true
                    }
                )
            );
        }


        /* ---------------------------------------------
           OBSERVACIONES
        --------------------------------------------- */

        if (observaciones) {

            observaciones.value =
                data.observaciones ?? "";
        }


        /* ---------------------------------------------
           ID EXCEPCIÓN
        --------------------------------------------- */

        const inputExcepcion =
            obtenerInputExcepcion();

        if (inputExcepcion) {

            inputExcepcion.value =
                data.id ?? "";
        }
    }


    /* =====================================================
       BOTONES CHECKLIST - CREAR
    ===================================================== */

    document.querySelectorAll(
        ".btn-checklist"
    ).forEach(button => {

        button.addEventListener(
            "click",
            () => {

                const id =
                    button.dataset.jovenId;

                const nombreJoven =
                    button.dataset.jovenNombre;

                if (!id) {

                    console.error(
                        "El botón Checklist no tiene data-joven-id."
                    );

                    return;
                }

                limpiarFormulario();

                configurarModoCrear();


                if (jovenId) {

                    jovenId.value =
                        id;
                }


                if (nombre) {

                    nombre.textContent =
                        nombreJoven || "—";
                }


                abrirModal();
            }
        );
    });


    /* =====================================================
       BOTONES EDITAR EXCEPCIÓN
    ===================================================== */

    document.querySelectorAll(
        ".btn-editar-excepcion"
    ).forEach(button => {

        button.addEventListener(
            "click",
            async () => {

                const id =
                    button.dataset.id;

                const jovenIdInicial =
                    button.dataset.jovenId;

                const jovenNombreInicial =
                    button.dataset.jovenNombre;


                if (!id) {

                    alert(
                        "No se encontró el ID de la excepción."
                    );

                    return;
                }


                /* -----------------------------------------
                   LIMPIAR
                ----------------------------------------- */

                limpiarFormulario();


                /* -----------------------------------------
                   CONFIGURAR EDICIÓN
                ----------------------------------------- */

                configurarModoEditar();


                /* -----------------------------------------
                   MOSTRAR JOVEN INMEDIATAMENTE
                ----------------------------------------- */

                if (jovenId) {

                    jovenId.value =
                        jovenIdInicial || "";
                }

                if (nombre) {

                    nombre.textContent =
                        jovenNombreInicial || "—";
                }


                /* -----------------------------------------
                   GUARDAR ID EXCEPCIÓN
                ----------------------------------------- */

                const inputExcepcion =
                    obtenerInputExcepcion();

                if (inputExcepcion) {

                    inputExcepcion.value =
                        id;
                }


                /* -----------------------------------------
                   ABRIR MODAL
                ----------------------------------------- */

                abrirModal();


                /* -----------------------------------------
                   CARGANDO
                ----------------------------------------- */

                if (botonSubmit) {

                    botonSubmit.disabled = true;

                    botonSubmit.innerHTML = `
                        <i class="fa-solid fa-spinner fa-spin"></i>
                        Cargando...
                    `;
                }


                try {

                    const data =
                        await obtenerExcepcion(id);

                    cargarDatosExcepcion(data);

                } catch (error) {

                    console.error(
                        "Error al cargar excepción:",
                        error
                    );

                    alert(
                        error.message
                        ||
                        "No se pudo cargar la excepción."
                    );

                    return;

                } finally {

                    if (
                        botonSubmit
                        &&
                        modal.classList.contains(
                            "is-open"
                        )
                    ) {

                        botonSubmit.disabled = false;

                        botonSubmit.innerHTML = `
                            <i class="fa-solid fa-floppy-disk"></i>
                            Guardar cambios
                        `;
                    }
                }
            }
        );
    });


    /* =====================================================
       CERRAR MODAL
    ===================================================== */

    cerrar?.addEventListener(
        "click",
        cerrarModal
    );


    cancelar?.addEventListener(
        "click",
        cerrarModal
    );


    overlay?.addEventListener(
        "click",
        cerrarModal
    );


    /* =====================================================
       ESC
    ===================================================== */

    document.addEventListener(
        "keydown",
        event => {

            if (
                event.key === "Escape"
                &&
                modal.classList.contains(
                    "is-open"
                )
            ) {

                cerrarModal();
            }
        }
    );


    /* =====================================================
       INICIALIZAR
    ===================================================== */

    configurarModoCrear();

});