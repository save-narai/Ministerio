"use strict";

/* ==========================================================
   PROFESORES POR CLASE — DISCIPULADO
   ----------------------------------------------------------
   Autoguardado en segundo plano (fetch) para el select de
   profesor de cada clase. Reutiliza la acción existente
   'asignar_profesor_clase_discipulado' de
   discipuladoClaseController.php — el backend ya distingue
   peticiones AJAX por el header X-Requested-With + Accept:
   application/json y responde en JSON en vez de redirigir
   (ver controller.php).
========================================================== */

(() => {

    const wrapper =
        document.querySelector(
            "[data-discipulado-profesores]"
        );

    if (!wrapper) {
        return;
    }

    const controllerClase =
        window.BASE_URL +
        "/controllers/discipuladoClaseController.php";


    /* ======================================================
       ESTADO DEL CSRF (igual que discipulado-matriz.js)
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


    function marcarGuardando(elemento) {

        elemento.classList.remove("is-saved", "is-error");
        elemento.classList.add("is-saving");

    }

    function marcarGuardado(elemento) {

        elemento.classList.remove("is-saving", "is-error");
        elemento.classList.add("is-saved");

        setTimeout(() => {
            elemento.classList.remove("is-saved");
        }, 1500);

    }

    function marcarError(elemento) {

        elemento.classList.remove("is-saving", "is-saved");
        elemento.classList.add("is-error");

        setTimeout(() => {
            elemento.classList.remove("is-error");
        }, 2500);

    }


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
       SELECT DE PROFESOR
    ====================================================== */

    wrapper.addEventListener("change", (evento) => {

        const select = evento.target.closest(
            "[data-profesor-select]"
        );

        if (!select) {
            return;
        }

        const fila = select.closest("[data-clase-id]");

        marcarGuardando(select);

        enviar(controllerClase, {

            action: "asignar_profesor_clase_discipulado",
            ciclo_id: wrapper.dataset.cicloId,
            id: fila.dataset.claseId,
            profesor_id: select.value

        })
            .then(() => {
                marcarGuardado(select);
            })
            .catch((error) => {

                marcarError(select);

                avisar(error.message, "error");

            });

    });

})();
