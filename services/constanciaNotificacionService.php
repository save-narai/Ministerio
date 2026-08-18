<?php

declare(strict_types=1);

require_once __DIR__ . '/sessionService.php';


/*
|--------------------------------------------------------------------------
| Constancia Notificación Service
|--------------------------------------------------------------------------
|
| Gestiona exclusivamente las constancias de lectura.
|
| Una constancia registra:
|
| • Qué notificación fue leída.
| • Qué asignación estaba relacionada.
| • Qué joven estaba relacionado.
| • Quién leyó la notificación.
| • Quién realizó la asignación.
| • Cuándo fue leída.
|
| Este servicio:
|
| • NO marca notificaciones como leídas.
| • NO elimina notificaciones.
| • NO realiza redirecciones.
| • Registra y consulta constancias.
|
|--------------------------------------------------------------------------
*/


/* ==========================================================
   VALIDAR USUARIO
========================================================== */

function validarUsuarioConstancia(
    PDO $pdo,
    int $usuarioId
): void {

    if ($usuarioId <= 0) {

        throw new Exception(
            'Usuario inválido para la constancia.'
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

        ':id' =>
            $usuarioId

    ]);


    if (!$stmt->fetchColumn()) {

        throw new Exception(
            'El usuario de la constancia no existe o está inactivo.'
        );
    }
}


/* ==========================================================
   VALIDAR NOTIFICACIÓN
========================================================== */

