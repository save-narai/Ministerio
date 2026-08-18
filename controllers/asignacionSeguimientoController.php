<?php

declare(strict_types=1);

require_once __DIR__ . '/controller.php';
require_once __DIR__ . '/../services/sessionService.php';
require_once __DIR__ . '/../services/asignacionSeguimientoService.php';


/* ==========================================================
   INICIALIZAR
========================================================== */

controllerInit();

$pdo = controllerPdo();


/* ==========================================================
   OBTENER PERÍODO DE UNA ASIGNACIÓN
========================================================== */

/*
 * Se utiliza para que, después de cancelar,
 * el sistema regrese exactamente al mismo
 * año/mes que estaba consultando el usuario.
 */

function obtenerPeriodoAsignacionParaRedirect(
    PDO $pdo,
    int $id
): array {

    if ($id <= 0) {

        throw new Exception(
            'Asignación inválida.'
        );
    }

    $stmt = $pdo->prepare("
        SELECT
            anio,
            mes
        FROM asignaciones_seguimiento
        WHERE id = :id
        LIMIT 1
    ");

    $stmt->execute([

        ':id' =>
            $id

    ]);

    $asignacion =
        $stmt->fetch(
            PDO::FETCH_ASSOC
        );

    if (!$asignacion) {

        throw new Exception(
            'La asignación no existe.'
        );
    }

    $anio =
        (int)(
            $asignacion['anio']
            ?? 0
        );

    $mes =
        (int)(
            $asignacion['mes']
            ?? 0
        );

    if (
        $anio < 2000 ||
        $anio > 2100 ||
        $mes < 1 ||
        $mes > 12
    ) {

        throw new Exception(
            'El período de la asignación no es válido.'
        );
    }

    return [

        'anio' =>
            $anio,

        'mes' =>
            $mes

    ];
}


/* ==========================================================
   CONTROLLER
========================================================== */

controllerRun(

    [

        /* ======================================================
           ASIGNAR UN JOVEN
        ====================================================== */

        'crear_asignacion' => function () use ($pdo) {

            $usuarioActual =
                usuarioId();

            if (
                $usuarioActual === null ||
                $usuarioActual <= 0
            ) {

                throw new Exception(
                    'No se pudo identificar al usuario actual.'
                );
            }

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

                (int)$usuarioActual,

                $anio,

                $mes,

                $observaciones

            );


            return controllerRedirect(

                '../views/seguimientos/asignaciones.php'
                . '?anio='
                . $anio
                . '&mes='
                . $mes,

                'Asignación creada correctamente.'

            );
        },


        /* ======================================================
           ASIGNAR VARIOS JÓVENES
        ====================================================== */

        'asignar_jovenes' => function () use ($pdo) {

            $usuarioActual =
                usuarioId();

            if (
                $usuarioActual === null ||
                $usuarioActual <= 0
            ) {

                throw new Exception(
                    'No se pudo identificar al usuario actual.'
                );
            }

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

            $jovenes =
                $_POST['joven_ids']
                ?? [];


            if (!is_array($jovenes)) {

                $jovenes = [
                    $jovenes
                ];
            }


            $jovenes =
                array_values(

                    array_unique(

                        array_filter(

                            array_map(
                                'intval',
                                $jovenes
                            ),

                            static fn ($id) =>
                                $id > 0

                        )

                    )

                );


            if (empty($jovenes)) {

                throw new Exception(
                    'Debes seleccionar al menos un joven.'
                );
            }


            $observaciones =
                $_POST['observaciones']
                ?? null;


            $cantidad = 0;


            foreach (
                $jovenes as $jovenId
            ) {

                crearAsignacionSeguimiento(

                    $pdo,

                    $jovenId,

                    $usuarioId,

                    (int)$usuarioActual,

                    $anio,

                    $mes,

                    $observaciones

                );

                $cantidad++;
            }


            return controllerRedirect(

                '../views/seguimientos/asignaciones.php'
                . '?anio='
                . $anio
                . '&mes='
                . $mes,

                $cantidad === 1

                    ? 'Joven asignado correctamente.'

                    : $cantidad
                        . ' jóvenes asignados correctamente.'

            );
        },


        /* ======================================================
           INICIAR ASIGNACIÓN
        ====================================================== */

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


        /* ======================================================
           CANCELAR UNA
        ====================================================== */

        'cancelar_asignacion' => function () use ($pdo) {

            $id =
                (int)(
                    $_POST['id']
                    ?? 0
                );


            /*
             * Antes de cancelar obtenemos el período real
             * de esa asignación.
             */

            $periodo =
                obtenerPeriodoAsignacionParaRedirect(
                    $pdo,
                    $id
                );


            /*
             * Cancelar elimina la asignación.
             * El joven vuelve automáticamente
             * a "Pendientes sin asignar".
             */

            cancelarAsignacionSeguimiento(
                $pdo,
                $id
            );


            return controllerRedirect(

                '../views/seguimientos/asignaciones.php'
                . '?anio='
                . $periodo['anio']
                . '&mes='
                . $periodo['mes'],

                'Asignación cancelada correctamente.'

            );
        },


        /* ======================================================
           CANCELAR VARIAS
        ====================================================== */

        'cancelar_asignaciones' => function () use ($pdo) {

            $ids =
                $_POST['ids']
                ?? [];


            if (!is_array($ids)) {

                $ids = [
                    $ids
                ];
            }


            $ids =
                array_values(

                    array_unique(

                        array_filter(

                            array_map(
                                'intval',
                                $ids
                            ),

                            static fn ($id) =>
                                $id > 0

                        )

                    )

                );


            if (empty($ids)) {

                throw new Exception(
                    'Debes seleccionar al menos una asignación.'
                );
            }


            /*
             * Todas las asignaciones seleccionadas
             * pertenecen al mismo período porque
             * provienen de la misma tabla filtrada.
             *
             * Tomamos el período de la primera.
             */

            $periodo =
                obtenerPeriodoAsignacionParaRedirect(

                    $pdo,

                    $ids[0]

                );


            $cantidad = 0;


            foreach (
                $ids as $id
            ) {

                cancelarAsignacionSeguimiento(

                    $pdo,

                    $id

                );

                $cantidad++;
            }


            return controllerRedirect(

                '../views/seguimientos/asignaciones.php'
                . '?anio='
                . $periodo['anio']
                . '&mes='
                . $periodo['mes'],

                $cantidad === 1

                    ? 'Asignación cancelada correctamente.'

                    : $cantidad
                        . ' asignaciones canceladas correctamente.'

            );
        }

    ],

    [

        'redirect' =>
            '../views/seguimientos/asignaciones.php'

    ]

);