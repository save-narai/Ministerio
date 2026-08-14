"use strict";

/* ==========================================================
   GX ALERTS
========================================================== */

class GXAlerts {

    constructor() {

        /* ==================================================
           CONFIGURACIÓN
        ================================================== */

        this.defaultDuration = 5000;

        this.alerts = [];

        this.init();

    }


    /* ======================================================
       INICIALIZAR
    ====================================================== */

    init() {

        this.alerts = [
            ...document.querySelectorAll(
                ".gx-alert"
            )
        ];


        this.alerts.forEach(
            alert => {

                this.prepare(
                    alert
                );

            }
        );

    }


    /* ======================================================
       PREPARAR ALERTA
    ====================================================== */

    prepare(
        alert
    ) {

        if (
            !alert ||
            alert.dataset.gxInitialized === "true"
        ) {

            return;

        }


        alert.dataset.gxInitialized =
            "true";


        const closeBtn =
            alert.querySelector(
                ".gx-alert-close"
            );


        const progress =
            alert.querySelector(
                ".gx-alert-progress"
            );


        const duration =
            parseInt(
                alert.dataset.duration ||
                this.defaultDuration,
                10
            );


        let remaining =
            duration;


        let timer =
            null;


        let start =
            Date.now();


        let paused =
            false;


        /* ==================================================
           PROGRESO
        ================================================== */

        const renderProgress =
            () => {

                if (!progress) {

                    return;

                }


                const percentage =
                    Math.max(

                        0,

                        (
                            remaining /
                            duration
                        ) * 100

                    );


                progress.style.setProperty(

                    "--gx-progress",

                    `${percentage}%`

                );

            };


        /* ==================================================
           INICIAR TEMPORIZADOR
        ================================================== */

        const startTimer =
            () => {

                clearTimeout(
                    timer
                );


                start =
                    Date.now();


                timer =
                    setTimeout(

                        () => {

                            remaining =
                                0;


                            renderProgress();


                            this.hide(
                                alert
                            );

                        },

                        remaining

                    );

            };


        /* ==================================================
           PAUSAR
        ================================================== */

        const pause =
            () => {

                if (
                    paused
                ) {

                    return;

                }


                paused =
                    true;


                clearTimeout(
                    timer
                );


                remaining -=
                    Date.now() -
                    start;


                remaining =
                    Math.max(
                        0,
                        remaining
                    );


                renderProgress();

            };


        /* ==================================================
           REANUDAR
        ================================================== */

        const resume =
            () => {

                if (
                    !paused
                ) {

                    return;

                }


                paused =
                    false;


                if (
                    remaining <= 0
                ) {

                    this.hide(
                        alert
                    );

                    return;

                }


                startTimer();

            };


        /* ==================================================
           INICIO
        ================================================== */

        renderProgress();

        startTimer();


        /* ==================================================
           HOVER
        ================================================== */

        alert.addEventListener(
            "mouseenter",
            pause
        );


        alert.addEventListener(
            "mouseleave",
            resume
        );


        /* ==================================================
           CERRAR MANUALMENTE
        ================================================== */

        if (closeBtn) {

            closeBtn.addEventListener(

                "click",

                event => {

                    event.preventDefault();

                    event.stopPropagation();


                    clearTimeout(
                        timer
                    );


                    this.hide(
                        alert
                    );

                }

            );

        }

    }


    /* ======================================================
       OCULTAR
    ====================================================== */

    hide(
        alert
    ) {

        if (
            !alert
        ) {

            return;

        }


        if (

            alert.classList.contains(
                "gx-hide"
            )

            ||

            alert.dataset.gxRemoving ===
                "true"

        ) {

            return;

        }


        alert.dataset.gxRemoving =
            "true";


        alert.classList.add(
            "gx-hide"
        );


        setTimeout(

            () => {

                this.remove(
                    alert
                );

            },

            350

        );

    }


    /* ======================================================
       ELIMINAR
    ====================================================== */

    remove(
        alert
    ) {

        if (
            !alert
        ) {

            return;

        }


        alert.remove();


        this.alerts =
            this.alerts.filter(

                item =>
                    item !== alert

            );


        const container =
            document.querySelector(
                ".gx-alert-container"
            );


        if (

            container &&

            container.children.length === 0

        ) {

            container.remove();

        }

    }


    /* ======================================================
       ESCAPAR HTML
    ====================================================== */

    escapeHTML(
        text
    ) {

        const div =
            document.createElement(
                "div"
            );


        div.textContent =
            text ?? "";


        return div.innerHTML;

    }


    /* ======================================================
       CREAR ALERTA
    ====================================================== */

    create(
        type,
        title,
        message,
        icon
    ) {

        let container =
            document.querySelector(
                ".gx-alert-container"
            );


        if (
            !container
        ) {

            container =
                document.createElement(
                    "div"
                );


            container.className =
                "gx-alert-container";


            document.body.appendChild(
                container
            );

        }


        const safeTitle =
            this.escapeHTML(
                title
            );


        const safeMessage =
            this.escapeHTML(
                message
            );


        const alert =
            document.createElement(
                "div"
            );


        alert.className =
            `gx-alert gx-alert-${type}`;


        alert.dataset.duration =
            this.defaultDuration;


        alert.innerHTML = `

            <div
                class="gx-alert-icon"
            >

                <i
                    class="${icon}"
                ></i>

            </div>


            <div
                class="gx-alert-content"
            >

                <h4>
                    ${safeTitle}
                </h4>

                <p>
                    ${safeMessage}
                </p>

            </div>


            <button
                type="button"
                class="gx-alert-close"
                aria-label="Cerrar alerta"
            >

                <i
                    class="fa-solid fa-xmark"
                ></i>

            </button>


            <div
                class="gx-alert-progress"
            ></div>


            <div
                class="gx-alert-shine"
            ></div>

        `;


        container.appendChild(
            alert
        );


        this.alerts.push(
            alert
        );


        this.prepare(
            alert
        );

    }


    /* ======================================================
       SUCCESS
    ====================================================== */

    success(
        message
    ) {

        this.create(

            "success",

            "Éxito",

            message,

            "fa-solid fa-circle-check"

        );

    }


    /* ======================================================
       ERROR
    ====================================================== */

    error(
        message
    ) {

        this.create(

            "error",

            "Error",

            message,

            "fa-solid fa-circle-xmark"

        );

    }


    /* ======================================================
       WARNING
    ====================================================== */

    warning(
        message
    ) {

        this.create(

            "warning",

            "Advertencia",

            message,

            "fa-solid fa-triangle-exclamation"

        );

    }


    /* ======================================================
       INFO
    ====================================================== */

    info(
        message
    ) {

        this.create(

            "info",

            "Información",

            message,

            "fa-solid fa-circle-info"

        );

    }

}


/* ==========================================================
   INSTANCIA GLOBAL
========================================================== */

document.addEventListener(

    "DOMContentLoaded",

    () => {

        if (
            !window.gxAlerts
        ) {

            window.gxAlerts =
                new GXAlerts();

        }

    }

);


/* ==========================================================
   API GLOBAL
========================================================== */

window.GXAlert = {

    success(
        message
    ) {

        window.gxAlerts
            ?.success(
                message
            );

    },


    error(
        message
    ) {

        window.gxAlerts
            ?.error(
                message
            );

    },


    warning(
        message
    ) {

        window.gxAlerts
            ?.warning(
                message
            );

    },


    info(
        message
    ) {

        window.gxAlerts
            ?.info(
                message
            );

    }

};