function validarNotificacionConstancia(
    PDO $pdo,
    int $notificacionId
): array {

    if ($notificacionId <= 0) {

        throw new Exception(
            'Notificación inválida.'
        );
    }


    $stmt = $pdo->prepare("
        SELECT

            n.*,

            j.nombre_completo AS joven_nombre

        FROM notificaciones n

        LEFT JOIN jovenes j
            ON n.joven_id = j.id

        WHERE n.id = :id

        LIMIT 1
    ");


    $stmt->execute([

        ':id' =>
            $notificacionId

    ]);


    $notificacion =
        $stmt->fetch(
            PDO::FETCH_ASSOC
        );


    if (!$notificacion) {

        throw new Exception(
            'La notificación no existe.'
        );
    }


    return $notificacion;
}


/* ==========================================================
   EXISTE CONSTANCIA
========================================================== */

function existeConstanciaNotificacion(
    PDO $pdo,
    int $notificacionId,
    int $usuarioLector
): bool {

    if (
        $notificacionId <= 0 ||
        $usuarioLector <= 0
    ) {

        return false;
    }


    $stmt = $pdo->prepare("
        SELECT id

        FROM constancias_notificaciones

        WHERE notificacion_id = :notificacion_id

        AND usuario_lector = :usuario_lector

        LIMIT 1
    ");


    $stmt->execute([

        ':notificacion_id' =>
            $notificacionId,

        ':usuario_lector' =>
            $usuarioLector

    ]);


    return (bool)$stmt->fetchColumn();
}


/* ==========================================================
   REGISTRAR CONSTANCIA
========================================================== */

function registrarConstanciaNotificacion(
    PDO $pdo,
    int $notificacionId,
    int $usuarioLector
): ?int {

    validarUsuarioConstancia(
        $pdo,
        $usuarioLector
    );


    /* ======================================================
       OBTENER NOTIFICACIÓN
    ====================================================== */

    $notificacion =
        validarNotificacionConstancia(
            $pdo,
            $notificacionId
        );


    /* ======================================================
       OBTENER ASIGNACIÓN
    ====================================================== */

    $asignacionId =
        (int)(
            $notificacion['asignacion_id']
            ?? 0
        );


    /*
     * Una constancia solamente aplica
     * a una notificación relacionada
     * con una asignación.
     */

    if (
        $asignacionId <= 0
    ) {

        return null;
    }


    /* ======================================================
       OBTENER ASIGNADOR
    ====================================================== */

    $asignadoPor =
        (int)(
            $notificacion['asignado_por']
            ?? 0
        );


    /*
     * Si la notificación no trae asignado_por,
     * lo buscamos directamente en la asignación.
     */

    if (
        $asignadoPor <= 0
    ) {

        $stmt =
            $pdo->prepare("
                SELECT asignado_por

                FROM asignaciones_seguimiento

                WHERE id = :id

                LIMIT 1
            ");


        $stmt->execute([

            ':id' =>
                $asignacionId

        ]);


        $asignadoPor =
            (int)(
                $stmt->fetchColumn()
                ?: 0
            );
    }


    /*
     * Sin asignador no podemos registrar
     * la constancia correctamente.
     */

    if (
        $asignadoPor <= 0
    ) {

        return null;
    }


    /* ======================================================
       EVITAR CONSTANCIA PARA UNO MISMO
    ====================================================== */

    if (
        $asignadoPor === $usuarioLector
    ) {

        return null;
    }


    /* ======================================================
       EVITAR DUPLICADOS
    ====================================================== */

    if (
        existeConstanciaNotificacion(
            $pdo,
            $notificacionId,
            $usuarioLector
        )
    ) {

        return null;
    }


    /* ======================================================
       JOVEN
    ====================================================== */

    $jovenId =
        isset(
            $notificacion['joven_id']
        )
            ? (int)$notificacion['joven_id']
            : 0;


    if (
        $jovenId <= 0
    ) {

        $jovenId = null;
    }


    /* ======================================================
       INSERTAR CONSTANCIA
    ====================================================== */

    $stmt =
        $pdo->prepare("
            INSERT INTO constancias_notificaciones
            (
                notificacion_id,
                asignacion_id,
                joven_id,
                usuario_lector,
                usuario_asignador,
                fecha_lectura
            )
            VALUES
            (
                :notificacion_id,
                :asignacion_id,
                :joven_id,
                :usuario_lector,
                :usuario_asignador,
                NOW()
            )
        ");


    $stmt->execute([

        ':notificacion_id' =>
            $notificacionId,

        ':asignacion_id' =>
            $asignacionId,

        ':joven_id' =>
            $jovenId,

        ':usuario_lector' =>
            $usuarioLector,

        ':usuario_asignador' =>
            $asignadoPor

    ]);


    $constanciaId =
        (int)$pdo->lastInsertId();


    /* ======================================================
       RECUPERAR CONSTANCIA COMPLETA
    ====================================================== */

    $stmt =
        $pdo->prepare("
            SELECT

                c.id,

                c.notificacion_id,

                c.asignacion_id,

                c.joven_id,

                c.usuario_lector,

                c.usuario_asignador,

                c.fecha_lectura,

                j.nombre_completo AS joven_nombre,

                lector.nombre AS lector_nombre,

                asignador.nombre AS asignador_nombre

            FROM constancias_notificaciones c

            LEFT JOIN jovenes j
                ON c.joven_id = j.id

            INNER JOIN usuarios lector
                ON c.usuario_lector = lector.id

            INNER JOIN usuarios asignador
                ON c.usuario_asignador = asignador.id

            WHERE c.id = :id

            LIMIT 1
        ");


    $stmt->execute([

        ':id' =>
            $constanciaId

    ]);


    $constancia =
        $stmt->fetch(
            PDO::FETCH_ASSOC
        );


    if (
        !$constancia
    ) {

        throw new Exception(
            'No se pudo recuperar la constancia de lectura.'
        );
    }


    /* ======================================================
       NOTIFICAR AL ASIGNADOR
    ====================================================== */

    notificarConstanciaLectura(
        $pdo,
        $constancia
    );


    return $constanciaId;
}


/* ==========================================================
   OBTENER CONSTANCIA POR NOTIFICACIÓN
========================================================== */

function obtenerConstanciaPorNotificacion(
    PDO $pdo,
    int $notificacionId
): ?array {

    if ($notificacionId <= 0) {

        return null;
    }


    $stmt = $pdo->prepare("
        SELECT

            c.*,

            n.titulo AS notificacion_titulo,

            n.mensaje AS notificacion_mensaje,

            j.nombre_completo AS joven_nombre,

            lector.nombre AS usuario_lector_nombre,

            lector.usuario AS usuario_lector_login,

            asignador.nombre AS usuario_asignador_nombre,

            asignador.usuario AS usuario_asignador_login

        FROM constancias_notificaciones c

        INNER JOIN notificaciones n
            ON c.notificacion_id = n.id

        LEFT JOIN jovenes j
            ON c.joven_id = j.id

        INNER JOIN usuarios lector
            ON c.usuario_lector = lector.id

        INNER JOIN usuarios asignador
            ON c.usuario_asignador = asignador.id

        WHERE c.notificacion_id = :notificacion_id

        ORDER BY

            c.fecha_lectura DESC

        LIMIT 1
    ");


    $stmt->execute([

        ':notificacion_id' =>
            $notificacionId

    ]);


    return $stmt->fetch(
        PDO::FETCH_ASSOC
    ) ?: null;
}


/* ==========================================================
   OBTENER CONSTANCIAS DEL ASIGNADOR
========================================================== */

function obtenerConstanciasPorAsignador(
    PDO $pdo,
    int $usuarioAsignador,
    int $limite = 100,
    int $offset = 0
): array {

    validarUsuarioConstancia(
        $pdo,
        $usuarioAsignador
    );


    $limite =
        max(
            1,
            min(
                $limite,
                500
            )
        );


    $offset =
        max(
            0,
            $offset
        );


    $stmt =
        $pdo->prepare("
            SELECT

                c.id,

                c.notificacion_id,

                c.asignacion_id,

                c.joven_id,

                c.usuario_lector,

                c.usuario_asignador,

                c.fecha_lectura,

                n.titulo AS notificacion_titulo,

                n.mensaje AS notificacion_mensaje,

                n.tipo AS notificacion_tipo,

                j.nombre_completo AS joven_nombre,

                lector.nombre AS lector_nombre,

                lector.usuario AS lector_usuario,

                asignador.nombre AS asignador_nombre,

                asignador.usuario AS asignador_usuario,

                a.anio AS asignacion_anio,

                a.mes AS asignacion_mes,

                a.estado AS asignacion_estado

            FROM constancias_notificaciones c

            INNER JOIN notificaciones n
                ON c.notificacion_id = n.id

            LEFT JOIN jovenes j
                ON c.joven_id = j.id

            INNER JOIN usuarios lector
                ON c.usuario_lector = lector.id

            INNER JOIN usuarios asignador
                ON c.usuario_asignador = asignador.id

            LEFT JOIN asignaciones_seguimiento a
                ON c.asignacion_id = a.id

            WHERE c.usuario_asignador = :usuario_asignador

            ORDER BY

                c.fecha_lectura DESC,

                c.id DESC

            LIMIT {$limite}

            OFFSET {$offset}
        ");


    $stmt->execute([

        ':usuario_asignador' =>
            $usuarioAsignador

    ]);


    return $stmt->fetchAll(
        PDO::FETCH_ASSOC
    );
}


/* ==========================================================
   OBTENER TODAS LAS CONSTANCIAS
   PARA ADMINISTRACIÓN
========================================================== */

function obtenerTodasLasConstanciasNotificacion(
    PDO $pdo,
    int $limite = 200,
    int $offset = 0
): array {

    $limite =
        max(
            1,
            min(
                $limite,
                500
            )
        );


    $offset =
        max(
            0,
            $offset
        );


    $stmt =
        $pdo->prepare("
            SELECT

                c.id,

                c.notificacion_id,

                c.asignacion_id,

                c.joven_id,

                c.usuario_lector,

                c.usuario_asignador,

                c.fecha_lectura,

                n.titulo AS notificacion_titulo,

                n.mensaje AS notificacion_mensaje,

                n.tipo AS notificacion_tipo,

                j.nombre_completo AS joven_nombre,

                lector.nombre AS lector_nombre,

                lector.usuario AS lector_usuario,

                asignador.nombre AS asignador_nombre,

                asignador.usuario AS asignador_usuario,

                a.anio AS asignacion_anio,

                a.mes AS asignacion_mes,

                a.estado AS asignacion_estado

            FROM constancias_notificaciones c

            INNER JOIN notificaciones n
                ON c.notificacion_id = n.id

            LEFT JOIN jovenes j
                ON c.joven_id = j.id

            INNER JOIN usuarios lector
                ON c.usuario_lector = lector.id

            INNER JOIN usuarios asignador
                ON c.usuario_asignador = asignador.id

            LEFT JOIN asignaciones_seguimiento a
                ON c.asignacion_id = a.id

            ORDER BY

                c.fecha_lectura DESC,

                c.id DESC

            LIMIT {$limite}

            OFFSET {$offset}
        ");


    $stmt->execute();


    return $stmt->fetchAll(
        PDO::FETCH_ASSOC
    );
}


/* ==========================================================
   CONTAR CONSTANCIAS DEL ASIGNADOR
========================================================== */

function contarConstanciasPorAsignador(
    PDO $pdo,
    int $usuarioAsignador
): int {

    validarUsuarioConstancia(
        $pdo,
        $usuarioAsignador
    );


    $stmt =
        $pdo->prepare("
            SELECT COUNT(*)

            FROM constancias_notificaciones

            WHERE usuario_asignador = :usuario_asignador
        ");


    $stmt->execute([

        ':usuario_asignador' =>
            $usuarioAsignador

    ]);


    return (int)$stmt->fetchColumn();
}


/* ==========================================================
   NOTIFICAR CONSTANCIA DE LECTURA AL ASIGNADOR
========================================================== */

function notificarConstanciaLectura(
    PDO $pdo,
    array $constancia
): ?int {

    $usuarioAsignador =
        (int)(
            $constancia['usuario_asignador']
            ?? 0
        );


    $usuarioLector =
        (int)(
            $constancia['usuario_lector']
            ?? 0
        );


    $notificacionId =
        (int)(
            $constancia['notificacion_id']
            ?? 0
        );


    $asignacionId =
        (int)(
            $constancia['asignacion_id']
            ?? 0
        );


    $jovenId =
        !empty(
            $constancia['joven_id']
        )
            ? (int)$constancia['joven_id']
            : null;


    if (
        $usuarioAsignador <= 0 ||
        $usuarioLector <= 0 ||
        $notificacionId <= 0 ||
        $asignacionId <= 0
    ) {

        return null;
    }


    /*
     * No notificamos al mismo usuario
     * que hizo la lectura.
     */

    if (
        $usuarioAsignador === $usuarioLector
    ) {

        return null;
    }


    /* ======================================================
       EVITAR DUPLICADOS
    ====================================================== */

    $stmt =
        $pdo->prepare("
            SELECT id

            FROM notificaciones

            WHERE usuario_id = :usuario_id

            AND tipo = 'NOTIFICACION_LEIDA'

            AND asignacion_id = :asignacion_id

            AND mensaje LIKE :mensaje

            LIMIT 1
        ");


    $lectorNombre =
        (string)(
            $constancia['lector_nombre']
            ?? 'El usuario'
        );


    $mensajeBusqueda =
        '%' .
        $lectorNombre .
        '%';


    $stmt->execute([

        ':usuario_id' =>
            $usuarioAsignador,

        ':asignacion_id' =>
            $asignacionId,

        ':mensaje' =>
            $mensajeBusqueda

    ]);


    if (
        $stmt->fetchColumn()
    ) {

        return null;
    }


    $jovenNombre =
        (string)(
            $constancia['joven_nombre']
            ?? 'el joven'
        );


    return crearNotificacion(
        $pdo,
        [

            'usuario_id' =>
                $usuarioAsignador,

            'tipo' =>
                'NOTIFICACION_LEIDA',

            'titulo' =>
                'Asignación leída',

            'mensaje' =>
                $lectorNombre
                . ' leyó la notificación de asignación de '
                . $jovenNombre
                . '.',


            'joven_id' =>
                $jovenId,

            'asignacion_id' =>
                $asignacionId

        ]
    );
}