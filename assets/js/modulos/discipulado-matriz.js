"use strict";

/* ==========================================================
   MATRIZ DE PARTICIPANTES — DISCIPULADO
   ----------------------------------------------------------
   Autoguardado en segundo plano (fetch) para:
     - checkbox de clase completada/pendiente
     - select de modalidad principal
     - input de observación (con debounce)
     - acción retirar / reactivar

   Reutiliza las acciones existentes de
   discipuladoProgresoController.php y
   discipuladoInscripcionController.php. El backend ya
   distingue peticiones AJAX por el header
   X-Requested-With + Accept: application/json y responde
   en JSON en vez de redirigir (ver controller.php).
========================================================== */

(() => {

    const wrapper =
        document.querySelector(
            "[data-discipulado-matriz]"
        );

    if (!wrapper) {
        return;
    }

    const controllerProgreso =
        window.BASE_URL +
        "/controllers/discipuladoProgresoController.php";

    const controllerInscripcion =
        window.BASE_URL +
        "/controllers/discipuladoInscripcionController.php";


    /* ======================================================
       RECALCULAR "PENDIENTES" DE UNA FILA
       ------------------------------------------------------
       Cuenta las casillas de clase (no de repaso) que siguen
       sin marcar dentro de la fila y actualiza la celda
       [data-pendientes-valor], sin esperar respuesta del
       servidor.
    ====================================================== */

    function actualizarPendientes(fila) {

        const celdaPendientes = fila.querySelector(
            "[data-pendientes-valor]"
        );

        if (!celdaPendientes) {
            return;
        }

        const casillasClase = fila.querySelectorAll(
            "[data-clase-checkbox]"
        );

        const pendientes = Array.from(casillasClase).filter(
            (casilla) => !casilla.checked
        ).length;

        celdaPendientes.textContent = String(pendientes);

    }


    /* ======================================================
       ESTADO DEL CSRF
       ------------------------------------------------------
       validarCsrf() regenera el token en cada petición
       exitosa; el backend lo reenvía en la respuesta JSON
       (campo csrf_token) para que la siguiente petición de
       esta misma página siga siendo válida.
    ====================================================== */

    const csrf = {

        token: window.CSRF_TOKEN || "",

        update(nuevoToken) {

            if (nuevoToken) {
                this.token = nuevoToken;
                window.CSRF_TOKEN = nuevoToken;
            }

        }

    };


    /* ======================================================
       DEBOUNCE
    ====================================================== */

    function debounce(fn, delay = 500) {

        let timeoutId = null;

        return function debounced(...args) {

            clearTimeout(timeoutId);

            timeoutId = setTimeout(
                () => fn.apply(this, args),
                delay
            );

        };

    }


    /* ======================================================
       INDICADOR DE GUARDADO POR CELDA
    ====================================================== */

    function marcarGuardando(elemento) {

        elemento.classList.remove(
            "is-saved",
            "is-error"
        );

        elemento.classList.add("is-saving");

    }

    function marcarGuardado(elemento) {

        elemento.classList.remove(
            "is-saving",
            "is-error"
        );

        elemento.classList.add("is-saved");

        setTimeout(() => {
            elemento.classList.remove("is-saved");
        }, 1500);

    }

    function marcarError(elemento) {

        elemento.classList.remove(
            "is-saving",
            "is-saved"
        );

        elemento.classList.add("is-error");

        setTimeout(() => {
            elemento.classList.remove("is-error");
        }, 2500);

    }


    /* ======================================================
       PETICIÓN AJAX GENÉRICA
    ====================================================== */

    async function enviar(url, datos) {

        const cuerpo = new URLSearchParams({
            ...datos,
            csrf_token: csrf.token
        });

        const respuesta = await fetch(url, {

            method: "POST",

            headers: {
                "Content-Type":
                    "application/x-www-form-urlencoded",
                "X-Requested-With": "XMLHttpRequest",
                "Accept": "application/json"
            },

            body: cuerpo,
            credentials: "same-origin"

        });

        let payload = null;

        try {
            payload = await respuesta.json();
        } catch (error) {
            payload = null;
        }

        if (payload && payload.csrf_token) {
            csrf.update(payload.csrf_token);
        }

        if (!respuesta.ok || !payload || payload.success === false) {

            const mensaje =
                (payload && payload.message) ||
                "No se pudo guardar el cambio.";

            throw new Error(mensaje);

        }

        return payload;

    }

    function avisar(mensaje, tipo = "success") {

        if (window.GXAlert && window.GXAlert[tipo]) {
            window.GXAlert[tipo](mensaje);
            return;
        }

        if (tipo === "error") {
            console.error(mensaje);
        }

    }


    /* ======================================================
       1. CHECKBOX DE CLASE (pendiente ↔ completada)
    ====================================================== */

    wrapper.addEventListener("change", (evento) => {

        const casilla = evento.target.closest(
            "[data-clase-checkbox]"
        );

        if (!casilla) {
            return;
        }

        const celda = casilla.closest(
            "[data-matriz-celda]"
        );

        const fila = casilla.closest(
            "[data-inscripcion-id]"
        );

        const modalidadSelect = fila.querySelector(
            "[data-modalidad-select]"
        );

        const modalidadActual =
            (modalidadSelect && modalidadSelect.value) ||
            "PRESENCIAL";

        const estado =
            casilla.checked
                ? modalidadActual
                : "PENDIENTE";

        marcarGuardando(celda);

        enviar(controllerProgreso, {

            action: "actualizar_checklist_ciclo_discipulado",
            ciclo_id: wrapper.dataset.cicloId,
            inscripcion_id: fila.dataset.inscripcionId,
            clase_id: casilla.dataset.claseId,
            estado

        })
            .then(() => {

                marcarGuardado(celda);

                celda.classList.toggle(
                    "discipulado-cell-completa",
                    casilla.checked
                );

                // Si la celda ya tenía marca de recuperación
                // y el usuario la desmarca desde el checkbox
                // simple, esa marca se pierde (se reinicia
                // como clase normal). Es la única forma en
                // que una casilla "simple" puede representar
                // ambos casos sin un segundo control por celda.
                celda.classList.remove(
                    "discipulado-cell-recuperacion"
                );

                actualizarPendientes(fila);

            })
            .catch((error) => {

                // Revertir el checkbox visualmente si falló.
                casilla.checked = !casilla.checked;

                marcarError(celda);

                avisar(error.message, "error");

            });

    });


    /* ======================================================
       2. CHECKBOX DE REPASO (1 o 2)
       ------------------------------------------------------
       Casilla independiente de cualquier clase puntual — ver
       migración 20260902_repasos_checklist_asistencia.sql.
    ====================================================== */

    wrapper.addEventListener("change", (evento) => {

        const casilla = evento.target.closest(
            "[data-repaso-checkbox]"
        );

        if (!casilla) {
            return;
        }

        const fila = casilla.closest(
            "[data-inscripcion-id]"
        );

        marcarGuardando(casilla);

        enviar(controllerInscripcion, {

            action: "actualizar_repaso_inscripcion_discipulado",
            ciclo_id: wrapper.dataset.cicloId,
            id: fila.dataset.inscripcionId,
            numero_repaso: casilla.dataset.numeroRepaso,
            valor: casilla.checked ? "1" : "0"

        })
            .then(() => {
                marcarGuardado(casilla);
            })
            .catch((error) => {

                casilla.checked = !casilla.checked;

                marcarError(casilla);

                avisar(error.message, "error");

            });

    });


    /* ======================================================
       3. SELECT DE MODALIDAD PRINCIPAL
    ====================================================== */

    wrapper.addEventListener("change", (evento) => {

        const select = evento.target.closest(
            "[data-modalidad-select]"
        );

        if (!select) {
            return;
        }

        const fila = select.closest(
            "[data-inscripcion-id]"
        );

        marcarGuardando(select);

        enviar(controllerInscripcion, {

            action: "cambiar_modalidad_inscripcion_discipulado",
            ciclo_id: wrapper.dataset.cicloId,
            id: fila.dataset.inscripcionId,
            modalidad_principal: select.value

        })
            .then(() => {
                marcarGuardado(select);
            })
            .catch((error) => {

                marcarError(select);

                avisar(error.message, "error");

            });

    });


    /* ======================================================
       4. OBSERVACIONES (con debounce, guarda al salir del
          campo o tras una pausa al escribir)
    ====================================================== */

    const guardarObservacion = debounce((input) => {

        const fila = input.closest(
            "[data-inscripcion-id]"
        );

        const valor = input.value.trim();

        if (valor === input.dataset.ultimoValor) {
            return;
        }

        if (valor === "") {
            input.dataset.ultimoValor = "";
            return;
        }

        marcarGuardando(input);

        enviar(controllerInscripcion, {

            action:
                "agregar_observacion_inscripcion_discipulado",
            ciclo_id: wrapper.dataset.cicloId,
            id: fila.dataset.inscripcionId,
            observacion: valor

        })
            .then(() => {

                input.dataset.ultimoValor = valor;

                marcarGuardado(input);

            })
            .catch((error) => {

                marcarError(input);

                avisar(error.message, "error");

            });

    }, 600);

    wrapper.addEventListener("input", (evento) => {

        const input = evento.target.closest(
            "[data-observacion-input]"
        );

        if (!input) {
            return;
        }

        guardarObservacion(input);

    });

    wrapper.addEventListener("blur", (evento) => {

        const input =
            evento.target.closest?.(
                "[data-observacion-input]"
            );

        if (!input) {
            return;
        }

        // Al salir del campo, guarda de inmediato sin
        // esperar el debounce.
        guardarObservacion(input);

    }, true);


    /* ======================================================
       5. RETIRAR / REACTIVAR (acción discreta)
       ------------------------------------------------------
       Ya no hay botón para esto en la tabla de Asistencia
       (se movió al perfil del joven, participantes/ver.php);
       este listener queda sin efecto ahí pero no estorba si
       algún día se reincorpora un botón con este atributo.
    ====================================================== */

    wrapper.addEventListener("click", (evento) => {

        const boton = evento.target.closest(
            "[data-toggle-estado]"
        );

        if (!boton) {
            return;
        }

        const fila = boton.closest(
            "[data-inscripcion-id]"
        );

        const estadoActual = boton.dataset.toggleEstado;

        const estadoNuevo =
            estadoActual === "ACTIVO"
                ? "CANCELADO"
                : "ACTIVO";

        const confirmacion =
            estadoNuevo === "CANCELADO"
                ? "¿Retirar a este joven del ciclo?"
                : "¿Reactivar la inscripción de este joven?";

        if (!window.confirm(confirmacion)) {
            return;
        }

        boton.disabled = true;

        enviar(controllerInscripcion, {

            action: "cambiar_estado_inscripcion_discipulado",
            ciclo_id: wrapper.dataset.cicloId,
            id: fila.dataset.inscripcionId,
            estado: estadoNuevo

        })
            .then(() => {

                fila.dataset.estado = estadoNuevo;

                boton.dataset.toggleEstado = estadoNuevo;

                boton.classList.toggle(
                    "btn-delete",
                    estadoNuevo === "ACTIVO"
                );

                boton.classList.toggle(
                    "btn-complete",
                    estadoNuevo === "CANCELADO"
                );

                fila.classList.toggle(
                    "discipulado-fila-retirada",
                    estadoNuevo === "CANCELADO"
                );

                const controlesFila = fila.querySelectorAll(
                    "[data-clase-checkbox], [data-modalidad-select], [data-observacion-input]"
                );

                controlesFila.forEach((control) => {
                    control.disabled = estadoNuevo === "CANCELADO";
                });

                boton.setAttribute(
                    "data-tooltip",
                    estadoNuevo === "ACTIVO"
                        ? "Retirar del ciclo"
                        : "Reactivar inscripción"
                );

                boton.querySelector("i")?.setAttribute(
                    "class",
                    estadoNuevo === "ACTIVO"
                        ? "fa-solid fa-user-slash"
                        : "fa-solid fa-user-check"
                );

                avisar(
                    estadoNuevo === "CANCELADO"
                        ? "Joven retirado del ciclo."
                        : "Inscripción reactivada."
                );

                boton.disabled = false;

            })
            .catch((error) => {

                boton.disabled = false;

                avisar(error.message, "error");

            });

    });

})();
