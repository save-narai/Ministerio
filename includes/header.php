<?php

declare(strict_types=1);


/* ==========================================================
   BOOTSTRAP
========================================================== */

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../services/notificacionService.php';
require_once __DIR__ . '/../services/sessionService.php';


/* ==========================================================
   CONFIGURACIÓN GLOBAL
========================================================== */

$config = $GLOBALS['config'] ?? [];

$tituloPagina ??=
    $config['nombre']
    ?? 'Ministerio';

$extraCSS ??= '';

$extraJS ??= '';


/* ==========================================================
   CSRF PARA JAVASCRIPT
========================================================== */

$csrfToken =
    $_SESSION['csrf_token']
    ?? '';


/* ==========================================================
   NOTIFICACIONES DEL USUARIO ACTUAL
========================================================== */

$usuarioNotificaciones = null;

$totalNotificaciones = 0;

$notificaciones = [];


try {

    $usuarioNotificaciones =
        usuarioId();

    if (
        $usuarioNotificaciones !== null
        &&
        $usuarioNotificaciones > 0
    ) {

        $usuarioNotificaciones =
            (int)$usuarioNotificaciones;


        /*
         * Eliminar residuos de cancelaciones antiguas.
         */

        eliminarNotificacionesCanceladas(
            $pdo,
            $usuarioNotificaciones
        );


        /*
         * Obtener notificaciones no leídas.
         */

        $notificaciones =
            obtenerNotificacionesNoLeidas(
                $pdo,
                $usuarioNotificaciones,
                8
            );


        /*
         * No mostrar cancelaciones antiguas.
         * Se filtran antes del contador para que
         * el número coincida con lo visible.
         */

        $notificaciones =
            array_values(
                array_filter(
                    $notificaciones,
                    static function (array $notificacion): bool {

                        $tipo =
                            strtoupper(
                                trim(
                                    (string)(
                                        $notificacion['tipo']
                                        ?? ''
                                    )
                                )
                            );

                        return $tipo !== 'ASIGNACION_CANCELADA';
                    }
                )
            );


        /*
         * Contador real de notificaciones visibles.
         */

        $totalNotificaciones =
            count(
                $notificaciones
            );
    }

} catch (Throwable $e) {

    /*
     * El header nunca debe romperse
     * por problemas de notificaciones.
     */

    $usuarioNotificaciones = null;

    $totalNotificaciones = 0;

    $notificaciones = [];
}

?>


<!-- ==========================================================
     VARIABLES GLOBALES PARA JAVASCRIPT
========================================================== -->

<script>

    window.BASE_URL =
        <?= json_encode(
            BASE_URL
        ) ?>;

    window.CSRF_TOKEN =
        <?= json_encode(
            $csrfToken
        ) ?>;

</script>


<!DOCTYPE html>

<html lang="es">


