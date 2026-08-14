<?php

declare(strict_types=1);

require_once __DIR__ . '/controller.php';
require_once __DIR__ . '/../services/sessionService.php';
require_once __DIR__ . '/../services/asignacionSeguimientoService.php';


/* ==========================================================
   INICIALIZAR
========================================================== */

controllerInit();

$pdo =
    controllerPdo();


/* ==========================================================
   VALIDAR ASIGNACIÓN CANCELADA
========================================================== */

function validarAsignacionCanceladaParaEliminar(
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

            id,

            joven_id,

            estado

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

    $estado =
        strtoupper(
            trim(
                (string)(
                    $asignacion['estado']
                    ?? ''
                )
            )
        );

    if ($estado !== 'CANCELADO') {

        throw new Exception(
            'Solo se pueden eliminar asignaciones canceladas.'
        );
    }

    return $asignacion;
}


/* ==========================================================
   ELIMINAR CANCELADA
========================================================== */

function eliminarAsignacionCancelada(
    PDO $pdo,
    int $id
): void {

    exigirPermiso(
        'asignar_seguimientos'
    );

    validarAsignacionCanceladaParaEliminar(
        $pdo,
        $id
    );

    $transaccionPropia =
        !$pdo->inTransaction();

    if ($transaccionPropia) {

        $pdo->beginTransaction();
    }

    try {

        /*
         * Primero eliminamos las notificaciones
         * relacionadas con esta asignación.
         */

        $stmt = $pdo->prepare("
            DELETE FROM notificaciones

            WHERE asignacion_id = :asignacion_id
        ");

        $stmt->execute([

            ':asignacion_id' =>
                $id

        ]);

        /*
         * Después eliminamos la asignación.
         */

        $stmt = $pdo->prepare("
            DELETE FROM asignaciones_seguimiento

            WHERE id = :id

            AND estado = 'CANCELADO'
        ");

        $stmt->execute([

            ':id' =>
                $id

        ]);

        if (
            $stmt->rowCount() === 0
        ) {

            throw new Exception(
                'No se pudo eliminar la asignación cancelada.'
            );
        }

        if ($transaccionPropia) {

            $pdo->commit();
        }

    } catch (Throwable $e) {

        if (
            $transaccionPropia &&
            $pdo->inTransaction()
        ) {

            $pdo->rollBack();
        }

        throw $e;
    }
}


/* ==========================================================
   ELIMINAR VARIAS CANCELADAS
========================================================== */

function eliminarAsignacionesCanceladas(
    PDO $pdo,
    array $ids
): int {

    exigirPermiso(
        'asignar_seguimientos'
    );

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
            'Debes seleccionar al menos una asignación cancelada.'
        );
    }

    $transaccionPropia =
        !$pdo->inTransaction();

    if ($transaccionPropia) {

        $pdo->beginTransaction();
    }

    try {

        $cantidad = 0;

        foreach ($ids as $id) {

            eliminarAsignacionCancelada(
                $pdo,
                $id
            );

            $cantidad++;
        }

        if ($transaccionPropia) {

            $pdo->commit();
        }

        return $cantidad;

    } catch (Throwable $e) {

        if (
            $transaccionPropia &&
            $pdo->inTransaction()
        ) {

            $pdo->rollBack();
        }

        throw $e;
    }
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

            cancelarAsignacionSeguimiento(
                $pdo,
                $id
            );

            return controllerRedirect(

                '../views/seguimientos/asignaciones.php',

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

            $cantidad = 0;

            foreach ($ids as $id) {

                cancelarAsignacionSeguimiento(
                    $pdo,
                    $id
                );

                $cantidad++;
            }

            return controllerRedirect(

                '../views/seguimientos/asignaciones.php',

                $cantidad === 1

                    ? 'Asignación cancelada correctamente.'

                    : $cantidad
                        . ' asignaciones canceladas correctamente.'

            );
        },


        /* ======================================================
           ELIMINAR UNA CANCELADA
        ====================================================== */

        'eliminar_asignacion_cancelada' =>
            function () use ($pdo) {

                $id =
                    (int)(
                        $_POST['id']
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

                eliminarAsignacionCancelada(
                    $pdo,
                    $id
                );

                return controllerRedirect(

                    '../views/seguimientos/asignaciones.php'
                    . '?anio='
                    . $anio
                    . '&mes='
                    . $mes,

                    'Asignación cancelada eliminada correctamente.'

                );
            },


        /* ======================================================
           ELIMINAR VARIAS CANCELADAS
        ====================================================== */

        'eliminar_asignaciones_canceladas' =>
            function () use ($pdo) {

                $ids =
                    $_POST['ids']
                    ?? [];

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

                if (!is_array($ids)) {

                    $ids = [
                        $ids
                    ];
                }

                $cantidad =
                    eliminarAsignacionesCanceladas(
                        $pdo,
                        $ids
                    );

                return controllerRedirect(

                    '../views/seguimientos/asignaciones.php'
                    . '?anio='
                    . $anio
                    . '&mes='
                    . $mes,

                    $cantidad === 1

                        ? 'Asignación cancelada eliminada correctamente.'

                        : $cantidad
                            . ' asignaciones canceladas eliminadas correctamente.'

                );
            }

    ],

    [

        'redirect' =>
            '../views/seguimientos/asignaciones.php'

    ]

);