<?php

declare(strict_types=1);

require_once __DIR__ . '/sessionService.php';
require_once __DIR__ . '/constanciaNotificacionService.php';


/*
|--------------------------------------------------------------------------
| NOTIFICACIÓN SERVICE
|--------------------------------------------------------------------------
|
| Servicio centralizado para la gestión de notificaciones.
|
*/


/* ==========================================================
   TIPOS DE NOTIFICACIÓN
========================================================== */

const TIPOS_NOTIFICACION = [

    'NUEVA_ASIGNACION',
    'ASIGNACION_EN_PROCESO',
    'ASIGNACION_COMPLETADA',
    'ASIGNACION_CANCELADA',
    'RECORDATORIO_SEGUIMIENTO',
    'NOTIFICACION_LEIDA'

];


/* ==========================================================
   VALIDAR TIPO
========================================================== */

function validarTipoNotificacion(
    string $tipo
): string {

    $tipo = strtoupper(trim($tipo));

    if (
        !in_array(
            $tipo,
            TIPOS_NOTIFICACION,
            true
        )
    ) {
        throw new Exception(
            'El tipo de notificación no es válido.'
        );
    }

    return $tipo;
}


/* ==========================================================
   VALIDAR USUARIO
========================================================== */

function validarUsuarioNotificacion(
    PDO $pdo,
    int $usuarioId
): void {

    if ($usuarioId <= 0) {
        throw new Exception(
            'Usuario de notificación inválido.'
        );
    }

    $stmt = $pdo->prepare("
        SELECT id
        FROM usuarios
        WHERE id = :id
        AND activo = 1
        LIMIT 1
    ");

    $stmt->execute([
        ':id' => $usuarioId
    ]);

    if (!$stmt->fetchColumn()) {
        throw new Exception(
            'El usuario de la notificación no existe o está inactivo.'
        );
    }
}


/* ==========================================================
   VALIDAR JOVEN
========================================================== */

function validarJovenNotificacion(
    PDO $pdo,
    ?int $jovenId
): ?int {

    if (
        $jovenId === null ||
        $jovenId <= 0
    ) {
        return null;
    }

    $stmt = $pdo->prepare("
        SELECT id
        FROM jovenes
        WHERE id = :id
        LIMIT 1
    ");

    $stmt->execute([
        ':id' => $jovenId
    ]);

    if (!$stmt->fetchColumn()) {
        throw new Exception(
            'El joven asociado a la notificación no existe.'
        );
    }

    return $jovenId;
}


/* ==========================================================
   VALIDAR ASIGNACIÓN
========================================================== */

function validarAsignacionNotificacion(
    PDO $pdo,
    ?int $asignacionId
): ?int {

    if (
        $asignacionId === null ||
        $asignacionId <= 0
    ) {
        return null;
    }

    $stmt = $pdo->prepare("
        SELECT id
        FROM asignaciones_seguimiento
        WHERE id = :id
        LIMIT 1
    ");

    $stmt->execute([
        ':id' => $asignacionId
    ]);

    if (!$stmt->fetchColumn()) {
        throw new Exception(
            'La asignación asociada a la notificación no existe.'
        );
    }

    return $asignacionId;
}


/* ==========================================================
   VALIDAR TÍTULO
========================================================== */

function validarTituloNotificacion(
    string $titulo
): string {

    $titulo = trim($titulo);

    if ($titulo === '') {
        throw new Exception(
            'El título de la notificación es obligatorio.'
        );
    }

    if (mb_strlen($titulo) > 150) {
        throw new Exception(
            'El título de la notificación es demasiado largo.'
        );
    }

    return $titulo;
}


/* ==========================================================
   VALIDAR MENSAJE
========================================================== */

function validarMensajeNotificacion(
    string $mensaje
): string {

    $mensaje = trim($mensaje);

    if ($mensaje === '') {
        throw new Exception(
            'El mensaje de la notificación es obligatorio.'
        );
    }

    if (mb_strlen($mensaje) > 5000) {
        throw new Exception(
            'El mensaje de la notificación es demasiado largo.'
        );
    }

    return $mensaje;
}


/* ==========================================================
   CREAR NOTIFICACIÓN
========================================================== */

function crearNotificacion(
    PDO $pdo,
    array $datos
): int {

    $usuarioId = (int)(
        $datos['usuario_id'] ?? 0
    );

    $tipo = validarTipoNotificacion(
        (string)(
            $datos['tipo'] ?? ''
        )
    );

    $titulo = validarTituloNotificacion(
        (string)(
            $datos['titulo'] ?? ''
        )
    );

    $mensaje = validarMensajeNotificacion(
        (string)(
            $datos['mensaje'] ?? ''
        )
    );


    /* ------------------------------------------------------
       JOVEN
    ------------------------------------------------------ */

    $jovenId = null;

    if (
        isset($datos['joven_id']) &&
        $datos['joven_id'] !== ''
    ) {
        $jovenId = (int)$datos['joven_id'];
    }

    $jovenId = validarJovenNotificacion(
        $pdo,
        $jovenId
    );


    /* ------------------------------------------------------
       ASIGNACIÓN
    ------------------------------------------------------ */

    $asignacionId = null;

    if (
        isset($datos['asignacion_id']) &&
        $datos['asignacion_id'] !== ''
    ) {
        $asignacionId = (int)$datos['asignacion_id'];
    }

    $asignacionId = validarAsignacionNotificacion(
        $pdo,
        $asignacionId
    );


    /* ------------------------------------------------------
       USUARIO
    ------------------------------------------------------ */

    validarUsuarioNotificacion(
        $pdo,
        $usuarioId
    );


    /* ------------------------------------------------------
       INSERTAR
    ------------------------------------------------------ */

    $stmt = $pdo->prepare("
        INSERT INTO notificaciones
        (
            usuario_id,
            tipo,
            titulo,
            mensaje,
            joven_id,
            asignacion_id,
            leida,
            fecha_lectura
        )

        VALUES
        (
            :usuario_id,
            :tipo,
            :titulo,
            :mensaje,
            :joven_id,
            :asignacion_id,
            0,
            NULL
        )
    ");

    $stmt->execute([

        ':usuario_id' => $usuarioId,

        ':tipo' => $tipo,

        ':titulo' => $titulo,

        ':mensaje' => $mensaje,

        ':joven_id' => $jovenId,

        ':asignacion_id' => $asignacionId

    ]);

    return (int)$pdo->lastInsertId();
}


/* ==========================================================
   OBTENER NOTIFICACIÓN POR ID
========================================================== */

function obtenerNotificacionPorId(
    PDO $pdo,
    int $id,
    ?int $usuarioId = null
): ?array {

    if ($id <= 0) {
        return null;
    }

    $sql = "
        SELECT

            n.*,

            j.nombre_completo AS joven_nombre,

            a.estado AS asignacion_estado,

            a.asignado_por,

            u.nombre AS usuario_nombre,

            ap.nombre AS asignado_por_nombre

        FROM notificaciones n

        LEFT JOIN jovenes j
            ON n.joven_id = j.id

        LEFT JOIN asignaciones_seguimiento a
            ON n.asignacion_id = a.id

        LEFT JOIN usuarios u
            ON n.usuario_id = u.id

        LEFT JOIN usuarios ap
            ON a.asignado_por = ap.id

        WHERE n.id = :id
    ";

    $params = [
        ':id' => $id
    ];

    if ($usuarioId !== null) {

        if ($usuarioId <= 0) {
            return null;
        }

        $sql .= "
            AND n.usuario_id = :usuario_id
        ";

        $params[':usuario_id'] = $usuarioId;
    }

    $sql .= "
        LIMIT 1
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute($params);

    return $stmt->fetch(
        PDO::FETCH_ASSOC
    ) ?: null;
}


/* ==========================================================
   OBTENER NOTIFICACIONES DE UN USUARIO
========================================================== */

function obtenerNotificacionesUsuario(
    PDO $pdo,
    int $usuarioId,
    int $limite = 20,
    int $offset = 0
): array {

    validarUsuarioNotificacion(
        $pdo,
        $usuarioId
    );

    $limite = max(
        1,
        min(
            $limite,
            100
        )
    );

    $offset = max(
        0,
        $offset
    );

    $stmt = $pdo->prepare("
        SELECT

            n.*,

            j.nombre_completo AS joven_nombre,

            a.estado AS asignacion_estado,

            a.asignado_por,

            ap.nombre AS asignado_por_nombre

        FROM notificaciones n

        LEFT JOIN jovenes j
            ON n.joven_id = j.id

        LEFT JOIN asignaciones_seguimiento a
            ON n.asignacion_id = a.id

        LEFT JOIN usuarios ap
            ON a.asignado_por = ap.id

        WHERE n.usuario_id = :usuario_id

        ORDER BY

            n.leida ASC,

            n.created_at DESC,

            n.id DESC

        LIMIT {$limite}

        OFFSET {$offset}
    ");

    $stmt->execute([
        ':usuario_id' => $usuarioId
    ]);

    return $stmt->fetchAll(
        PDO::FETCH_ASSOC
    );
}


/* ==========================================================
   OBTENER NOTIFICACIONES NO LEÍDAS
========================================================== */

function obtenerNotificacionesNoLeidas(
    PDO $pdo,
    int $usuarioId,
    int $limite = 20
): array {

    validarUsuarioNotificacion(
        $pdo,
        $usuarioId
    );

    $limite = max(
        1,
        min(
            $limite,
            100
        )
    );

    $stmt = $pdo->prepare("
        SELECT

            n.*,

            j.nombre_completo AS joven_nombre,

            a.estado AS asignacion_estado,

            a.asignado_por,

            ap.nombre AS asignado_por_nombre

        FROM notificaciones n

        LEFT JOIN jovenes j
            ON n.joven_id = j.id

        LEFT JOIN asignaciones_seguimiento a
            ON n.asignacion_id = a.id

        LEFT JOIN usuarios ap
            ON a.asignado_por = ap.id

        WHERE n.usuario_id = :usuario_id

        AND n.leida = 0

        ORDER BY

            n.created_at DESC,

            n.id DESC

        LIMIT {$limite}
    ");

    $stmt->execute([
        ':usuario_id' => $usuarioId
    ]);

    return $stmt->fetchAll(
        PDO::FETCH_ASSOC
    );
}


/* ==========================================================
   CONTAR NO LEÍDAS
========================================================== */

function contarNotificacionesNoLeidas(
    PDO $pdo,
    int $usuarioId
): int {

    if ($usuarioId <= 0) {
        return 0;
    }

    $stmt = $pdo->prepare("
        SELECT COUNT(*)

        FROM notificaciones

        WHERE usuario_id = :usuario_id

        AND leida = 0
    ");

    $stmt->execute([
        ':usuario_id' => $usuarioId
    ]);

    return (int)$stmt->fetchColumn();
}


/* ==========================================================
   REGISTRAR CONSTANCIA DE LECTURA
========================================================== */

function registrarConstanciaLecturaNotificacion(
    PDO $pdo,
    array $notificacion,
    int $usuarioLector
): void {

    if ($usuarioLector <= 0) {
        return;
    }

    $asignacionId = (int)(
        $notificacion['asignacion_id'] ?? 0
    );

    if ($asignacionId <= 0) {
        return;
    }

    $asignadoPor = (int)(
        $notificacion['asignado_por'] ?? 0
    );

    if ($asignadoPor <= 0) {
        return;
    }

    if ($asignadoPor === $usuarioLector) {
        return;
    }

    $tipoOriginal = strtoupper(
        trim(
            (string)(
                $notificacion['tipo'] ?? ''
            )
        )
    );

    if (
        $tipoOriginal === 'NOTIFICACION_LEIDA'
    ) {
        return;
    }

    $nombreLector = 'El usuario';

    $stmt = $pdo->prepare("
        SELECT nombre
        FROM usuarios
        WHERE id = :id
        LIMIT 1
    ");

    $stmt->execute([
        ':id' => $usuarioLector
    ]);

    $nombreLectorDb = $stmt->fetchColumn();

    if (
        $nombreLectorDb !== false &&
        $nombreLectorDb !== null &&
        trim((string)$nombreLectorDb) !== ''
    ) {
        $nombreLector = (string)$nombreLectorDb;
    }

    $jovenNombre = trim(
        (string)(
            $notificacion['joven_nombre'] ?? ''
        )
    );

    if ($jovenNombre === '') {
        $jovenNombre = 'el joven';
    }

    $stmt = $pdo->prepare("
        SELECT id

        FROM notificaciones

        WHERE usuario_id = :usuario_id

        AND asignacion_id = :asignacion_id

        AND tipo = 'NOTIFICACION_LEIDA'

        AND mensaje LIKE :mensaje

        LIMIT 1
    ");

    $mensajeBusqueda =
        '%' .
        $nombreLector .
        '%';

    $stmt->execute([

        ':usuario_id' =>
            $asignadoPor,

        ':asignacion_id' =>
            $asignacionId,

        ':mensaje' =>
            $mensajeBusqueda

    ]);

    if ($stmt->fetchColumn()) {
        return;
    }

    crearNotificacion(
        $pdo,
        [

            'usuario_id' =>
                $asignadoPor,

            'tipo' =>
                'NOTIFICACION_LEIDA',

            'titulo' =>
                'Notificación leída',

            'mensaje' =>
                $nombreLector .
                ' leyó la notificación de asignación de ' .
                $jovenNombre .
                '.',

            'joven_id' =>
                (int)(
                    $notificacion['joven_id'] ?? 0
                ),

            'asignacion_id' =>
                $asignacionId

        ]
    );
}


/* ==========================================================
   MARCAR COMO LEÍDA
========================================================== */

function marcarNotificacionLeida(
    PDO $pdo,
    int $id,
    int $usuarioId
): void {

    if (
        $id <= 0 ||
        $usuarioId <= 0
    ) {
        throw new Exception(
            'Notificación inválida.'
        );
    }

    $notificacion = obtenerNotificacionPorId(
        $pdo,
        $id,
        $usuarioId
    );

    if (!$notificacion) {
        throw new Exception(
            'La notificación no existe o no pertenece al usuario actual.'
        );
    }

    if (
        (int)(
            $notificacion['leida'] ?? 0
        ) === 1
    ) {
        return;
    }

    $transaccionPropia =
        !$pdo->inTransaction();

    if ($transaccionPropia) {
        $pdo->beginTransaction();
    }

    try {

        $stmt = $pdo->prepare("
            UPDATE notificaciones

            SET

                leida = 1,

                fecha_lectura = NOW()

            WHERE id = :id

            AND usuario_id = :usuario_id

            AND leida = 0

            LIMIT 1
        ");

        $stmt->execute([

            ':id' =>
                $id,

            ':usuario_id' =>
                $usuarioId

        ]);

        if (
            $stmt->rowCount() > 0
        ) {

            registrarConstanciaLecturaNotificacion(
                $pdo,
                $notificacion,
                $usuarioId
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
   MARCAR COMO NO LEÍDA
========================================================== */

function marcarNotificacionNoLeida(
    PDO $pdo,
    int $id,
    int $usuarioId
): void {

    if (
        $id <= 0 ||
        $usuarioId <= 0
    ) {
        throw new Exception(
            'Notificación inválida.'
        );
    }

    $notificacion = obtenerNotificacionPorId(
        $pdo,
        $id,
        $usuarioId
    );

    if (!$notificacion) {
        throw new Exception(
            'La notificación no existe o no pertenece al usuario actual.'
        );
    }

    if (
        (int)(
            $notificacion['leida'] ?? 0
        ) === 0
    ) {
        return;
    }

    $stmt = $pdo->prepare("
        UPDATE notificaciones

        SET

            leida = 0,

            fecha_lectura = NULL

        WHERE id = :id

        AND usuario_id = :usuario_id

        AND leida = 1

        LIMIT 1
    ");

    $stmt->execute([

        ':id' =>
            $id,

        ':usuario_id' =>
            $usuarioId

    ]);
}


/* ==========================================================
   MARCAR TODAS COMO LEÍDAS
========================================================== */

function marcarTodasNotificacionesLeidas(
    PDO $pdo,
    int $usuarioId
): void {

    if ($usuarioId <= 0) {
        throw new Exception(
            'Usuario inválido.'
        );
    }

    $pendientes =
        obtenerNotificacionesNoLeidas(
            $pdo,
            $usuarioId,
            100
        );

    $transaccionPropia =
        !$pdo->inTransaction();

    if ($transaccionPropia) {
        $pdo->beginTransaction();
    }

    try {

        $stmt = $pdo->prepare("
            UPDATE notificaciones

            SET

                leida = 1,

                fecha_lectura = NOW()

            WHERE usuario_id = :usuario_id

            AND leida = 0
        ");

        $stmt->execute([
            ':usuario_id' => $usuarioId
        ]);

        foreach ($pendientes as $notificacion) {

            registrarConstanciaLecturaNotificacion(
                $pdo,
                $notificacion,
                $usuarioId
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
   MARCAR TODAS COMO NO LEÍDAS
========================================================== */

function marcarTodasNotificacionesNoLeidas(
    PDO $pdo,
    int $usuarioId
): int {

    if ($usuarioId <= 0) {
        throw new Exception(
            'Usuario inválido.'
        );
    }

    $stmt = $pdo->prepare("
        UPDATE notificaciones

        SET

            leida = 0,

            fecha_lectura = NULL

        WHERE usuario_id = :usuario_id

        AND leida = 1
    ");

    $stmt->execute([
        ':usuario_id' => $usuarioId
    ]);

    return $stmt->rowCount();
}


/* ==========================================================
   ELIMINAR NOTIFICACIÓN
========================================================== */

function eliminarNotificacion(
    PDO $pdo,
    int $id,
    int $usuarioId
): void {

    if (
        $id <= 0 ||
        $usuarioId <= 0
    ) {
        throw new Exception(
            'Notificación inválida.'
        );
    }

    $stmt = $pdo->prepare("
        DELETE FROM notificaciones

        WHERE id = :id

        AND usuario_id = :usuario_id

        LIMIT 1
    ");

    $stmt->execute([

        ':id' =>
            $id,

        ':usuario_id' =>
            $usuarioId

    ]);

    if (
        $stmt->rowCount() === 0
    ) {
        throw new Exception(
            'La notificación no existe o no pertenece al usuario actual.'
        );
    }
}


/* ==========================================================
   ELIMINAR TODAS LAS NOTIFICACIONES LEÍDAS
========================================================== */

function eliminarNotificacionesLeidas(
    PDO $pdo,
    int $usuarioId
): int {

    if ($usuarioId <= 0) {
        throw new Exception(
            'Usuario inválido.'
        );
    }

    $stmt = $pdo->prepare("
        DELETE FROM notificaciones

        WHERE usuario_id = :usuario_id

        AND leida = 1
    ");

    $stmt->execute([
        ':usuario_id' => $usuarioId
    ]);

    return $stmt->rowCount();
}


/* ==========================================================
   ELIMINAR NOTIFICACIONES LEÍDAS ANTIGUAS
========================================================== */

function eliminarNotificacionesLeidasAntesDe(
    PDO $pdo,
    int $usuarioId,
    int $dias = 30
): int {

    if ($usuarioId <= 0) {
        throw new Exception(
            'Usuario inválido.'
        );
    }

    $dias = max(
        1,
        min(
            $dias,
            3650
        )
    );

    $stmt = $pdo->prepare("
        DELETE FROM notificaciones

        WHERE usuario_id = :usuario_id

        AND leida = 1

        AND created_at < DATE_SUB(
            NOW(),
            INTERVAL {$dias} DAY
        )
    ");

    $stmt->execute([
        ':usuario_id' => $usuarioId
    ]);

    return $stmt->rowCount();
}


/* ==========================================================
   ELIMINAR CANCELACIONES ANTIGUAS
========================================================== */

function eliminarNotificacionesCanceladas(
    PDO $pdo,
    int $usuarioId
): int {

    if ($usuarioId <= 0) {
        throw new Exception(
            'Usuario inválido.'
        );
    }

    $stmt = $pdo->prepare("
        DELETE FROM notificaciones

        WHERE usuario_id = :usuario_id

        AND tipo = 'ASIGNACION_CANCELADA'
    ");

    $stmt->execute([
        ':usuario_id' => $usuarioId
    ]);

    return $stmt->rowCount();
}


/* ==========================================================
   OBTENER USUARIO ACTUAL
========================================================== */

function obtenerUsuarioActualNotificaciones(): int
{
    $usuarioId = usuarioId();

    if (
        $usuarioId === null ||
        $usuarioId <= 0
    ) {
        throw new Exception(
            'No se pudo identificar al usuario actual.'
        );
    }

    return (int)$usuarioId;
}