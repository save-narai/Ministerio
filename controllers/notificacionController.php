<?php

declare(strict_types=1);

require_once __DIR__ . '/controller.php';
require_once __DIR__ . '/../services/sessionService.php';
require_once __DIR__ . '/../services/notificacionService.php';


/*
|--------------------------------------------------------------------------
| INICIALIZAR CONTROLLER
|--------------------------------------------------------------------------
*/

controllerInit();

$pdo = controllerPdo();


/*
|--------------------------------------------------------------------------
| DETECTAR PETICIÓN AJAX / JSON
|--------------------------------------------------------------------------
*/

$esAjax =
    isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower(
        (string)$_SERVER['HTTP_X_REQUESTED_WITH']
    ) === 'xmlhttprequest';


/*
|--------------------------------------------------------------------------
| CONTROLADOR DE NOTIFICACIONES
|--------------------------------------------------------------------------
*/

controllerRun(

    [

        /* ==========================================================
           MARCAR UNA NOTIFICACIÓN COMO LEÍDA
        ========================================================== */

        'marcar_leida' => function () use (
            $pdo,
            $esAjax
        ) {

            $usuarioId =
                usuarioId();


            if (
                $usuarioId === null ||
                $usuarioId <= 0
            ) {

                throw new Exception(
                    'No se pudo identificar al usuario actual.'
                );

            }


            $notificacionId =
                (int)(
                    $_POST['id']
                    ?? 0
                );


            if (
                $notificacionId <= 0
            ) {

                throw new Exception(
                    'La notificación seleccionada no es válida.'
                );

            }


            marcarNotificacionLeida(

                $pdo,

                $notificacionId,

                (int)$usuarioId

            );


            $totalNoLeidas =
                contarNotificacionesNoLeidas(

                    $pdo,

                    (int)$usuarioId

                );


            /*
             * Para fetch() devolvemos datos.
             */

            if ($esAjax) {

                return [

                    'json' => true,

                    'success' => true,

                    'message' =>
                        'Notificación marcada como leída.',

                    'total_no_leidas' =>
                        $totalNoLeidas

                ];

            }


            /*
             * Fallback normal.
             */

            return controllerRedirect(

                '../views/notificaciones/index.php',

                'Notificación marcada como leída.'

            );

        },


        /* ==========================================================
           MARCAR TODAS COMO LEÍDAS
        ========================================================== */

        'marcar_todas_leidas' => function () use (
            $pdo,
            $esAjax
        ) {

            $usuarioId =
                usuarioId();


            if (
                $usuarioId === null ||
                $usuarioId <= 0
            ) {

                throw new Exception(
                    'No se pudo identificar al usuario actual.'
                );

            }


            marcarTodasNotificacionesLeidas(

                $pdo,

                (int)$usuarioId

            );


            if ($esAjax) {

                return [

                    'json' => true,

                    'success' => true,

                    'message' =>
                        'Todas las notificaciones fueron marcadas como leídas.',

                    'total_no_leidas' => 0

                ];

            }


            return controllerRedirect(

                '../views/notificaciones/index.php',

                'Todas las notificaciones fueron marcadas como leídas.'

            );

        },


        /* ==========================================================
           ELIMINAR NOTIFICACIÓN
        ========================================================== */

        'eliminar' => function () use (
            $pdo,
            $esAjax
        ) {

            $usuarioId =
                usuarioId();


            if (
                $usuarioId === null ||
                $usuarioId <= 0
            ) {

                throw new Exception(
                    'No se pudo identificar al usuario actual.'
                );

            }


            $notificacionId =
                (int)(
                    $_POST['id']
                    ?? 0
                );


            if (
                $notificacionId <= 0
            ) {

                throw new Exception(
                    'La notificación seleccionada no es válida.'
                );

            }


            eliminarNotificacion(

                $pdo,

                $notificacionId,

                (int)$usuarioId

            );


            $totalNoLeidas =
                contarNotificacionesNoLeidas(

                    $pdo,

                    (int)$usuarioId

                );


            if ($esAjax) {

                return [

                    'json' => true,

                    'success' => true,

                    'message' =>
                        'Notificación eliminada correctamente.',

                    'total_no_leidas' =>
                        $totalNoLeidas

                ];

            }


            return controllerRedirect(

                '../views/notificaciones/index.php',

                'Notificación eliminada correctamente.'

            );

        }

    ],

    [

        'redirect' =>
            '../views/notificaciones/index.php'

    ]

);