<head>


    <!-- ======================================================
       META
    ======================================================= -->

    <meta charset="UTF-8">


    <meta
        name="csrf-token"
        content="<?= htmlspecialchars(
            $csrfToken,
            ENT_QUOTES,
            'UTF-8'
        ) ?>"
    >


    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >


    <meta
        name="description"
        content="Sistema de Seguimiento Ministerial"
    >


    <meta
        name="author"
        content="Ministerio Remanente"
    >


    <!-- ======================================================
       TITLE
    ======================================================= -->

    <title>
        <?= htmlspecialchars(
            $tituloPagina,
            ENT_QUOTES,
            'UTF-8'
        ) ?>
    </title>


    <!-- ======================================================
       FAVICON
    ======================================================= -->

    <link
        rel="icon"
        type="image/png"
        href="<?= BASE_URL ?>/assets/img/favicon.png"
    >


    <!-- ======================================================
       APP CSS
    ======================================================= -->

    <link
        rel="stylesheet"
        href="<?= BASE_URL ?>/assets/css/app.css?v=<?= filemtime(
            __DIR__ . '/../assets/css/app.css'
        ) ?>"
    >


    <!-- ======================================================
       FONT AWESOME
    ======================================================= -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    >


    <!-- ======================================================
       GOOGLE FONTS
    ======================================================= -->

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap"
        rel="stylesheet"
    >


    <!-- ======================================================
       DATATABLES CSS
    ======================================================= -->

    <link
        rel="stylesheet"
        href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css"
    >

    <link
        rel="stylesheet"
        href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css"
    >


    <!-- ======================================================
       CSS ADICIONAL DEL MÓDULO
    ======================================================= -->

    <?= $extraCSS ?>


    <!-- ======================================================
       TEMA
    ======================================================= -->

    <script>

        (() => {

            const theme =
                localStorage.getItem(
                    'theme'
                );

            if (
                theme === 'dark'
            ) {

                document.documentElement
                    .classList
                    .add('dark');

            }

        })();

    </script>


    <!-- ======================================================
       JQUERY
    ======================================================= -->

    <script
        defer
        src="https://code.jquery.com/jquery-3.7.1.min.js">
    </script>


    <!-- ======================================================
       DATATABLES
    ======================================================= -->

    <script
        defer
        src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js">
    </script>

    <script
        defer
        src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js">
    </script>

    <script
        defer
        src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js">
    </script>

    <script
        defer
        src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js">
    </script>

    <script
        defer
        src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js">
    </script>

    <script
        defer
        src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js">
    </script>

    <script
        defer
        src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js">
    </script>


    <!-- ======================================================
       CHART.JS
    ======================================================= -->

    <script
        defer
        src="https://cdn.jsdelivr.net/npm/chart.js">
    </script>


    <!-- ======================================================
       COMPONENTES GLOBALES
    ======================================================= -->

    <script
        defer
        src="<?= BASE_URL ?>/assets/js/theme.js?v=<?= file_exists(
            __DIR__ . '/../assets/js/theme.js'
        ) ? filemtime(
            __DIR__ . '/../assets/js/theme.js'
        ) : time() ?>">
    </script>

    <script
        defer
        src="<?= BASE_URL ?>/assets/js/components/datatable.js?v=<?= file_exists(
            __DIR__ . '/../assets/js/components/datatable.js'
        ) ? filemtime(
            __DIR__ . '/../assets/js/components/datatable.js'
        ) : time() ?>">
    </script>

    <script
        defer
        src="<?= BASE_URL ?>/assets/js/components/datatable-export.js?v=<?= file_exists(
            __DIR__ . '/../assets/js/components/datatable-export.js'
        ) ? filemtime(
            __DIR__ . '/../assets/js/components/datatable-export.js'
        ) : time() ?>">
    </script>

    <script
        defer
        src="<?= BASE_URL ?>/assets/js/components/search.js?v=<?= file_exists(
            __DIR__ . '/../assets/js/components/search.js'
        ) ? filemtime(
            __DIR__ . '/../assets/js/components/search.js'
        ) : time() ?>">
    </script>

    <script
        defer
        src="<?= BASE_URL ?>/assets/js/components/filters.js?v=<?= file_exists(
            __DIR__ . '/../assets/js/components/filters.js'
        ) ? filemtime(
            __DIR__ . '/../assets/js/components/filters.js'
        ) : time() ?>">
    </script>

    <script
        defer
        src="<?= BASE_URL ?>/assets/js/components/phone-validation.js?v=<?= file_exists(
            __DIR__ . '/../assets/js/components/phone-validation.js'
        ) ? filemtime(
            __DIR__ . '/../assets/js/components/phone-validation.js'
        ) : time() ?>">
    </script>

    <script
        defer
        src="<?= BASE_URL ?>/assets/js/components/gx-alerts.js?v=<?= file_exists(
            __DIR__ . '/../assets/js/components/gx-alerts.js'
        ) ? filemtime(
            __DIR__ . '/../assets/js/components/gx-alerts.js'
        ) : time() ?>">
    </script>

    <script
        defer
        src="<?= BASE_URL ?>/assets/js/components/gx-notifications.js?v=<?= file_exists(
            __DIR__ . '/../assets/js/components/gx-notifications.js'
        ) ? filemtime(
            __DIR__ . '/../assets/js/components/gx-notifications.js'
        ) : time() ?>">
    </script>


    <!-- ======================================================
       JAVASCRIPT ADICIONAL DEL MÓDULO
    ======================================================= -->

    <?= $extraJS ?>


</head>


<body>


<!-- ==========================================================
   APP
========================================================== -->

<div class="app">


    <!-- ======================================================
       SIDEBAR
    ======================================================= -->

    <?php require_once __DIR__ . '/sidebar.php'; ?>


    <!-- ======================================================
       CONTENIDO PRINCIPAL
    ======================================================= -->

    <main class="main">


        <!-- ==================================================
             TOPBAR
        =================================================== -->

        <header class="topbar topbar-minimal">

            <div class="topbar-right">


<!-- ==================================================
     NOTIFICACIONES
=================================================== -->

