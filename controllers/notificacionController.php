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

            $usuarioId = usuarioId();

            if (
                $usuarioId === null ||
                $usuarioId <= 0
            ) {
                throw new Exception(
                    'No se pudo identificar al usuario actual.'
                );
            }

            $usuarioId = (int)$usuarioId;

            $notificacionId = (int)(
                $_POST['id'] ?? 0
            );

            if ($notificacionId <= 0) {
                throw new Exception(
                    'La notificación seleccionada no es válida.'
                );
            }

            marcarNotificacionLeida(
                $pdo,
                $notificacionId,
                $usuarioId
            );

            $totalNoLeidas =
                contarNotificacionesNoLeidas(
                    $pdo,
                    $usuarioId
                );

            if ($esAjax) {

                return [

                    'json' =>
                        true,

                    'success' =>
                        true,

                    'message' =>
                        'Notificación marcada como leída.',

                    'total_no_leidas' =>
                        $totalNoLeidas

                ];
            }

            return controllerRedirect(
                '../views/notificaciones/index.php',
                'Notificación marcada como leída.'
            );
        },


        /* ==========================================================
           MARCAR UNA NOTIFICACIÓN COMO NO LEÍDA
        ========================================================== */

        'marcar_no_leida' => function () use (
            $pdo,
            $esAjax
        ) {

            $usuarioId = usuarioId();

            if (
                $usuarioId === null ||
                $usuarioId <= 0
            ) {
                throw new Exception(
                    'No se pudo identificar al usuario actual.'
                );
            }

            $usuarioId = (int)$usuarioId;

            $notificacionId = (int)(
                $_POST['id'] ?? 0
            );

            if ($notificacionId <= 0) {
                throw new Exception(
                    'La notificación seleccionada no es válida.'
                );
            }

            marcarNotificacionNoLeida(
                $pdo,
                $notificacionId,
                $usuarioId
            );

            $totalNoLeidas =
                contarNotificacionesNoLeidas(
                    $pdo,
                    $usuarioId
                );

            if ($esAjax) {

                return [

                    'json' =>
                        true,

                    'success' =>
                        true,

                    'message' =>
                        'Notificación marcada como no leída.',

                    'total_no_leidas' =>
                        $totalNoLeidas

                ];
            }

            return controllerRedirect(
                '../views/notificaciones/index.php',
                'Notificación marcada como no leída.'
            );
        },


        /* ==========================================================
           MARCAR TODAS COMO LEÍDAS
        ========================================================== */

        'marcar_todas_leidas' => function () use (
            $pdo,
            $esAjax
        ) {

            $usuarioId = usuarioId();

            if (
                $usuarioId === null ||
                $usuarioId <= 0
            ) {
                throw new Exception(
                    'No se pudo identificar al usuario actual.'
                );
            }

            $usuarioId = (int)$usuarioId;

            marcarTodasNotificacionesLeidas(
                $pdo,
                $usuarioId
            );

            $totalNoLeidas =
                contarNotificacionesNoLeidas(
                    $pdo,
                    $usuarioId
                );

            if ($esAjax) {

                return [

                    'json' =>
                        true,

                    'success' =>
                        true,

                    'message' =>
                        'Todas las notificaciones fueron marcadas como leídas.',

                    'total_no_leidas' =>
                        $totalNoLeidas

                ];
            }

            return controllerRedirect(
                '../views/notificaciones/index.php',
                'Todas las notificaciones fueron marcadas como leídas.'
            );
        },


        /* ==========================================================
           MARCAR TODAS COMO NO LEÍDAS
        ========================================================== */

        'marcar_todas_no_leidas' => function () use (
            $pdo,
            $esAjax
        ) {

            $usuarioId = usuarioId();

            if (
                $usuarioId === null ||
                $usuarioId <= 0
            ) {
                throw new Exception(
                    'No se pudo identificar al usuario actual.'
                );
            }

            $usuarioId = (int)$usuarioId;

            $actualizadas =
                marcarTodasNotificacionesNoLeidas(
                    $pdo,
                    $usuarioId
                );

            $totalNoLeidas =
                contarNotificacionesNoLeidas(
                    $pdo,
                    $usuarioId
                );

            $mensaje =
                $actualizadas > 0

                    ? "Se marcaron {$actualizadas} notificaciones como no leídas."

                    : 'No había notificaciones leídas.';

            if ($esAjax) {

                return [

                    'json' =>
                        true,

                    'success' =>
                        true,

                    'message' =>
                        $mensaje,

                    'actualizadas' =>
                        $actualizadas,

                    'total_no_leidas' =>
                        $totalNoLeidas

                ];
            }

            return controllerRedirect(
                '../views/notificaciones/index.php',
                $mensaje
            );
        },


        /* ==========================================================
           ELIMINAR TODAS LAS LEÍDAS
        ========================================================== */

        'eliminar_leidas' => function () use (
            $pdo,
            $esAjax
        ) {

            $usuarioId = usuarioId();

            if (
                $usuarioId === null ||
                $usuarioId <= 0
            ) {
                throw new Exception(
                    'No se pudo identificar al usuario actual.'
                );
            }

            $usuarioId = (int)$usuarioId;

            $eliminadas =
                eliminarNotificacionesLeidas(
                    $pdo,
                    $usuarioId
                );

            $totalNoLeidas =
                contarNotificacionesNoLeidas(
                    $pdo,
                    $usuarioId
                );

            $mensaje =
                $eliminadas > 0

                    ? "Se eliminaron {$eliminadas} notificaciones leídas."

                    : 'No había notificaciones leídas para eliminar.';

            if ($esAjax) {

                return [

                    'json' =>
                        true,

                    'success' =>
                        true,

                    'message' =>
                        $mensaje,

                    'eliminadas' =>
                        $eliminadas,

                    'total_no_leidas' =>
                        $totalNoLeidas

                ];
            }

            return controllerRedirect(
                '../views/notificaciones/index.php',
                $mensaje
            );
        },


        /* ==========================================================
           LIMPIAR NOTIFICACIONES ANTIGUAS
        ========================================================== */

        'limpiar_antiguas' => function () use (
            $pdo,
            $esAjax
        ) {

            $usuarioId = usuarioId();

            if (
                $usuarioId === null ||
                $usuarioId <= 0
            ) {
                throw new Exception(
                    'No se pudo identificar al usuario actual.'
                );
            }

            $usuarioId = (int)$usuarioId;

            $dias = (int)(
                $_POST['dias'] ?? 30
            );

            $dias = max(
                1,
                min(
                    $dias,
                    3650
                )
            );

            $eliminadas =
                eliminarNotificacionesLeidasAntesDe(
                    $pdo,
                    $usuarioId,
                    $dias
                );

            $totalNoLeidas =
                contarNotificacionesNoLeidas(
                    $pdo,
                    $usuarioId
                );

            $mensaje =
                $eliminadas > 0

                    ? "Se eliminaron {$eliminadas} notificaciones antiguas."

                    : 'No había notificaciones antiguas para limpiar.';

            if ($esAjax) {

                return [

                    'json' =>
                        true,

                    'success' =>
                        true,

                    'message' =>
                        $mensaje,

                    'eliminadas' =>
                        $eliminadas,

                    'total_no_leidas' =>
                        $totalNoLeidas

                ];
            }

            return controllerRedirect(
                '../views/notificaciones/index.php',
                $mensaje
            );
        },


        /* ==========================================================
           ELIMINAR UNA NOTIFICACIÓN
        ========================================================== */

        'eliminar' => function () use (
            $pdo,
            $esAjax
        ) {

            $usuarioId = usuarioId();

            if (
                $usuarioId === null ||
                $usuarioId <= 0
            ) {
                throw new Exception(
                    'No se pudo identificar al usuario actual.'
                );
            }

            $usuarioId = (int)$usuarioId;

            $notificacionId = (int)(
                $_POST['id'] ?? 0
            );

            if ($notificacionId <= 0) {
                throw new Exception(
                    'La notificación seleccionada no es válida.'
                );
            }

            eliminarNotificacion(
                $pdo,
                $notificacionId,
                $usuarioId
            );

            $totalNoLeidas =
                contarNotificacionesNoLeidas(
                    $pdo,
                    $usuarioId
                );

            if ($esAjax) {

                return [

                    'json' =>
                        true,

                    'success' =>
                        true,

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