/* ==========================================================
   GX NOTIFICATIONS
   Sistema Global de Alertas
========================================================== */

"use strict";

class GXNotifications {

    constructor() {

        this.duration = 5000;

        this.alerts = [];

        this.init();

    }


    /* ======================================================
       INIT
    ====================================================== */

    init() {

        this.alerts = [
            ...document.querySelectorAll(".gx-alert")
        ];

        if (!this.alerts.length) return;

        this.alerts.forEach(alert => {

            this.prepare(alert);

        });

    }


    /* ======================================================
       PREPARAR ALERTA
    ====================================================== */

    prepare(alert) {

        if (!alert || alert.dataset.gxInitialized === "true") {
            return;
        }

        alert.dataset.gxInitialized = "true";

        const closeButton =
            alert.querySelector(".gx-alert-close");

        const progress =
            alert.querySelector(".gx-alert-progress");


        let timer = null;

        let remaining = this.duration;

        let startTime = Date.now();

        let paused = false;


        /* ==================================================
           ACTUALIZAR BARRA
        ================================================== */

        const updateProgress = () => {

            if (!progress) return;

            const percentage =
                Math.max(
                    0,
                    Math.min(
                        100,
                        (remaining / this.duration) * 100
                    )
                );

            progress.style.setProperty(
                "--gx-progress",
                `${percentage}%`
            );

        };


        /* ==================================================
           INICIAR TEMPORIZADOR
        ================================================== */

        const startTimer = () => {

            clearTimeout(timer);

            if (remaining <= 0) {

                this.hide(alert);

                return;
            }

            startTime = Date.now();

            paused = false;

            updateProgress();

            timer = setTimeout(() => {

                remaining = 0;

                updateProgress();

                this.hide(alert);

            }, remaining);

        };


        /* ==================================================
           PAUSAR
        ================================================== */

        const pauseTimer = () => {

            if (paused) return;

            paused = true;

            clearTimeout(timer);

            remaining -= Date.now() - startTime;

            remaining = Math.max(
                0,
                remaining
            );

            updateProgress();

        };


        /* ==================================================
           REANUDAR
        ================================================== */

        const resumeTimer = () => {

            if (!paused) return;

            paused = false;

            if (remaining <= 0) {

                this.hide(alert);

                return;

            }

            startTimer();

        };


        /* ==================================================
           INICIAR
        ================================================== */

        updateProgress();

        startTimer();


        /* ==================================================
           HOVER
        ================================================== */

        alert.addEventListener(
            "mouseenter",
            pauseTimer
        );

        alert.addEventListener(
            "mouseleave",
            resumeTimer
        );


        /* ==================================================
           BOTÓN CERRAR
        ================================================== */

        if (closeButton) {

            closeButton.addEventListener(
                "click",
                (event) => {

                    event.preventDefault();

                    event.stopPropagation();

                    clearTimeout(timer);

                    this.hide(alert);

                }
            );

        }

    }


    /* ======================================================
       OCULTAR ALERTA
    ====================================================== */

    hide(alert) {

        if (!alert) return;

        if (
            alert.classList.contains("gx-hide") ||
            alert.dataset.gxRemoving === "true"
        ) {

            return;

        }

        alert.dataset.gxRemoving = "true";

        alert.classList.add("gx-hide");


        setTimeout(() => {

            this.remove(alert);

        }, 350);

    }


    /* ======================================================
       ELIMINAR DEL DOM
    ====================================================== */

    remove(alert) {

        if (!alert) return;

        alert.remove();

        this.alerts =
            this.alerts.filter(
                item => item !== alert
            );

        this.updateContainer();

    }


    /* ======================================================
       ACTUALIZAR CONTENEDOR
    ====================================================== */

    updateContainer() {

        const container =
            document.querySelector(
                ".gx-alert-container"
            );

        if (!container) return;

        if (!container.children.length) {

            container.remove();

        }

    }


    /* ======================================================
       CREAR ALERTA DINÁMICA
    ====================================================== */

    create(type, title, message, icon) {

        let container =
            document.querySelector(
                ".gx-alert-container"
            );


        /* ----------------------------------------------
           Crear contenedor si no existe
        ---------------------------------------------- */

        if (!container) {

            container =
                document.createElement("div");

            container.className =
                "gx-alert-container";

            document.body.appendChild(
                container
            );

        }


        /* ----------------------------------------------
           Crear alerta
        ---------------------------------------------- */

        const alert =
            document.createElement("div");

        alert.className =
            `gx-alert gx-alert-${type}`;


        alert.innerHTML = `

            <div class="gx-alert-icon">

                <i class="${icon}"></i>

            </div>

            <div class="gx-alert-content">

                <h4>${title}</h4>

                <p>${message}</p>

            </div>

            <button
                type="button"
                class="gx-alert-close"
                aria-label="Cerrar notificación"
            >

                <i class="fa-solid fa-xmark"></i>

            </button>

            <div class="gx-alert-progress"></div>

            <div class="gx-alert-shine"></div>

        `;


        container.appendChild(alert);

        this.alerts.push(alert);

        this.prepare(alert);

    }


    /* ======================================================
       MÉTODOS RÁPIDOS
    ====================================================== */

    success(message) {

        this.create(
            "success",
            "Éxito",
            message,
            "fa-solid fa-circle-check"
        );

    }


    error(message) {

        this.create(
            "error",
            "Error",
            message,
            "fa-solid fa-circle-xmark"
        );

    }


    warning(message) {

        this.create(
            "warning",
            "Advertencia",
            message,
            "fa-solid fa-triangle-exclamation"
        );

    }


    info(message) {

        this.create(
            "info",
            "Información",
            message,
            "fa-solid fa-circle-info"
        );

    }


    /* ======================================================
       DESTRUIR
    ====================================================== */

    destroy() {

        this.alerts.forEach(alert => {

            alert.remove();

        });

        this.alerts = [];

    }

}


/* ==========================================================
   INSTANCIA GLOBAL
========================================================== */

let gxNotifications = null;


/* ==========================================================
   INICIALIZACIÓN
========================================================== */

document.addEventListener(
    "DOMContentLoaded",
    () => {

        if (window.gxNotifications) {
            return;
        }

        gxNotifications =
            new GXNotifications();

        window.gxNotifications =
            gxNotifications;

    }
);


/* ==========================================================
   ATAJOS GLOBALES
========================================================== */

window.notifySuccess = (message) => {

    if (!window.gxNotifications) return;

    window.gxNotifications.success(message);

};


window.notifyError = (message) => {

    if (!window.gxNotifications) return;

    window.gxNotifications.error(message);

};


window.notifyWarning = (message) => {

    if (!window.gxNotifications) return;

    window.gxNotifications.warning(message);

};


window.notifyInfo = (message) => {

    if (!window.gxNotifications) return;

    window.gxNotifications.info(message);

};


/* ==========================================================
   API GLOBAL
========================================================== */

window.GXNotify = {

    success(message) {

        window.notifySuccess(message);

    },

    error(message) {

        window.notifyError(message);

    },

    warning(message) {

        window.notifyWarning(message);

    },

    info(message) {

        window.notifyInfo(message);

    }

};