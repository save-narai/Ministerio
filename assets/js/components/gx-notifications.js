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

        const closeButton =
            alert.querySelector(".gx-alert-close");

        const progress =
            alert.querySelector(".gx-alert-progress");

        let timer = null;

        let remaining = this.duration;

        let start = Date.now();

        /* -----------------------------------------
           Iniciar temporizador
        ----------------------------------------- */

        const startTimer = () => {

            start = Date.now();

            timer = setTimeout(() => {

                this.hide(alert);

            }, remaining);

            if (progress) {

                progress.style.animation = "none";

                progress.offsetHeight;

                progress.style.animation =
                    `gxProgress ${remaining}ms linear forwards`;

            }

        };

        /* -----------------------------------------
           Pausar
        ----------------------------------------- */

        const pauseTimer = () => {

            clearTimeout(timer);

            remaining -= Date.now() - start;

            if (progress) {

                progress.style.animationPlayState = "paused";

            }

        };

        /* -----------------------------------------
           Reanudar
        ----------------------------------------- */

        const resumeTimer = () => {

            if (remaining <= 0) {

                this.hide(alert);

                return;

            }

            if (progress) {

                progress.style.animationPlayState = "running";

            }

            startTimer();

        };

        startTimer();

        /* -----------------------------------------
           Hover
        ----------------------------------------- */

        alert.addEventListener(

            "mouseenter",

            pauseTimer

        );

        alert.addEventListener(

            "mouseleave",

            resumeTimer

        );

        /* -----------------------------------------
           Botón cerrar
        ----------------------------------------- */

        if (closeButton) {

            closeButton.addEventListener(

                "click",

                () => this.hide(alert)

            );

        }

    }                                                                                                                                                           /* ======================================================
       OCULTAR ALERTA
    ====================================================== */

    hide(alert) {

        if (!alert) return;

        if (alert.classList.contains("gx-hide")) return;

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

        this.alerts = this.alerts.filter(item => item !== alert);

        this.updateContainer();

    }

    /* ======================================================
       ACTUALIZAR CONTENEDOR
    ====================================================== */

    updateContainer() {

        const container = document.querySelector(

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

        const container = document.querySelector(

            ".gx-alert-container"

        );

        if (!container) return;

        const alert = document.createElement("div");

        alert.className = `gx-alert gx-alert-${type}`;

        alert.innerHTML = `

            <div class="gx-alert-icon">

                <i class="${icon}"></i>

            </div>

            <div class="gx-alert-content">

                <h4>${title}</h4>

                <p>${message}</p>

            </div>

            <button
                class="gx-alert-close"
                type="button"
            >

                <i class="fa-solid fa-xmark"></i>

            </button>

            <div class="gx-alert-progress"></div>

            <div class="gx-alert-shine"></div>

        `;

        container.appendChild(alert);

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

    }     /* ======================================================
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

        if (window.gxNotifications) return;

        gxNotifications = new GXNotifications();

        window.gxNotifications = gxNotifications;

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

/* ==========================================================
   DEBUG (SOLO DESARROLLO)
========================================================== */

// Ejemplos:
//
// GXNotify.success("Registro guardado correctamente.");
//
// GXNotify.error("No fue posible eliminar el registro.");
//
// GXNotify.warning("Hay seguimientos pendientes.");
//
// GXNotify.info("Nueva actualización disponible.");