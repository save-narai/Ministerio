<?php

declare(strict_types=1);

require_once __DIR__ . '/../middleware/permiso.php';
require_once __DIR__ . '/jovenService.php';
require_once __DIR__ . '/notificacionService.php';


/*
|--------------------------------------------------------------------------
| Asignación de Seguimientos Service
|--------------------------------------------------------------------------
|
| Gestiona:
|
| 1. Jóvenes pendientes de seguimiento.
| 2. Asignación de jóvenes a usuarios.
| 3. Consulta de asignaciones.
| 4. Cambio de estados.
| 5. Cancelación de asignaciones.
| 6. Notificaciones relacionadas con asignaciones.
|
*/


/* ==========================================================
   CONSTANTES
========================================================== */

const ESTADOS_ASIGNACION_SEGUIMIENTO = [

    'PENDIENTE',

    'EN_PROCESO',

    'COMPLETADO',

    'CANCELADO'

];


/* ==========================================================
   VALIDAR ESTADO
========================================================== */

function validarEstadoAsignacionSeguimiento(
    string $estado
): string {

    $estado = strtoupper(
        trim($estado)
    );

    if (!in_array(
        $estado,
        ESTADOS_ASIGNACION_SEGUIMIENTO,
        true
    )) {

        throw new Exception(
            'Estado de asignación inválido.'
        );

    }

    return $estado;
}


/* ==========================================================
   VALIDAR JOVEN
========================================================== */

function validarJovenAsignacionSeguimiento(
    PDO $pdo,
    int $jovenId
): array {

    if ($jovenId <= 0) {

        throw new Exception(
            'Joven inválido.'
        );

    }

    $joven = obtenerJovenPorId(
        $pdo,
        $jovenId
    );

    if (!$joven) {

        throw new Exception(
            'El joven no existe.'
        );

    }

    if (
        strtoupper(
            trim(
                (string)(
                    $joven['estado_actividad']
                    ?? ''
                )
            )
        ) === 'ELIMINADO'
    ) {

        throw new Exception(
            'No se puede asignar un joven eliminado.'
        );

    }

    return $joven;
}


/* ==========================================================
   VALIDAR USUARIO RESPONSABLE
========================================================== */