<?php if (
    $usuarioNotificaciones !== null
): ?>

    <div
        class="gx-notifications"
        id="gxNotifications"
    >

        <a
            href="<?= BASE_URL ?>/views/notificaciones/index.php"
            class="gx-notifications__trigger"
            id="gxNotificationsTrigger"
            aria-label="Notificaciones"
        >

            <i class="fa-solid fa-bell"></i>

            <span
                class="gx-notifications__badge"
                id="gxNotificationsBadge"
                <?= $totalNotificaciones <= 0 ? 'hidden' : '' ?>
            >
                <?= $totalNotificaciones ?>
            </span>

        </a>


      
                        <!-- MENÚ -->

                        <div
                            class="gx-notifications__menu"
                            id="gxNotificationsMenu"
                            aria-hidden="true"
                        >


                            <!-- HEADER -->

                            <div
                                class="gx-notifications__header"
                            >

                                <div>

                                    <h3>
                                        Notificaciones
                                    </h3>

                                    <span
                                        id="gxNotificationsCountText"
                                    >

                                        <?= $totalNotificaciones ?>

                                        <?= $totalNotificaciones === 1
                                            ? 'pendiente'
                                            : 'pendientes' ?>

                                    </span>

                                </div>


                                <button
                                    type="button"
                                    class="gx-notifications__close"
                                    id="gxNotificationsClose"
                                    aria-label="Cerrar notificaciones"
                                >

                                    <i class="fa-solid fa-xmark"></i>

                                </button>

                            </div>


                            <!-- LISTA -->

                            <div
                                class="gx-notifications__list"
                                id="gxNotificationsList"
                            >


                                <?php if (
                                    !empty($notificaciones)
                                ): ?>


                                    <?php foreach (
                                        $notificaciones
                                        as $notificacion
                                    ): ?>

                                        <?php

                                        $tipoNotificacion =
                                            strtoupper(
                                                trim(
                                                    (string)(
                                                        $notificacion['tipo']
                                                        ?? ''
                                                    )
                                                )
                                            );


                                        if (
                                            $tipoNotificacion ===
                                            'ASIGNACION_CANCELADA'
                                        ) {
                                            continue;
                                        }


                                        $iconoNotificacion =
                                            match (
                                                $tipoNotificacion
                                            ) {

                                                'NUEVA_ASIGNACION' =>
                                                    'fa-user-plus',

                                                'ASIGNACION_EN_PROCESO' =>
                                                    'fa-play',

                                                'ASIGNACION_COMPLETADA' =>
                                                    'fa-circle-check',

                                                'RECORDATORIO_SEGUIMIENTO' =>
                                                    'fa-bell',

                                                default =>
                                                    'fa-bell'
                                            };


                                        $claseTipo =
                                            match (
                                                $tipoNotificacion
                                            ) {

                                                'NUEVA_ASIGNACION' =>
                                                    'nueva',

                                                'ASIGNACION_EN_PROCESO' =>
                                                    'proceso',

                                                'ASIGNACION_COMPLETADA' =>
                                                    'completada',

                                                'RECORDATORIO_SEGUIMIENTO' =>
                                                    'recordatorio',

                                                default =>
                                                    'general'
                                            };

                                        ?>


                                        <button
                                            type="button"
                                            class="gx-notifications__item"
                                            data-notificacion-id="<?= (int)(
                                                $notificacion['id']
                                                ?? 0
                                            ) ?>"
                                            data-tipo="<?= e(
                                                $tipoNotificacion
                                            ) ?>"
                                            data-joven-id="<?= (int)(
                                                $notificacion['joven_id']
                                                ?? 0
                                            ) ?>"
                                            data-asignacion-id="<?= (int)(
                                                $notificacion['asignacion_id']
                                                ?? 0
                                            ) ?>"
                                        >


                                            <!-- ICONO -->

                                            <span
                                                class="
                                                    gx-notifications__item-icon
                                                    gx-notifications__item-icon--<?= e(
                                                        $claseTipo
                                                    )
                                                ?>"
                                            >

                                                <i
                                                    class="fa-solid <?= e(
                                                        $iconoNotificacion
                                                    ) ?>"
                                                ></i>

                                            </span>


                                            <!-- TEXTO -->

                                            <span
                                                class="gx-notifications__item-content"
                                            >

                                                <strong>

                                                    <?= e(
                                                        $notificacion['titulo']
                                                        ?? 'Notificación'
                                                    ) ?>

                                                </strong>

                                                <span>

                                                    <?= e(
                                                        $notificacion['mensaje']
                                                        ?? ''
                                                    ) ?>

                                                </span>

                                            </span>


                                            <!-- ESTADO -->

                                            <span
                                                class="gx-notifications__item-dot"
                                            ></span>


                                        </button>


                                    <?php endforeach; ?>


                                <?php endif; ?>


                                <!-- VACÍO -->

                                <div
                                    class="gx-notifications__empty"
                                    id="gxNotificationsEmpty"
                                    <?= !empty($notificaciones)
                                        ? 'hidden'
                                        : '' ?>
                                >

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

                                </div>


                            </div>


                            <!-- FOOTER -->

                            <div
                                class="gx-notifications__footer"
                            >

                                <a
                                    href="<?= BASE_URL ?>/views/notificaciones/index.php"
                                >

                                    Ver todas

                                    <i
                                        class="fa-solid fa-arrow-right"
                                    ></i>

                                </a>

                            </div>


                        </div>

                    </div>

                <?php endif; ?>


                <!-- ==================================================
                     TEMA
                =================================================== -->

                <button
                    type="button"
                    id="themeToggle"
                    class="theme-toggle"
                    aria-label="Cambiar tema"
                >

                    <i
                        class="fa-solid fa-moon"
                    ></i>

                </button>


            </div>

        </header>


        <!-- ==================================================
             ALERTAS / MENSAJES DEL SISTEMA
        ================================================== -->

        <div class="gx-alert-container">


            <!-- SUCCESS -->

            <?php if (
                $mensaje = getFlash('success')
            ): ?>

                <div
                    class="gx-alert gx-alert-success"
                >

                    <div
                        class="gx-alert-icon"
                    >

                        <i
                            class="fa-solid fa-circle-check"
                        ></i>

                    </div>


                    <div
                        class="gx-alert-content"
                    >

                        <h4>
                            Éxito
                        </h4>


                        <p>
                            <?= htmlspecialchars(
                                $mensaje,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </p>

                    </div>


                    <button
                        type="button"
                        class="gx-alert-close"
                        aria-label="Cerrar"
                    >

                        <i
                            class="fa-solid fa-xmark"
                        ></i>

                    </button>


                    <div
                        class="gx-alert-progress"
                    ></div>

                </div>

            <?php endif; ?>


            <!-- ERROR -->

            <?php if (
                $mensaje = getFlash('error')
            ): ?>

                <div
                    class="gx-alert gx-alert-error"
                >

                    <div
                        class="gx-alert-icon"
                    >

                        <i
                            class="fa-solid fa-circle-xmark"
                        ></i>

                    </div>


                    <div
                        class="gx-alert-content"
                    >

                        <h4>
                            Error
                        </h4>


                        <p>
                            <?= htmlspecialchars(
                                $mensaje,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </p>

                    </div>


                    <button
                        type="button"
                        class="gx-alert-close"
                        aria-label="Cerrar"
                    >

                        <i
                            class="fa-solid fa-xmark"
                        ></i>

                    </button>


                    <div
                        class="gx-alert-progress"
                    ></div>

                </div>

            <?php endif; ?>


            <!-- WARNING -->

            <?php if (
                $mensaje = getFlash('warning')
            ): ?>

                <div
                    class="gx-alert gx-alert-warning"
                >

                    <div
                        class="gx-alert-icon"
                    >

                        <i
                            class="fa-solid fa-triangle-exclamation"
                        ></i>

                    </div>


                    <div
                        class="gx-alert-content"
                    >

                        <h4>
                            Advertencia
                        </h4>


                        <p>
                            <?= htmlspecialchars(
                                $mensaje,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </p>

                    </div>


                    <button
                        type="button"
                        class="gx-alert-close"
                        aria-label="Cerrar"
                    >

                        <i
                            class="fa-solid fa-xmark"
                        ></i>

                    </button>


                    <div
                        class="gx-alert-progress"
                    ></div>

                </div>

            <?php endif; ?>


            <!-- INFO -->

            <?php if (
                $mensaje = getFlash('info')
            ): ?>

                <div
                    class="gx-alert gx-alert-info"
                >

                    <div
                        class="gx-alert-icon"
                    >

                        <i
                            class="fa-solid fa-circle-info"
                        ></i>

                    </div>


                    <div
                        class="gx-alert-content"
                    >

                        <h4>
                            Información
                        </h4>


                        <p>
                            <?= htmlspecialchars(
                                $mensaje,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </p>

                    </div>


                    <button
                        type="button"
                        class="gx-alert-close"
                        aria-label="Cerrar"
                    >

                        <i
                            class="fa-solid fa-xmark"
                        ></i>

                    </button>


                    <div
                        class="gx-alert-progress"
                    ></div>

                </div>

            <?php endif; ?>


        </div>


        <!-- ======================================================
             INICIO DEL CONTENIDO DE LA VISTA
        ======================================================= -->

        <?php

        /*
        |--------------------------------------------------------------------------
        | A partir de este punto comienza el contenido específico
        | de cada módulo.
        |--------------------------------------------------------------------------
        |
        | Dashboard
        | Usuarios
        | Roles
        | Jóvenes
        | Reuniones
        | Seguimientos
        | Configuración
        |
        | El cierre de:
        |
        | </section>
        | </main>
        | </div>
        | </body>
        | </html>
        |
        | se encuentra en:
        |
        | includes/footer.php
        |--------------------------------------------------------------------------
        */

        ?>