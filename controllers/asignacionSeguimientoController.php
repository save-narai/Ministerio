<?php

declare(strict_types=1);
require_once __DIR__ . '/controller.php';
require_once __DIR__ . '/../services/sessionService.php';
require_once __DIR__ . '/../services/asignacionSeguimientoService.php';

controllerInit();

$pdo = controllerPdo();

/*
|--------------------------------------------------------------------------
| CONTROLADOR DE ASIGNACIONES DE SEGUIMIENTO
|--------------------------------------------------------------------------
|
| Gestiona:
|
| - Crear una asignación individual.
| - Asignar varios jóvenes a un usuario.
| - Iniciar una asignación.
| - Completar una asignación.
| - Cancelar una asignación.
|
*/


controllerRun(

    [

        /* ==================================================
           CREAR ASIGNACIÓN INDIVIDUAL
        ================================================== */

        'crear_asignacion' => function () use ($pdo) {

            $usuarioActual =
                (int)(
                    $_SESSION['usuario']['id']
                    ?? $_SESSION['user_id']
                    ?? 0
                );

            $jovenId =
                (int)(
                    $_POST['joven_id']
                    ?? 0
                );

            $usuarioId =
                (int)(
                    $_POST['usuario_id']
                    ?? 0
                );

            $anio =
                (int)(
                    $_POST['anio']
                    ?? date('Y')
                );

            $mes =
                (int)(
                    $_POST['mes']
                    ?? date('m')
                );

            $observaciones =
                $_POST['observaciones']
                ?? null;


            crearAsignacionSeguimiento(

                $pdo,

                $jovenId,

                $usuarioId,

                $usuarioActual,

                $anio,

                $mes,

                $observaciones

            );


            return controllerRedirect(

                '../views/seguimientos/asignaciones.php',

                'Asignación creada correctamente.'

            );

        },


/* ==================================================
   ASIGNAR VARIOS JÓVENES
================================================== */

'asignar_jovenes' => function () use ($pdo) {

    /* ==========================================
       USUARIO ACTUAL
    ========================================== */

    $usuarioActual = usuarioId();

    if (
        $usuarioActual === null ||
        $usuarioActual <= 0
    ) {

        throw new Exception(
            'No se pudo identificar al usuario actual.'
        );

    }

    $usuarioActual = (int)$usuarioActual;


    /* ==========================================
       USUARIO RESPONSABLE
    ========================================== */

    $usuarioId = (int)(
        $_POST['usuario_id']
        ?? 0
    );


    /* ==========================================
       PERÍODO
    ========================================== */

    $anio = (int)(
        $_POST['anio']
        ?? date('Y')
    );

    $mes = (int)(
        $_POST['mes']
        ?? date('m')
    );


    /* ==========================================
       JÓVENES SELECCIONADOS
    ========================================== */

    $jovenes =
        $_POST['joven_ids']
        ?? [];


    if (!is_array($jovenes)) {

        $jovenes = [

            $jovenes

        ];

    }


    


    /* ==========================================
       LIMPIAR IDS
    ========================================== */

    $jovenes = array_values(

        array_unique(

            array_filter(

                array_map(
                    'intval',
                    $jovenes
                ),

                fn ($id) =>
                    $id > 0

            )

        )

    );


    /* ==========================================
       VALIDAR SELECCIÓN
    ========================================== */

    if (empty($jovenes)) {

        throw new Exception(
            'Debes seleccionar al menos un joven.'
        );

    }


    /* ==========================================
       TRANSACCIÓN
    ========================================== */

    $pdo->beginTransaction();

    try {

        $cantidad = 0;


        foreach ($jovenes as $jovenId) {

            crearAsignacionSeguimiento(

                $pdo,

                $jovenId,

                $usuarioId,

                $usuarioActual,

                $anio,

                $mes,

                $_POST['observaciones']
                    ?? null

            );

            $cantidad++;

        }


        $pdo->commit();


        return controllerRedirect(

            '../views/seguimientos/asignaciones.php',

            $cantidad === 1

                ? 'Joven asignado correctamente.'

                : $cantidad .
                    ' jóvenes asignados correctamente.'

        );


    } catch (Throwable $e) {

        if ($pdo->inTransaction()) {

            $pdo->rollBack();

        }

        throw $e;

    }

},

        /* ==================================================
           INICIAR ASIGNACIÓN
        ================================================== */

        'iniciar_asignacion' => function () use ($pdo) {

            $id =
                (int)(
                    $_POST['id']
                    ?? 0
                );


            iniciarAsignacionSeguimiento(

                $pdo,

                $id

            );


            return controllerRedirect(

                '../views/seguimientos/mis-seguimientos.php',

                'Seguimiento marcado como en proceso.'

            );

        },


        /* ==================================================
           COMPLETAR ASIGNACIÓN
        ================================================== */

        'completar_asignacion' => function () use ($pdo) {

            $id =
                (int)(
                    $_POST['id']
                    ?? 0
                );


            completarAsignacionSeguimiento(

                $pdo,

                $id

            );


            return controllerRedirect(

                '../views/seguimientos/mis-seguimientos.php',

                'Asignación completada correctamente.'

            );

        },


        /* ==================================================
           CANCELAR ASIGNACIÓN
        ================================================== */

        'cancelar_asignacion' => function () use ($pdo) {

            $id =
                (int)(
                    $_POST['id']
                    ?? 0
                );


            cancelarAsignacionSeguimiento(

                $pdo,

                $id

            );


            return controllerRedirect(

                '../views/seguimientos/asignaciones.php',

                'Asignación cancelada correctamente.'

            );

        },

        'cancelar_asignaciones' => function () use ($pdo) {

            $asignaciones = $_POST['asignacion_ids'] ?? [];

            if (!is_array($asignaciones)) {
                $asignaciones = [$asignaciones];
            }

            // Limpiar ids
            $asignaciones = array_values(array_unique(array_filter(array_map('intval', $asignaciones), fn($id) => $id > 0)));

            if (empty($asignaciones)) {
                throw new Exception('Debes seleccionar al menos una asignación.');
            }

            $pdo->beginTransaction();

            try {
                $cantidad = 0;

                foreach ($asignaciones as $id) {
                    cancelarAsignacionSeguimiento($pdo, $id);
                    $cantidad++;
                }

                $pdo->commit();

                return controllerRedirect(
                    '../views/seguimientos/asignaciones.php',
                    $cantidad === 1
                        ? 'Asignación cancelada correctamente.'
                        : $cantidad . ' asignación(es) cancelada(s) correctamente.'
                );

            } catch (Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                throw $e;
            }

        }

    ]
);