function validarUsuarioAsignacionSeguimiento(
    PDO $pdo,
    int $usuarioId
): array {

    if ($usuarioId <= 0) {

        throw new Exception(
            'Usuario responsable inválido.'
        );

    }

    $stmt = $pdo->prepare("
        SELECT
            id,
            nombre,
            usuario,
            rol_id,
            activo
        FROM usuarios
        WHERE id = :id
        LIMIT 1
    ");

    $stmt->execute([
        ':id' => $usuarioId
    ]);

    $usuario = $stmt->fetch(
        PDO::FETCH_ASSOC
    );

    if (!$usuario) {

        throw new Exception(
            'El usuario responsable no existe.'
        );

    }

    if ((int)$usuario['activo'] !== 1) {

        throw new Exception(
            'El usuario seleccionado está inactivo.'
        );

    }

    return $usuario;
}


/* ==========================================================
   VALIDAR PERÍODO
========================================================== */

function validarPeriodoAsignacionSeguimiento(
    int $anio,
    int $mes
): void {

    if (
        $anio < 2000 ||
        $anio > 2100
    ) {

        throw new Exception(
            'El año de la asignación no es válido.'
        );

    }

    if (
        $mes < 1 ||
        $mes > 12
    ) {

        throw new Exception(
            'El mes de la asignación no es válido.'
        );

    }
}


/* ==========================================================
   EXISTE ASIGNACIÓN
========================================================== */

function existeAsignacionSeguimiento(
    PDO $pdo,
    int $jovenId,
    int $anio,
    int $mes,
    ?int $exceptoId = null
): bool {

    if (
        $jovenId <= 0 ||
        $anio < 2000 ||
        $mes < 1 ||
        $mes > 12
    ) {

        return false;

    }

    $sql = "
        SELECT id
        FROM asignaciones_seguimiento
        WHERE joven_id = :joven_id
        AND anio = :anio
        AND mes = :mes
    ";

    $params = [

        ':joven_id' => $jovenId,

        ':anio' => $anio,

        ':mes' => $mes

    ];

    if ($exceptoId !== null) {

        $sql .= "
            AND id <> :excepto_id
        ";

        $params[':excepto_id'] =
            $exceptoId;

    }

    $sql .= "
        LIMIT 1
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute($params);

    return (bool)$stmt->fetchColumn();
}


/* ==========================================================
   OBTENER ASIGNACIÓN POR ID
========================================================== */

function obtenerAsignacionSeguimientoPorId(
    PDO $pdo,
    int $id
): ?array {

    if ($id <= 0) {

        return null;

    }

    $stmt = $pdo->prepare("
        SELECT

            a.*,

            j.nombre_completo AS joven_nombre,

            j.telefono AS joven_telefono,

            j.genero AS joven_genero,

            j.estado_espiritual,

            u.nombre AS usuario_nombre,

            u.usuario AS usuario_login,

            r.nombre AS rol_nombre,

            ap.nombre AS asignado_por_nombre

        FROM asignaciones_seguimiento a

        INNER JOIN jovenes j
            ON a.joven_id = j.id

        INNER JOIN usuarios u
            ON a.usuario_id = u.id

        LEFT JOIN roles r
            ON u.rol_id = r.id

        INNER JOIN usuarios ap
            ON a.asignado_por = ap.id

        WHERE a.id = :id

        LIMIT 1
    ");

    $stmt->execute([
        ':id' => $id
    ]);

    return $stmt->fetch(
        PDO::FETCH_ASSOC
    ) ?: null;
}


/* ==========================================================
   OBTENER ASIGNACIÓN DEL JOVEN EN UN PERÍODO
========================================================== */

function obtenerAsignacionSeguimientoPeriodo(
    PDO $pdo,
    int $jovenId,
    int $anio,
    int $mes
): ?array {

    if (
        $jovenId <= 0 ||
        $anio < 2000 ||
        $mes < 1 ||
        $mes > 12
    ) {

        return null;

    }

    $stmt = $pdo->prepare("
        SELECT

            a.*,

            j.nombre_completo AS joven_nombre,

            j.telefono AS joven_telefono,

            j.genero AS joven_genero,

            j.estado_espiritual,

            u.nombre AS usuario_nombre,

            ap.nombre AS asignado_por_nombre

        FROM asignaciones_seguimiento a

        INNER JOIN jovenes j
            ON a.joven_id = j.id

        INNER JOIN usuarios u
            ON a.usuario_id = u.id

        INNER JOIN usuarios ap
            ON a.asignado_por = ap.id

        WHERE a.joven_id = :joven_id

        AND a.anio = :anio

        AND a.mes = :mes

        LIMIT 1
    ");

    $stmt->execute([

        ':joven_id' => $jovenId,

        ':anio' => $anio,

        ':mes' => $mes

    ]);

    return $stmt->fetch(
        PDO::FETCH_ASSOC
    ) ?: null;
}


/* ==========================================================
   VALIDAR PERMISO SOBRE ASIGNACIÓN
========================================================== */

function validarAccesoAsignacionSeguimiento(
    array $asignacion
): void {

    /*
     * Quien tenga permiso de asignar seguimientos
     * puede gestionar cualquier asignación.
     */

    if (tienePermiso('asignar_seguimientos')) {

        return;

    }

    /*
     * Un usuario normal solamente puede gestionar
     * una asignación que le fue entregada.
     */

    $usuarioActual = usuarioId();

    if (
        $usuarioActual === null ||
        $usuarioActual <= 0
    ) {

        throw new Exception(
            'No se pudo identificar al usuario actual.'
        );

    }

    if (
        (int)$asignacion['usuario_id']
        !==
        (int)$usuarioActual
    ) {

        throw new RuntimeException(
            'No tienes permiso para gestionar esta asignación.'
        );

    }
}


/* ==========================================================
   CREAR NOTIFICACIÓN DE ASIGNACIÓN
========================================================== */

function notificarNuevaAsignacionSeguimiento(
    PDO $pdo,
    array $asignacion
): void {

    $jovenNombre =
        $asignacion['joven_nombre']
        ?? 'Joven';


    $meses = [

        1  => 'Enero',
        2  => 'Febrero',
        3  => 'Marzo',
        4  => 'Abril',
        5  => 'Mayo',
        6  => 'Junio',
        7  => 'Julio',
        8  => 'Agosto',
        9  => 'Septiembre',
        10 => 'Octubre',
        11 => 'Noviembre',
        12 => 'Diciembre'

    ];


    $mesNombre =
        $meses[
            (int)$asignacion['mes']
        ]
        ?? 'mes seleccionado';


    crearNotificacion(

        $pdo,

        [

            'usuario_id' =>
                (int)$asignacion['usuario_id'],

            'tipo' =>
                'NUEVA_ASIGNACION',

            'titulo' =>
                'Nueva asignación de seguimiento',

            'mensaje' =>
                "Te asignaron el seguimiento de "
                . "{$jovenNombre} para "
                . "{$mesNombre} "
                . "{$asignacion['anio']}.",

            'joven_id' =>
                (int)$asignacion['joven_id'],

            'asignacion_id' =>
                (int)$asignacion['id']

        ]

    );
}


/* ==========================================================
   CREAR NOTIFICACIÓN DE CAMBIO DE ESTADO
========================================================== */

function notificarCambioEstadoAsignacionSeguimiento(
    PDO $pdo,
    array $asignacion,
    string $estado
): void {

    $jovenNombre =
        $asignacion['joven_nombre']
        ?? 'Joven';


    $tipoNotificacion = null;

    $titulo = null;

    $mensaje = null;


    switch ($estado) {

        case 'EN_PROCESO':

            $tipoNotificacion =
                'ASIGNACION_EN_PROCESO';

            $titulo =
                'Seguimiento iniciado';

            $mensaje =
                "El seguimiento de "
                . "{$jovenNombre} "
                . "se encuentra en proceso.";

            break;


        case 'COMPLETADO':

            $tipoNotificacion =
                'ASIGNACION_COMPLETADA';

            $titulo =
                'Seguimiento completado';

            $mensaje =
                "El seguimiento de "
                . "{$jovenNombre} "
                . "fue marcado como completado.";

            break;


        case 'CANCELADO':

            $tipoNotificacion =
                'ASIGNACION_CANCELADA';

            $titulo =
                'Asignación cancelada';

            $mensaje =
                "La asignación de "
                . "{$jovenNombre} "
                . "fue cancelada.";

            break;

    }


    if ($tipoNotificacion === null) {

        return;

    }


    crearNotificacion(

        $pdo,

        [

            'usuario_id' =>
                (int)$asignacion['usuario_id'],

            'tipo' =>
                $tipoNotificacion,

            'titulo' =>
                $titulo,

            'mensaje' =>
                $mensaje,

            'joven_id' =>
                (int)$asignacion['joven_id'],

            'asignacion_id' =>
                (int)$asignacion['id']

        ]

    );
}


/* ==========================================================
   CREAR ASIGNACIÓN
========================================================== */

function crearAsignacionSeguimiento(
    PDO $pdo,
    int $jovenId,
    int $usuarioId,
    int $asignadoPor,
    int $anio,
    int $mes,
    ?string $observaciones = null
): int {

    exigirPermiso(
        'asignar_seguimientos'
    );


    validarPeriodoAsignacionSeguimiento(
        $anio,
        $mes
    );


    validarJovenAsignacionSeguimiento(
        $pdo,
        $jovenId
    );


    validarUsuarioAsignacionSeguimiento(
        $pdo,
        $usuarioId
    );


    validarUsuarioAsignacionSeguimiento(
        $pdo,
        $asignadoPor
    );


    if (
        existeAsignacionSeguimiento(
            $pdo,
            $jovenId,
            $anio,
            $mes
        )
    ) {

        throw new Exception(
            'Este joven ya tiene una asignación para este mes.'
        );

    }


    $observaciones =
        trim(
            (string)$observaciones
        );


    if ($observaciones === '') {

        $observaciones = null;

    }


    if (
        $observaciones !== null &&
        mb_strlen($observaciones) > 2000
    ) {

        throw new Exception(
            'Las observaciones de la asignación son demasiado largas.'
        );

    }


    $pdo->beginTransaction();


    try {

        $stmt = $pdo->prepare("
            INSERT INTO asignaciones_seguimiento
            (
                joven_id,
                usuario_id,
                asignado_por,
                anio,
                mes,
                estado,
                observaciones
            )
            VALUES
            (
                :joven_id,
                :usuario_id,
                :asignado_por,
                :anio,
                :mes,
                'PENDIENTE',
                :observaciones
            )
        ");


        $stmt->execute([

            ':joven_id' =>
                $jovenId,

            ':usuario_id' =>
                $usuarioId,

            ':asignado_por' =>
                $asignadoPor,

            ':anio' =>
                $anio,

            ':mes' =>
                $mes,

            ':observaciones' =>
                $observaciones

        ]);


        $asignacionId =
            (int)$pdo->lastInsertId();


        $asignacion =
            obtenerAsignacionSeguimientoPorId(
                $pdo,
                $asignacionId
            );


        if (!$asignacion) {

            throw new Exception(
                'No se pudo recuperar la asignación creada.'
            );

        }


        /*
         * Notificar al usuario responsable.
         */

        notificarNuevaAsignacionSeguimiento(
            $pdo,
            $asignacion
        );


        $pdo->commit();


        return $asignacionId;


    } catch (Throwable $e) {

        if ($pdo->inTransaction()) {

            $pdo->rollBack();

        }

        throw $e;

    }
}


/* ==========================================================
   OBTENER ASIGNACIONES DEL MES
========================================================== */

function obtenerAsignacionesSeguimientoMes(
    PDO $pdo,
    int $anio,
    int $mes
): array {

    validarPeriodoAsignacionSeguimiento(
        $anio,
        $mes
    );


    $stmt = $pdo->prepare("
        SELECT

            a.*,

            j.nombre_completo AS joven_nombre,

            j.telefono AS joven_telefono,

            j.genero AS joven_genero,

            j.estado_espiritual,

            u.nombre AS usuario_nombre,

            r.nombre AS rol_nombre,

            ap.nombre AS asignado_por_nombre

        FROM asignaciones_seguimiento a

        INNER JOIN jovenes j
            ON a.joven_id = j.id

        INNER JOIN usuarios u
            ON a.usuario_id = u.id

        LEFT JOIN roles r
            ON u.rol_id = r.id

        INNER JOIN usuarios ap
            ON a.asignado_por = ap.id

        WHERE a.anio = :anio

        AND a.mes = :mes

        ORDER BY

            CASE a.estado

                WHEN 'PENDIENTE'
                    THEN 1

                WHEN 'EN_PROCESO'
                    THEN 2

                WHEN 'COMPLETADO'
                    THEN 3

                WHEN 'CANCELADO'
                    THEN 4

                ELSE 5

            END,

            j.nombre_completo ASC
    ");


    $stmt->execute([

        ':anio' => $anio,

        ':mes' => $mes

    ]);


    return $stmt->fetchAll(
        PDO::FETCH_ASSOC
    );
}


/* ==========================================================
   OBTENER ASIGNACIONES DE UN USUARIO
========================================================== */

function obtenerAsignacionesUsuario(
    PDO $pdo,
    int $usuarioId,
    int $anio,
    int $mes
): array {

    validarPeriodoAsignacionSeguimiento(
        $anio,
        $mes
    );


    validarUsuarioAsignacionSeguimiento(
        $pdo,
        $usuarioId
    );


    $stmt = $pdo->prepare("
        SELECT

            a.*,

            j.nombre_completo AS joven_nombre,

            j.telefono AS joven_telefono,

            j.genero AS joven_genero,

            j.estado_espiritual

        FROM asignaciones_seguimiento a

        INNER JOIN jovenes j
            ON a.joven_id = j.id

        WHERE a.usuario_id = :usuario_id

        AND a.anio = :anio

        AND a.mes = :mes

        ORDER BY

            CASE a.estado

                WHEN 'PENDIENTE'
                    THEN 1

                WHEN 'EN_PROCESO'
                    THEN 2

                WHEN 'COMPLETADO'
                    THEN 3

                WHEN 'CANCELADO'
                    THEN 4

                ELSE 5

            END,

            j.nombre_completo ASC
    ");


    $stmt->execute([

        ':usuario_id' =>
            $usuarioId,

        ':anio' =>
            $anio,

        ':mes' =>
            $mes

    ]);


    return $stmt->fetchAll(
        PDO::FETCH_ASSOC
    );
}


/* ==========================================================
   OBTENER JÓVENES PENDIENTES SIN ASIGNAR
========================================================== */

function obtenerJovenesPendientesSinAsignar(
    PDO $pdo,
    int $anio,
    int $mes
): array {

    validarPeriodoAsignacionSeguimiento(
        $anio,
        $mes
    );


    $stmt = $pdo->prepare("
        SELECT

            j.id,

            j.nombre_completo,

            j.telefono,

            j.genero,

            j.estado_espiritual

        FROM jovenes j

        LEFT JOIN asignaciones_seguimiento a

            ON a.joven_id = j.id

            AND a.anio = :anio

            AND a.mes = :mes

        WHERE

            j.estado_actividad = 'ACTIVO'

        AND j.estado_espiritual = 'NUEVO'

        AND a.id IS NULL


        AND NOT EXISTS (

            SELECT 1

            FROM seguimientos s

            WHERE

                s.joven_id = j.id

                AND MONTH(
                    s.fecha_contacto
                ) = :mes_seguimiento

                AND YEAR(
                    s.fecha_contacto
                ) = :anio_seguimiento

        )


        AND NOT EXISTS (

            SELECT 1

            FROM excepciones_seguimiento e

            WHERE

                e.joven_id = j.id

                AND e.mes = :mes_excepcion

                AND e.anio = :anio_excepcion

        )


        ORDER BY
            j.nombre_completo ASC
    ");


    $stmt->execute([

        ':anio' =>
            $anio,

        ':mes' =>
            $mes,

        ':mes_seguimiento' =>
            $mes,

        ':anio_seguimiento' =>
            $anio,

        ':mes_excepcion' =>
            $mes,

        ':anio_excepcion' =>
            $anio

    ]);


    return $stmt->fetchAll(
        PDO::FETCH_ASSOC
    );
}


/* ==========================================================
   CAMBIAR ESTADO
========================================================== */

function cambiarEstadoAsignacionSeguimiento(
    PDO $pdo,
    int $id,
    string $estado
): void {

    $estado =
        validarEstadoAsignacionSeguimiento(
            $estado
        );


    $asignacion =
        obtenerAsignacionSeguimientoPorId(
            $pdo,
            $id
        );


    if (!$asignacion) {

        throw new Exception(
            'La asignación no existe.'
        );

    }


    /*
     * Admin / líder / sublíder con permiso:
     * pueden gestionar cualquier asignación.
     *
     * Usuario normal:
     * solamente su propia asignación.
     */

    validarAccesoAsignacionSeguimiento(
        $asignacion
    );


    $fechaCompletado = null;


    if ($estado === 'COMPLETADO') {

        $fechaCompletado =
            date('Y-m-d H:i:s');

    }


    $pdo->beginTransaction();


    try {

        $stmt = $pdo->prepare("
            UPDATE asignaciones_seguimiento

            SET

                estado = :estado,

                fecha_completado = :fecha_completado

            WHERE id = :id
        ");


        $stmt->execute([

            ':estado' =>
                $estado,

            ':fecha_completado' =>
                $fechaCompletado,

            ':id' =>
                $id

        ]);


        /*
         * Actualizamos la información para notificación.
         */

        $asignacionActualizada =
            obtenerAsignacionSeguimientoPorId(
                $pdo,
                $id
            );


        if (
            !$asignacionActualizada
        ) {

            throw new Exception(
                'No se pudo recuperar la asignación actualizada.'
            );

        }


        /*
         * Notificar solo cuando el estado genera
         * una notificación relevante.
         */

        notificarCambioEstadoAsignacionSeguimiento(

            $pdo,

            $asignacionActualizada,

            $estado

        );


        $pdo->commit();


    } catch (Throwable $e) {

        if ($pdo->inTransaction()) {

            $pdo->rollBack();

        }

        throw $e;

    }
}


/* ==========================================================
   CANCELAR ASIGNACIÓN
========================================================== */

function cancelarAsignacionSeguimiento(
    PDO $pdo,
    int $id
): void {

    /*
     * Solo quien puede administrar asignaciones
     * puede cancelarlas.
     */

    exigirPermiso(
        'asignar_seguimientos'
    );


    cambiarEstadoAsignacionSeguimiento(
        $pdo,
        $id,
        'CANCELADO'
    );
}


/* ==========================================================
   COMPLETAR ASIGNACIÓN
========================================================== */

function completarAsignacionSeguimiento(
    PDO $pdo,
    int $id
): void {

    cambiarEstadoAsignacionSeguimiento(
        $pdo,
        $id,
        'COMPLETADO'
    );
}


/* ==========================================================
   MARCAR EN PROCESO
========================================================== */

function iniciarAsignacionSeguimiento(
    PDO $pdo,
    int $id
): void {

    cambiarEstadoAsignacionSeguimiento(
        $pdo,
        $id,
        'EN_PROCESO'
    );
}