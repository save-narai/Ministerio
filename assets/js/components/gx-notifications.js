"use strict";

/* ==========================================================
   GX NOTIFICATIONS
========================================================== */

(() => {

    /*
     * Evitar redeclaraciones.
     */
    if (window.GXNotifications) {

        return;

    }


    class GXNotifications {

        constructor() {

            this.container = null;

            this.trigger = null;

            this.menu = null;

            this.badge = null;

            this.list = null;

            this.init();

        }


        /* ==================================================
           INIT
        ================================================== */

        init() {

            this.container =
                document.getElementById(
                    "gxNotifications"
                );


            this.trigger =
                document.getElementById(
                    "gxNotificationsTrigger"
                );


            this.menu =
                document.getElementById(
                    "gxNotificationsMenu"
                );


            this.badge =
                document.getElementById(
                    "gxNotificationsBadge"
                );


            this.list =
                document.getElementById(
                    "gxNotificationsList"
                );


            /*
             * Si la página no tiene notificaciones,
             * no hacemos nada.
             */

            if (
                !this.container ||
                !this.trigger ||
                !this.menu
            ) {

                return;

            }


            this.bindEvents();

        }


        /* ==================================================
           EVENTOS
        ================================================== */

        bindEvents() {

            /*
             * CAMPANITA
             */

            this.trigger.addEventListener(
                "click",
                event => {

                    /*
                     * Si el trigger es <a>, evitamos
                     * la navegación para poder abrir
                     * el panel.
                     */

                    event.preventDefault();

                    event.stopPropagation();


                    this.toggle();

                }
            );


            /*
             * CLICK DENTRO DEL MENÚ
             */

            this.menu.addEventListener(
                "click",
                event => {

                    event.stopPropagation();

                }
            );


            /*
             * CLICK AFUERA
             */

            document.addEventListener(
                "click",
                () => {

                    this.close();

                }
            );


            /*
             * ESC
             */

            document.addEventListener(
                "keydown",
                event => {

                    if (
                        event.key === "Escape"
                    ) {

                        this.close();

                    }

                }
            );


            /*
             * BOTÓN CERRAR
             */

            const closeButton =
                document.getElementById(
                    "gxNotificationsClose"
                );


            if (closeButton) {

                closeButton.addEventListener(
                    "click",
                    event => {

                        event.preventDefault();

                        event.stopPropagation();

                        this.close();

                    }
                );

            }


            /*
             * CLICK EN UNA NOTIFICACIÓN
             */

            if (this.list) {

                this.list.addEventListener(
                    "click",
                    event => {

                        const item =
                            event.target.closest(
                                ".gx-notifications__item"
                            );


                        if (!item) {

                            return;

                        }


                        event.preventDefault();

                        event.stopPropagation();


                        this.handleNotification(
                            item
                        );

                    }
                );

            }

        }


        /* ==================================================
           TOGGLE
        ================================================== */

        toggle() {

            if (
                !this.container
            ) {

                return;

            }


            if (
                this.container.classList.contains(
                    "is-open"
                )
            ) {

                this.close();

            } else {

                this.open();

            }

        }


        /* ==================================================
           OPEN
        ================================================== */

        open() {

            if (
                !this.container ||
                !this.trigger ||
                !this.menu
            ) {

                return;

            }


            this.container.classList.add(
                "is-open"
            );


            this.trigger.setAttribute(
                "aria-expanded",
                "true"
            );


            this.menu.setAttribute(
                "aria-hidden",
                "false"
            );

        }


        /* ==================================================
           CLOSE
        ================================================== */

        close() {

            if (
                !this.container ||
                !this.trigger ||
                !this.menu
            ) {

                return;

            }


            this.container.classList.remove(
                "is-open"
            );


            this.trigger.setAttribute(
                "aria-expanded",
                "false"
            );


            this.menu.setAttribute(
                "aria-hidden",
                "true"
            );

        }


        /* ==================================================
           PROCESAR NOTIFICACIÓN
        ================================================== */

        async handleNotification(
            item
        ) {

            if (!item) {

                return;

            }


            const id =
                this.toInt(
                    item.dataset.notificacionId
                );


            const tipo =
                item.dataset.tipo || "";


            const asignacionId =
                this.toInt(
                    item.dataset.asignacionId
                );


            const jovenId =
                this.toInt(
                    item.dataset.jovenId
                );


            if (
                id <= 0
            ) {

                return;

            }


            /*
             * Primero marcamos como leída.
             */

            const resultado =
                await this.markAsRead(
                    id
                );


            if (!resultado) {

                return;

            }


            /*
             * Quitamos visualmente.
             */

            this.removeItem(
                item
            );


            /*
             * Navegamos.
             */

            this.navigate({

                tipo,

                asignacionId,

                jovenId

            });

        }


        /* ==================================================
           MARCAR COMO LEÍDA
        ================================================== */

        async markAsRead(
            notificationId
        ) {

            if (
                notificationId <= 0
            ) {

                return false;

            }


            const baseUrl =
                window.BASE_URL || "";


            const url =
                `${baseUrl}/controllers/notificacionController.php`;


            const csrfMeta =
                document.querySelector(
                    'meta[name="csrf-token"]'
                );


            const csrfToken =
                csrfMeta?.getAttribute(
                    "content"
                ) || "";


            const formData =
                new FormData();


            formData.append(
                "action",
                "marcar_leida"
            );


            formData.append(
                "id",
                String(
                    notificationId
                )
            );


            if (
                csrfToken !== ""
            ) {

                formData.append(
                    "csrf_token",
                    csrfToken
                );

            }


            try {

                const response =
                    await fetch(
                        url,
                        {

                            method: "POST",

                            body: formData,

                            credentials:
                                "same-origin",

                            headers: {

                                "X-Requested-With":
                                    "XMLHttpRequest",

                                "Accept":
                                    "application/json"

                            }

                        }
                    );


                const text =
                    await response.text();


                let data;


                try {

                    data =
                        JSON.parse(
                            text
                        );

                } catch (
                    error
                ) {

                    console.error(
                        "Respuesta no válida del controller:",
                        text
                    );

                    return false;

                }


                if (
                    !response.ok
                ) {

                    console.error(
                        "Error HTTP:",
                        response.status,
                        data
                    );

                    return false;

                }


                if (
                    data?.success !== true
                ) {

                    console.error(
                        "El servidor rechazó la operación:",
                        data?.message
                    );

                    return false;

                }


                const total =
                    Number(
                        data?.total_no_leidas
                        ??
                        data?.data?.total_no_leidas
                        ??
                        0
                    );


                this.updateCounter(
                    total
                );


                return true;


            } catch (
                error
            ) {

                console.error(
                    "Error al marcar notificación:",
                    error
                );

                return false;

            }

        }


        /* ==================================================
           QUITAR NOTIFICACIÓN
        ================================================== */

        removeItem(
            item
        ) {

            if (!item) {

                return;

            }


            item.classList.add(
                "gx-notifications__item--removing"
            );


            setTimeout(
                () => {

                    item.remove();

                    this.refreshEmpty();

                },
                180
            );

        }


        /* ==================================================
           ESTADO VACÍO
        ================================================== */

        refreshEmpty() {

            if (!this.list) {

                return;

            }


            const items =
                this.list.querySelectorAll(
                    ".gx-notifications__item"
                );


            if (
                items.length > 0
            ) {

                return;

            }


            let empty =
                this.list.querySelector(
                    ".gx-notifications__empty"
                );


            if (empty) {

                empty.hidden = false;

                return;

            }


            empty =
                document.createElement(
                    "div"
                );


            empty.className =
                "gx-notifications__empty";


            empty.innerHTML = `

                <span
                    class="gx-notifications__empty-icon"
                >

                    <i
                        class="fa-regular fa-bell-slash"
                    ></i>

                </span>

                <strong>
                    Todo al día
                </strong>

                <p>
                    No tienes notificaciones pendientes.
                </p>

            `;


            this.list.appendChild(
                empty
            );

        }


        /* ==================================================
           ACTUALIZAR CONTADOR
        ================================================== */

        updateCounter(
            total
        ) {

            let cantidad =
                Number(
                    total
                );


            if (
                !Number.isFinite(
                    cantidad
                )
            ) {

                cantidad = 0;

            }


            cantidad =
                Math.max(
                    0,
                    cantidad
                );


            /*
             * BADGE
             */

            if (
                cantidad <= 0
            ) {

                if (
                    this.badge
                ) {

                    this.badge.remove();

                    this.badge = null;

                }

            } else {

                if (
                    !this.badge &&
                    this.trigger
                ) {

                    this.badge =
                        document.createElement(
                            "span"
                        );


                    this.badge.className =
                        "gx-notifications__badge";


                    this.badge.id =
                        "gxNotificationsBadge";


                    this.trigger.appendChild(
                        this.badge
                    );

                }


                if (
                    this.badge
                ) {

                    this.badge.textContent =
                        String(
                            cantidad
                        );

                }

            }


            /*
             * TEXTO DEL PANEL
             */

            const counter =
                document.getElementById(
                    "gxNotificationsCountText"
                );


            if (
                counter
            ) {

                counter.textContent =
                    `${cantidad} ${
                        cantidad === 1
                            ? "pendiente"
                            : "pendientes"
                    }`;

            }

        }


        /* ==================================================
           NAVEGACIÓN
        ================================================== */

        navigate(
            datos
        ) {

            if (!datos) {

                return;

            }


            const baseUrl =
                window.BASE_URL || "";


            let url = null;


            switch (
                datos.tipo
            ) {

                case "NUEVA_ASIGNACION":

                case "ASIGNACION_EN_PROCESO":

                    url =
                        `${baseUrl}/views/seguimientos/mis-seguimientos.php`;

                    break;


                case "ASIGNACION_COMPLETADA":

                    url =
                        `${baseUrl}/views/seguimientos/asignaciones.php`;

                    break;


                default:

                    /*
                     * Para cualquier otra notificación
                     * no hacemos redirección automática.
                     */

                    return;

            }


            if (
                url
            ) {

                window.location.assign(
                    url
                );

            }

        }


        /* ==================================================
           UTILIDAD
        ================================================== */

        toInt(
            value
        ) {

            const number =
                parseInt(
                    value || "0",
                    10
                );


            return Number.isNaN(
                number
            )
                ? 0
                : number;

        }

    }


    /* ======================================================
       REGISTRAR CLASE
    ====================================================== */

    window.GXNotifications =
        GXNotifications;


    /* ======================================================
       INICIALIZAR
    ====================================================== */

    const initNotifications =
        () => {

            if (
                !window.gxNotifications &&
                window.GXNotifications
            ) {

                window.gxNotifications =
                    new window.GXNotifications();

            }

        };


    if (
        document.readyState === "loading"
    ) {

        document.addEventListener(
            "DOMContentLoaded",
            initNotifications,
            {
                once: true
            }
        );

    } else {

        initNotifications();

    }


    /* ======================================================
       API PÚBLICA
    ====================================================== */

    window.GXNotificationsAPI = {

        open() {

            window.gxNotifications?.open();

        },


        close() {

            window.gxNotifications?.close();

        },


        toggle() {

            window.gxNotifications?.toggle();

        },


        markAsRead(
            id
        ) {

            return (
                window.gxNotifications
                    ?.markAsRead(
                        id
                    )
            );

        }

    };

})();