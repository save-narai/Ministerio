<?php

declare(strict_types=1);

require_once __DIR__ . '/../middleware/permiso.php';
require_once __DIR__ . '/jovenService.php';
require_once __DIR__ . '/notificacionService.php';

/*
|--------------------------------------------------------------------------
| ASIGNACIÓN DE SEGUIMIENTOS SERVICE
|--------------------------------------------------------------------------
|
| Máquina de estados:
|
| SIN ASIGNAR
|      ↓
| PENDIENTE
|      ↓
| EN_PROCESO
|      ↓
| COMPLETADO
|
| La cancelación NO es un estado persistente.
|
| PENDIENTE / EN_PROCESO
|      ↓
| CANCELAR
|      ↓
| ELIMINAR ASIGNACIÓN
|      ↓
| SIN ASIGNAR
|
|--------------------------------------------------------------------------
*/


/* ==========================================================
   CONSTANTES
========================================================== */

const ESTADOS_ASIGNACION_SEGUIMIENTO = [

    'PENDIENTE',

    'EN_PROCESO',

    'COMPLETADO'

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

    $estadoActividad = strtoupper(
        trim(
            (string)(
                $joven['estado_actividad']
                ?? ''
            )
        )
    );

    if ($estadoActividad === 'ELIMINADO') {

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

    /*
     * 0 = TODOS LOS MESES
     * 1 - 12 = mes específico
     */

    if (
        $mes < 0 ||
        $mes > 12
    ) {

        throw new Exception(
            'El mes de la asignación no es válido.'
        );
    }
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

        LEFT JOIN usuarios ap
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
   OBTENER ASIGNACIÓN DEL JOVEN EN PERÍODO
========================================================== */

function obtenerAsignacionSeguimientoPeriodo(
    PDO $pdo,
    int $jovenId,
    int $anio,
    int $mes
): ?array {

    if ($jovenId <= 0) {

        return null;
    }

    if ($mes === 0) {

        return null;
    }

    validarPeriodoAsignacionSeguimiento(
        $anio,
        $mes
    );

    $stmt = $pdo->prepare("
        SELECT

            a.*,

            j.nombre_completo AS joven_nombre,

            u.nombre AS usuario_nombre,

            ap.nombre AS asignado_por_nombre

        FROM asignaciones_seguimiento a

        INNER JOIN jovenes j
            ON a.joven_id = j.id

        INNER JOIN usuarios u
            ON a.usuario_id = u.id

        LEFT JOIN usuarios ap
            ON a.asignado_por = ap.id

        WHERE a.joven_id = :joven_id

        AND a.anio = :anio

        AND a.mes = :mes

        ORDER BY a.id DESC

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
   OBTENER ASIGNACIÓN ACTIVA
========================================================== */

function obtenerAsignacionActivaSeguimiento(
    PDO $pdo,
    int $jovenId,
    int $anio,
    int $mes
): ?array {

    if ($jovenId <= 0) {

        return null;
    }

    if ($mes === 0) {

        return null;
    }

    validarPeriodoAsignacionSeguimiento(
        $anio,
        $mes
    );

    $stmt = $pdo->prepare("
        SELECT

            a.*,

            j.nombre_completo AS joven_nombre,

            u.nombre AS usuario_nombre,

            ap.nombre AS asignado_por_nombre

        FROM asignaciones_seguimiento a

        INNER JOIN jovenes j
            ON a.joven_id = j.id

        INNER JOIN usuarios u
            ON a.usuario_id = u.id

        LEFT JOIN usuarios ap
            ON a.asignado_por = ap.id

        WHERE a.joven_id = :joven_id

        AND a.anio = :anio

        AND a.mes = :mes

        AND a.estado IN (
            'PENDIENTE',
            'EN_PROCESO'
        )

        ORDER BY a.id DESC

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
   VALIDAR ACCESO
========================================================== */

function validarAccesoAsignacionSeguimiento(
    array $asignacion
): void {

    if (tienePermiso('asignar_seguimientos')) {

        return;
    }

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
   NOTIFICAR NUEVA ASIGNACIÓN
========================================================== */

function notificarNuevaAsignacionSeguimiento(
    PDO $pdo,
    array $asignacion
): void {

    $usuarioId =
        (int)(
            $asignacion['usuario_id']
            ?? 0
        );

    if ($usuarioId <= 0) {

        return;
    }

    $jovenNombre =
        (string)(
            $asignacion['joven_nombre']
            ?? 'Joven'
        );

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
            (int)(
                $asignacion['mes']
                ?? 0
            )
        ]
        ?? 'mes seleccionado';

    crearNotificacion(
        $pdo,
        [

            'usuario_id' =>
                $usuarioId,

            'tipo' =>
                'NUEVA_ASIGNACION',

            'titulo' =>
                'Nueva asignación de seguimiento',

            'mensaje' =>
                "Te asignaron el seguimiento de "
                . $jovenNombre
                . " para "
                . $mesNombre
                . " "
                . (
                    $asignacion['anio']
                    ?? ''
                )
                . ".",

            'joven_id' =>
                (int)(
                    $asignacion['joven_id']
                    ?? 0
                ),

            'asignacion_id' =>
                (int)(
                    $asignacion['id']
                    ?? 0
                )

        ]
    );
}


/* ==========================================================
   NOTIFICAR CAMBIO DE ESTADO
========================================================== */

function notificarCambioEstadoAsignacionSeguimiento(
    PDO $pdo,
    array $asignacion,
    string $estado
): void {

    $estado =
        validarEstadoAsignacionSeguimiento(
            $estado
        );

    $jovenNombre =
        (string)(
            $asignacion['joven_nombre']
            ?? 'este joven'
        );

    $asignacionId =
        (int)(
            $asignacion['id']
            ?? 0
        );

    $jovenId =
        (int)(
            $asignacion['joven_id']
            ?? 0
        );

    $destinatario =
        (int)(
            $asignacion['asignado_por']
            ?? 0
        );

    if ($destinatario <= 0) {

        $destinatario =
            (int)(
                $asignacion['usuario_id']
                ?? 0
            );
    }

    if ($destinatario <= 0) {

        return;
    }

    $tipo = null;

    $titulo = null;

    $mensaje = null;

    switch ($estado) {

        case 'EN_PROCESO':

            $tipo =
                'ASIGNACION_EN_PROCESO';

            $titulo =
                'Seguimiento iniciado';

            $mensaje =
                "El seguimiento de "
                . $jovenNombre
                . " fue iniciado.";

            break;


        case 'COMPLETADO':

            $tipo =
                'ASIGNACION_COMPLETADA';

            $titulo =
                'Seguimiento completado';

            $mensaje =
                "El seguimiento de "
                . $jovenNombre
                . " fue completado.";

            break;
    }

    if ($tipo === null) {

        return;
    }

    crearNotificacion(
        $pdo,
        [

            'usuario_id' =>
                $destinatario,

            'tipo' =>
                $tipo,

            'titulo' =>
                $titulo,

            'mensaje' =>
                $mensaje,

            'joven_id' =>
                $jovenId > 0
                    ? $jovenId
                    : null,

            'asignacion_id' =>
                $asignacionId > 0
                    ? $asignacionId
                    : null

        ]
    );
}


/* ==========================================================
   OBTENER ÚLTIMO SEGUIMIENTO FINALIZADO
========================================================== */

function obtenerUltimoSeguimientoFinalizado(
    PDO $pdo,
    int $jovenId
): ?array {

    if ($jovenId <= 0) {

        return null;
    }

    $stmt = $pdo->prepare("
        SELECT

            s.id,

            s.joven_id,

            s.fecha_contacto,

            s.modalidad_contacto,

            s.estado_proceso,

            s.responsable_id,

            u.nombre AS responsable_nombre

        FROM seguimientos s

        LEFT JOIN usuarios u
            ON s.responsable_id = u.id

        WHERE s.joven_id = :joven_id

        AND s.estado_proceso = 'FINALIZADO'

        AND s.fecha_contacto IS NOT NULL

        ORDER BY

            s.fecha_contacto DESC,

            s.id DESC

        LIMIT 1
    ");

    $stmt->execute([

        ':joven_id' =>
            $jovenId

    ]);

    return $stmt->fetch(
        PDO::FETCH_ASSOC
    ) ?: null;
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

    if ($mes === 0) {

        throw new Exception(
            'Debes seleccionar un mes específico para crear una asignación.'
        );
    }

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


    $observaciones =
        trim(
            (string)$observaciones
        );

    if (
        $observaciones === ''
    ) {

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


    $stmt = $pdo->prepare("
        SELECT

            id,

            estado

        FROM asignaciones_seguimiento

        WHERE joven_id = :joven_id

        AND anio = :anio

        AND mes = :mes

        LIMIT 1
    ");

    $stmt->execute([

        ':joven_id' =>
            $jovenId,

        ':anio' =>
            $anio,

        ':mes' =>
            $mes

    ]);

    $existente =
        $stmt->fetch(
            PDO::FETCH_ASSOC
        );


    if ($existente) {

        throw new Exception(
            'Este joven ya tiene una asignación para este período.'
        );
    }


    $seguimientoFinalizado =
        obtenerUltimoSeguimientoFinalizado(
            $pdo,
            $jovenId
        );


    $estadoInicial =
        $seguimientoFinalizado
            ? 'COMPLETADO'
            : 'PENDIENTE';


    $fechaCompletado =
        $seguimientoFinalizado
            ? date('Y-m-d H:i:s')
            : null;


    $transaccionPropia =
        !$pdo->inTransaction();


    if ($transaccionPropia) {

        $pdo->beginTransaction();
    }


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
                observaciones,
                fecha_completado
            )
            VALUES
            (
                :joven_id,
                :usuario_id,
                :asignado_por,
                :anio,
                :mes,
                :estado,
                :observaciones,
                :fecha_completado
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

            ':estado' =>
                $estadoInicial,

            ':observaciones' =>
                $observaciones,

            ':fecha_completado' =>
                $fechaCompletado

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
                'No se pudo recuperar la asignación.'
            );
        }


        if (
            $estadoInicial === 'PENDIENTE'
        ) {

            notificarNuevaAsignacionSeguimiento(
                $pdo,
                $asignacion
            );
        }


        if (
            $estadoInicial === 'COMPLETADO'
        ) {

            notificarCambioEstadoAsignacionSeguimiento(
                $pdo,
                $asignacion,
                'COMPLETADO'
            );
        }


        if ($transaccionPropia) {

            $pdo->commit();
        }


        return $asignacionId;


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
   SINCRONIZAR ASIGNACIÓN
========================================================== */

function sincronizarAsignacionConSeguimiento(
    PDO $pdo,
    int $asignacionId
): void {

    if ($asignacionId <= 0) {

        throw new Exception(
            'Asignación inválida.'
        );
    }


    $asignacion =
        obtenerAsignacionSeguimientoPorId(
            $pdo,
            $asignacionId
        );


    if (!$asignacion) {

        throw new Exception(
            'La asignación no existe.'
        );
    }


    $estadoActual =
        strtoupper(
            trim(
                (string)(
                    $asignacion['estado']
                    ?? ''
                )
            )
        );


    if (!in_array(
        $estadoActual,
        [
            'PENDIENTE',
            'EN_PROCESO'
        ],
        true
    )) {

        return;
    }


    $seguimientoFinalizado =
        obtenerUltimoSeguimientoFinalizado(
            $pdo,
            (int)$asignacion['joven_id']
        );


    if (!$seguimientoFinalizado) {

        return;
    }


    $stmt = $pdo->prepare("
        UPDATE asignaciones_seguimiento

        SET

            estado = 'COMPLETADO',

            fecha_completado = NOW()

        WHERE id = :id

        AND estado IN (
            'PENDIENTE',
            'EN_PROCESO'
        )
    ");

    $stmt->execute([

        ':id' =>
            $asignacionId

    ]);


    $actualizada =
        obtenerAsignacionSeguimientoPorId(
            $pdo,
            $asignacionId
        );


    if (!$actualizada) {

        throw new Exception(
            'No se pudo recuperar la asignación sincronizada.'
        );
    }


    if (
        $stmt->rowCount() > 0
    ) {

        notificarCambioEstadoAsignacionSeguimiento(
            $pdo,
            $actualizada,
            'COMPLETADO'
        );
    }
}


/* ==========================================================
   OBTENER ASIGNACIONES DEL PERÍODO
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


    $sql = "

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

        LEFT JOIN usuarios ap
            ON a.asignado_por = ap.id

        WHERE a.anio = :anio

    ";


    $params = [

        ':anio' =>
            $anio

    ];


    if ($mes > 0) {

        $sql .= "
            AND a.mes = :mes
        ";

        $params[':mes'] =
            $mes;
    }


    $sql .= "

        ORDER BY

            a.anio DESC,

            a.mes DESC,

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

    ";


    $stmt =
        $pdo->prepare(
            $sql
        );


    $stmt->execute(
        $params
    );


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


    if ($mes === 0) {

        $sql = "
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

            ORDER BY

                a.mes DESC,

                CASE a.estado

                    WHEN 'PENDIENTE'
                        THEN 1

                    WHEN 'EN_PROCESO'
                        THEN 2

                    WHEN 'COMPLETADO'
                        THEN 3

                    ELSE 4

                END,

                j.nombre_completo ASC
        ";

        $params = [

            ':usuario_id' =>
                $usuarioId,

            ':anio' =>
                $anio

        ];

    } else {

        $sql = "
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

                    ELSE 4

                END,

                j.nombre_completo ASC
        ";

        $params = [

            ':usuario_id' =>
                $usuarioId,

            ':anio' =>
                $anio,

            ':mes' =>
                $mes

        ];
    }


    $stmt =
        $pdo->prepare(
            $sql
        );


    $stmt->execute(
        $params
    );


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


    /*
     * ======================================================
     * MISMA BASE QUE SEGUIMIENTOS
     * ======================================================
     *
     * Activos + NUEVO
     *
     * Y además:
     *
     * - ingresaron este mes
     *   O
     * - nunca tienen FINALIZADO histórico.
     */


    $sql = "

        SELECT

            j.id,

            j.nombre_completo,

            j.telefono,

            j.genero,

            j.estado_espiritual,

            j.fecha_ingreso

        FROM jovenes j

        WHERE

            j.estado_actividad = 'ACTIVO'

        AND j.estado_espiritual = 'NUEVO'

        AND (

            (

                j.fecha_ingreso IS NOT NULL

                AND YEAR(j.fecha_ingreso) = YEAR(CURDATE())

                AND MONTH(j.fecha_ingreso) = MONTH(CURDATE())

            )

            OR

            NOT EXISTS (

                SELECT 1

                FROM seguimientos s

                WHERE s.joven_id = j.id

                AND s.estado_proceso = 'FINALIZADO'

                AND s.fecha_contacto IS NOT NULL

            )

        )


        /*
         * ==================================================
         * ASIGNACIONES
         * ==================================================
         *
         * mes = 0:
         *     no debe existir asignación en ningún mes
         *     del año seleccionado.
         *
         * mes > 0:
         *     no debe existir asignación en ese mes.
         */

        AND NOT EXISTS (

            SELECT 1

            FROM asignaciones_seguimiento a

            WHERE a.joven_id = j.id

            AND a.anio = :anio_asig

    ";


    /*
     * Cuando se consulta un mes específico,
     * agregamos el filtro del mes.
     *
     * Cuando mes = 0, no agregamos este filtro,
     * por lo que cualquier asignación del año
     * excluye al joven.
     */

    if ($mes > 0) {

        $sql .= "

            AND a.mes = :mes_asig

        ";

    }


    $sql .= "

        )


        /*
         * ==================================================
         * EXCEPCIONES
         * ==================================================
         *
         * Solo para un mes específico.
         */

    ";


    if ($mes > 0) {

        $sql .= "

            AND NOT EXISTS (

                SELECT 1

                FROM excepciones_seguimiento e

                WHERE e.joven_id = j.id

                AND e.anio = :anio_exc

                AND e.mes = :mes_exc

            )

        ";

    }


    $sql .= "

        ORDER BY

            CASE

                WHEN

                    j.fecha_ingreso IS NOT NULL

                    AND YEAR(j.fecha_ingreso) = YEAR(CURDATE())

                    AND MONTH(j.fecha_ingreso) = MONTH(CURDATE())

                THEN 0

                ELSE 1

            END,

            j.fecha_ingreso ASC,

            j.nombre_completo ASC

    ";


    $stmt =
        $pdo->prepare(
            $sql
        );


    /*
     * ======================================================
     * PARÁMETROS
     * ======================================================
     */

    $params = [

        ':anio_asig' =>
            $anio

    ];


    if ($mes > 0) {

        $params[':mes_asig'] =
            $mes;

        $params[':anio_exc'] =
            $anio;

        $params[':mes_exc'] =
            $mes;
    }


    $stmt->execute(
        $params
    );


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

    validarAccesoAsignacionSeguimiento(
        $asignacion
    );

    $estadoActual =
        strtoupper(
            trim(
                (string)(
                    $asignacion['estado']
                    ?? ''
                )
            )
        );


    $transiciones = [

        'PENDIENTE' => [
            'EN_PROCESO'
        ],

        'EN_PROCESO' => [],

        'COMPLETADO' => []

    ];


    if (
        !in_array(
            $estado,
            $transiciones[$estadoActual]
                ?? [],
            true
        )
    ) {

        throw new Exception(
            "No se puede cambiar una asignación de {$estadoActual} a {$estado}."
        );
    }


    $transaccionPropia =
        !$pdo->inTransaction();


    if ($transaccionPropia) {

        $pdo->beginTransaction();
    }


    try {

        $stmt = $pdo->prepare("
            UPDATE asignaciones_seguimiento

            SET

                estado = :estado,

                fecha_completado = NULL

            WHERE id = :id
        ");


        $stmt->execute([

            ':estado' =>
                $estado,

            ':id' =>
                $id

        ]);


        $asignacionActualizada =
            obtenerAsignacionSeguimientoPorId(
                $pdo,
                $id
            );


        if (!$asignacionActualizada) {

            throw new Exception(
                'No se pudo recuperar la asignación actualizada.'
            );
        }


        notificarCambioEstadoAsignacionSeguimiento(
            $pdo,
            $asignacionActualizada,
            $estado
        );


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
   CANCELAR ASIGNACIÓN
========================================================== */

function cancelarAsignacionSeguimiento(
    PDO $pdo,
    int $id
): void {

    exigirPermiso(
        'asignar_seguimientos'
    );


    if ($id <= 0) {

        throw new Exception(
            'Asignación inválida.'
        );
    }


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


    $estadoActual =
        strtoupper(
            trim(
                (string)(
                    $asignacion['estado']
                    ?? ''
                )
            )
        );


    if (!in_array(
        $estadoActual,
        [
            'PENDIENTE',
            'EN_PROCESO'
        ],
        true
    )) {

        throw new Exception(
            'Solo se pueden cancelar asignaciones pendientes o en proceso.'
        );
    }


    $transaccionPropia =
        !$pdo->inTransaction();


    if ($transaccionPropia) {

        $pdo->beginTransaction();
    }


    try {

        /*
         * Eliminar notificaciones relacionadas
         * antes de eliminar la asignación.
         */

        $stmt = $pdo->prepare("
            DELETE FROM notificaciones

            WHERE asignacion_id = :asignacion_id
        ");

        $stmt->execute([
            ':asignacion_id' => $id
        ]);


        /*
         * Cancelación:
         * la asignación desaparece y vuelve
         * al estado SIN ASIGNAR.
         */

        $stmt = $pdo->prepare("
            DELETE FROM asignaciones_seguimiento

            WHERE id = :id

            AND estado IN (
                'PENDIENTE',
                'EN_PROCESO'
            )
        ");

        $stmt->execute([
            ':id' => $id
        ]);


        if ($stmt->rowCount() === 0) {

            throw new Exception(
                'No se pudo cancelar la asignación.'
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
   INICIAR ASIGNACIÓN
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
