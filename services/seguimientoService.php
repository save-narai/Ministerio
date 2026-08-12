<?php

declare(strict_types=1);

require_once __DIR__ . '/../middleware/permiso.php';
require_once __DIR__ . '/jovenService.php';

/*
|--------------------------------------------------------------------------
| Seguimiento Service
|--------------------------------------------------------------------------
|
| Gestiona:
|
| 1. Seguimientos reales.
| 2. Historial unificado de seguimientos + excepciones.
| 3. Resumen mensual.
| 4. Jóvenes pendientes de seguimiento.
|
| IMPORTANTE:
|
| Las excepciones de seguimiento tienen su propio service:
|
|   excepcionSeguimientoService.php
|
| Este archivo puede CONSULTAR la tabla
| excepciones_seguimiento para construir:
|
|   - historial
|   - resumen
|   - jóvenes sin seguimiento
|
| Pero NO declara funciones PHP CRUD de excepciones.
|
|--------------------------------------------------------------------------
*/


/* ==========================================================
   CONSTANTES
========================================================== */

const MODALIDADES_SEGUIMIENTO = [
    'WHATSAPP',
    'LLAMADA',
    'VISITA',
    'MENSAJE'
];

const ESTADOS_SEGUIMIENTO = [
    'PENDIENTE',
    'EN_PROCESO',
    'FINALIZADO'
];


/* ==========================================================
   VALIDAR FECHA DE CONTACTO
========================================================== */

function validarFechaSeguimiento(
    ?string $fecha
): string
{
    $fecha = trim((string)$fecha);

    if ($fecha === '') {

        throw new Exception(
            'Debe ingresar la fecha de contacto.'
        );
    }

    $timestamp = strtotime($fecha);

    if ($timestamp === false) {

        throw new Exception(
            'La fecha de contacto no es válida.'
        );
    }

    $fechaNormalizada = date(
        'Y-m-d',
        $timestamp
    );

    $hoy = date('Y-m-d');

    if ($fechaNormalizada > $hoy) {

        throw new Exception(
            'La fecha no puede ser futura.'
        );
    }

    return $fechaNormalizada;
}


/* ==========================================================
   VALIDAR MODALIDAD
========================================================== */

function validarModalidadSeguimiento(
    string $modalidad
): string
{
    $modalidad = strtoupper(
        trim($modalidad)
    );

    if (!in_array(
        $modalidad,
        MODALIDADES_SEGUIMIENTO,
        true
    )) {

        throw new Exception(
            'Modalidad de contacto inválida.'
        );
    }

    return $modalidad;
}


/* ==========================================================
   VALIDAR ESTADO
========================================================== */

function validarEstadoSeguimiento(
    string $estado
): string
{
    $estado = strtoupper(
        trim($estado)
    );

    if (!in_array(
        $estado,
        ESTADOS_SEGUIMIENTO,
        true
    )) {

        throw new Exception(
            'Estado del proceso inválido.'
        );
    }

    return $estado;
}


/* ==========================================================
   VALIDAR RESPONSABLE
========================================================== */

function validarResponsableSeguimiento(
    PDO $pdo,
    ?int $responsableId
): ?int
{
    if (
        $responsableId === null ||
        $responsableId <= 0
    ) {

        return null;
    }

    $stmt = $pdo->prepare("
        SELECT id
        FROM usuarios
        WHERE id = :id
        LIMIT 1
    ");

    $stmt->execute([
        ':id' => $responsableId
    ]);

    if (!$stmt->fetchColumn()) {

        throw new Exception(
            'El responsable seleccionado no existe.'
        );
    }

    return $responsableId;
}


/* ==========================================================
   VALIDAR OBSERVACIONES
========================================================== */

function validarObservacionesSeguimiento(
    ?string $observaciones
): ?string
{
    $observaciones = trim(
        (string)$observaciones
    );

    if ($observaciones === '') {

        return null;
    }

    if (mb_strlen($observaciones) > 2000) {

        throw new Exception(
            'Las observaciones son demasiado largas.'
        );
    }

    return $observaciones;
}


/* ==========================================================
   VALIDAR JOVEN PARA SEGUIMIENTO
========================================================== */

function validarJovenSeguimiento(
    PDO $pdo,
    int $jovenId
): array
{
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

    return $joven;
}


/* ==========================================================
   PREPARAR DATOS DEL SEGUIMIENTO
========================================================== */

function prepararDatosSeguimiento(
    PDO $pdo,
    array $datos
): array
{
    $jovenId = (int)(
        $datos['joven_id'] ?? 0
    );

    $joven = validarJovenSeguimiento(
        $pdo,
        $jovenId
    );

    $fechaContacto =
        validarFechaSeguimiento(
            $datos['fecha_contacto'] ?? null
        );

    $modalidad =
        validarModalidadSeguimiento(
            $datos['modalidad_contacto'] ?? ''
        );

    $estado =
        validarEstadoSeguimiento(
            $datos['estado_proceso'] ?? ''
        );

    $responsableId = null;

    if (
        isset($datos['responsable_id']) &&
        $datos['responsable_id'] !== ''
    ) {

        $responsableId = (int)(
            $datos['responsable_id']
        );
    }

    $responsableId =
        validarResponsableSeguimiento(
            $pdo,
            $responsableId
        );

    $observaciones =
        validarObservacionesSeguimiento(
            $datos['observaciones'] ?? null
        );

    return [

        'jovenId' =>
            $jovenId,

        'joven' =>
            $joven,

        'fechaContacto' =>
            $fechaContacto,

        'modalidad' =>
            $modalidad,

        'estado' =>
            $estado,

        'responsableId' =>
            $responsableId,

        'observaciones' =>
            $observaciones
    ];
}


/* ==========================================================
   CREAR SEGUIMIENTO
========================================================== */

function crearSeguimiento(
    PDO $pdo,
    array $datos
): int
{
    exigirPermiso(
        'gestionar_seguimientos'
    );

    $datos =
        prepararDatosSeguimiento(
            $pdo,
            $datos
        );

    $pdo->beginTransaction();

    try {

        $stmt = $pdo->prepare("
            INSERT INTO seguimientos
            (
                joven_id,
                fecha_contacto,
                modalidad_contacto,
                estado_proceso,
                responsable_id,
                observaciones
            )
            VALUES
            (
                :joven_id,
                :fecha_contacto,
                :modalidad,
                :estado,
                :responsable_id,
                :observaciones
            )
        ");

        $stmt->execute([

            ':joven_id' =>
                $datos['jovenId'],

            ':fecha_contacto' =>
                $datos['fechaContacto'],

            ':modalidad' =>
                $datos['modalidad'],

            ':estado' =>
                $datos['estado'],

            ':responsable_id' =>
                $datos['responsableId'],

            ':observaciones' =>
                $datos['observaciones']
        ]);


        /*
        |--------------------------------------------------------------------------
        | ACTUALIZAR ACTIVIDAD DEL JOVEN
        |--------------------------------------------------------------------------
        */

        $stmt = $pdo->prepare("
            UPDATE jovenes
            SET
                ultima_actividad = NOW(),
                estado_actividad = 'ACTIVO'
            WHERE id = :id
        ");

        $stmt->execute([
            ':id' =>
                $datos['jovenId']
        ]);


        $seguimientoId =
            (int)$pdo->lastInsertId();

        $pdo->commit();

        return $seguimientoId;

    } catch (Throwable $e) {

        if ($pdo->inTransaction()) {

            $pdo->rollBack();
        }

        throw $e;
    }
}


/* ==========================================================
   OBTENER HISTORIAL UNIFICADO DEL MES
========================================================== */

function obtenerHistorialSeguimientosMes(
    PDO $pdo,
    ?int $anio = null,
    ?int $mes = null
): array
{
    $anio ??= (int)date('Y');
    $mes ??= (int)date('m');

    if (
        $anio <= 0 ||
        $mes < 1 ||
        $mes > 12
    ) {

        return [];
    }

    $stmt = $pdo->prepare("
        SELECT

            s.id AS registro_id,

            s.id AS seguimiento_id,

            NULL AS excepcion_id,

            s.joven_id,

            j.nombre_completo,

            j.telefono,

            j.genero,

            'SEGUIMIENTO' AS tipo_registro,

            s.modalidad_contacto,

            s.estado_proceso,

            NULL AS excepcion_motivo,

            s.observaciones,

            s.fecha_contacto AS fecha_registro,

            s.fecha_contacto,

            u.nombre AS responsable_nombre,

            s.responsable_id,

            NULL AS excepcion_anio,

            NULL AS excepcion_mes,

            NULL AS excepcion_created_at

        FROM seguimientos s

        INNER JOIN jovenes j
            ON s.joven_id = j.id

        LEFT JOIN usuarios u
            ON s.responsable_id = u.id

        WHERE YEAR(s.fecha_contacto) = :anio_1

        AND MONTH(s.fecha_contacto) = :mes_1


        UNION ALL


        SELECT

            e.id AS registro_id,

            NULL AS seguimiento_id,

            e.id AS excepcion_id,

            e.joven_id,

            j.nombre_completo,

            j.telefono,

            j.genero,

            'EXCEPCION' AS tipo_registro,

            NULL AS modalidad_contacto,

            NULL AS estado_proceso,

            e.motivo AS excepcion_motivo,

            e.observaciones,

            e.created_at AS fecha_registro,

            NULL AS fecha_contacto,

            u.nombre AS responsable_nombre,

            e.responsable_id,

            e.anio AS excepcion_anio,

            e.mes AS excepcion_mes,

            e.created_at AS excepcion_created_at

        FROM excepciones_seguimiento e

        INNER JOIN jovenes j
            ON e.joven_id = j.id

        LEFT JOIN usuarios u
            ON e.responsable_id = u.id

        WHERE e.anio = :anio_2

        AND e.mes = :mes_2

        ORDER BY

            fecha_registro DESC,

            nombre_completo ASC
    ");

    $stmt->execute([

        ':anio_1' =>
            $anio,

        ':mes_1' =>
            $mes,

        ':anio_2' =>
            $anio,

        ':mes_2' =>
            $mes
    ]);

    return $stmt->fetchAll(
        PDO::FETCH_ASSOC
    );
}


/* ==========================================================
   RESUMEN GENERAL DEL MES
========================================================== */

function obtenerResumenSeguimientosMes(
    ?PDO $pdoParam = null
): array
{
    global $pdo;

    $pdo = $pdoParam ?? $pdo;

    $mesNumero = date('m');

    $anio = date('Y');


    $meses = [

        '01' => 'Enero',
        '02' => 'Febrero',
        '03' => 'Marzo',
        '04' => 'Abril',
        '05' => 'Mayo',
        '06' => 'Junio',
        '07' => 'Julio',
        '08' => 'Agosto',
        '09' => 'Septiembre',
        '10' => 'Octubre',
        '11' => 'Noviembre',
        '12' => 'Diciembre'
    ];


    $mesTexto =
        $meses[$mesNumero]
        . ' '
        . $anio;


    /*
    |--------------------------------------------------------------------------
    | JÓVENES ACTIVOS QUE REQUIEREN SEGUIMIENTO
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->query("
        SELECT COUNT(*)

        FROM jovenes j

        WHERE j.estado_actividad = 'ACTIVO'

        AND j.estado_espiritual = 'NUEVO'

        AND NOT EXISTS (

            SELECT 1

            FROM excepciones_seguimiento e

            WHERE e.joven_id = j.id

            AND e.anio = YEAR(CURDATE())

            AND e.mes = MONTH(CURDATE())
        )
    ");

    $totalActivos =
        (int)$stmt->fetchColumn();


    /*
    |--------------------------------------------------------------------------
    | JÓVENES CON SEGUIMIENTO REAL
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->query("
        SELECT COUNT(DISTINCT s.joven_id)

        FROM seguimientos s

        INNER JOIN jovenes j
            ON s.joven_id = j.id

        WHERE YEAR(s.fecha_contacto)
            = YEAR(CURDATE())

        AND MONTH(s.fecha_contacto)
            = MONTH(CURDATE())

        AND j.estado_actividad = 'ACTIVO'

        AND j.estado_espiritual = 'NUEVO'

        AND NOT EXISTS (

            SELECT 1

            FROM excepciones_seguimiento e

            WHERE e.joven_id = s.joven_id

            AND e.anio = YEAR(CURDATE())

            AND e.mes = MONTH(CURDATE())
        )
    ");

    $totalConSeguimiento =
        (int)$stmt->fetchColumn();


    /*
    |--------------------------------------------------------------------------
    | TOTAL DE EXCEPCIONES
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->query("
        SELECT COUNT(*)

        FROM excepciones_seguimiento e

        INNER JOIN jovenes j
            ON e.joven_id = j.id

        WHERE e.anio = YEAR(CURDATE())

        AND e.mes = MONTH(CURDATE())

        AND j.estado_actividad = 'ACTIVO'

        AND j.estado_espiritual = 'NUEVO'
    ");

    $totalExcepciones =
        (int)$stmt->fetchColumn();


    /*
    |--------------------------------------------------------------------------
    | SIN SEGUIMIENTO
    |--------------------------------------------------------------------------
    */

    $totalSinSeguimiento =
        max(
            0,
            $totalActivos
            - $totalConSeguimiento
            - $totalExcepciones
        );


    /*
    |--------------------------------------------------------------------------
    | PORCENTAJE
    |--------------------------------------------------------------------------
    |
    | Las excepciones justificadas no se consideran pendientes.
    |
    */

    $totalAtendidos =
        $totalConSeguimiento
        + $totalExcepciones;


    $porcentaje =
        $totalActivos > 0
            ? round(
                (
                    $totalAtendidos
                    / $totalActivos
                ) * 100
            )
            : 0;


    /*
    |--------------------------------------------------------------------------
    | SEMÁFORO
    |--------------------------------------------------------------------------
    */

    $color = 'danger';

    if ($porcentaje >= 90) {

        $color = 'ok';

    } elseif ($porcentaje >= 70) {

        $color = 'warning';
    }


    /*
    |--------------------------------------------------------------------------
    | HISTORIAL DEL MES
    |--------------------------------------------------------------------------
    */

    $historialMes =
        obtenerHistorialSeguimientosMes(
            $pdo,
            (int)$anio,
            (int)$mesNumero
        );


    return [

        'mesTexto' =>
            $mesTexto,

        'totalActivos' =>
            $totalActivos,

        'seguimientosMes' =>
            $historialMes,

        'historialMes' =>
            $historialMes,

        'totalConSeguimiento' =>
            $totalConSeguimiento,

        'totalExcepciones' =>
            $totalExcepciones,

        'totalSinSeguimiento' =>
            $totalSinSeguimiento,

        'porcentaje' =>
            $porcentaje,

        'color' =>
            $color
    ];
}


/* ==========================================================
   OBTENER SEGUIMIENTOS REALES POR JOVEN
========================================================== */

/*
| IMPORTANTE:
|
| Esta función devuelve solamente seguimientos reales.
|
| Para obtener:
|
|   - seguimientos
|   - excepciones
|
| debe utilizarse obtenerHistorialJoven().
*/

function obtenerSeguimientosPorJoven(
    PDO $pdo,
    int $jovenId
): array
{
    if ($jovenId <= 0) {

        return [];
    }

    $stmt = $pdo->prepare("
        SELECT

            s.*,

            'SEGUIMIENTO' AS tipo_registro,

            NULL AS excepcion_motivo,

            u.nombre AS responsable_nombre

        FROM seguimientos s

        LEFT JOIN usuarios u
            ON s.responsable_id = u.id

        WHERE s.joven_id = :joven_id

        ORDER BY

            s.fecha_contacto DESC,

            s.id DESC
    ");

    $stmt->execute([

        ':joven_id' =>
            $jovenId
    ]);

    return $stmt->fetchAll(
        PDO::FETCH_ASSOC
    );
}


/* ==========================================================
   OBTENER HISTORIAL COMPLETO POR JOVEN
========================================================== */

function obtenerHistorialJoven(
    PDO $pdo,
    int $jovenId
): array
{
    if ($jovenId <= 0) {

        return [];
    }

    $stmt = $pdo->prepare("
        SELECT

            s.id AS registro_id,

            s.id AS seguimiento_id,

            s.joven_id,

            'SEGUIMIENTO' AS tipo_registro,

            s.modalidad_contacto,

            s.estado_proceso,

            s.observaciones,

            s.fecha_contacto AS fecha_registro,

            s.fecha_contacto,

            s.responsable_id,

            u.nombre AS responsable_nombre,

            NULL AS excepcion_id,

            NULL AS excepcion_motivo,

            NULL AS excepcion_anio,

            NULL AS excepcion_mes,

            NULL AS excepcion_created_at

        FROM seguimientos s

        LEFT JOIN usuarios u
            ON s.responsable_id = u.id

        WHERE s.joven_id = :joven_id_1


        UNION ALL


        SELECT

            e.id AS registro_id,

            NULL AS seguimiento_id,

            e.joven_id,

            'EXCEPCION' AS tipo_registro,

            NULL AS modalidad_contacto,

            NULL AS estado_proceso,

            e.observaciones,

            e.created_at AS fecha_registro,

            NULL AS fecha_contacto,

            e.responsable_id,

            u.nombre AS responsable_nombre,

            e.id AS excepcion_id,

            e.motivo AS excepcion_motivo,

            e.anio AS excepcion_anio,

            e.mes AS excepcion_mes,

            e.created_at AS excepcion_created_at

        FROM excepciones_seguimiento e

        LEFT JOIN usuarios u
            ON e.responsable_id = u.id

        WHERE e.joven_id = :joven_id_2


        ORDER BY

            fecha_registro DESC,

            registro_id DESC
    ");

    $stmt->execute([

        ':joven_id_1' =>
            $jovenId,

        ':joven_id_2' =>
            $jovenId
    ]);

    return $stmt->fetchAll(
        PDO::FETCH_ASSOC
    );
}


/* ==========================================================
   COMPATIBILIDAD CON FUNCIÓN ANTERIOR
========================================================== */

function obtenerHistorialSeguimientoPorJoven(
    PDO $pdo,
    int $jovenId
): array
{
    return obtenerHistorialJoven(
        $pdo,
        $jovenId
    );
}


/* ==========================================================
   CONTAR SEGUIMIENTOS REALES DEL MES
========================================================== */

function contarSeguimientosMes(
    PDO $pdo,
    int $jovenId
): int
{
    if ($jovenId <= 0) {

        return 0;
    }

    $stmt = $pdo->prepare("
        SELECT COUNT(*)

        FROM seguimientos

        WHERE joven_id = :id

        AND MONTH(fecha_contacto)
            = MONTH(CURDATE())

        AND YEAR(fecha_contacto)
            = YEAR(CURDATE())
    ");

    $stmt->execute([

        ':id' =>
            $jovenId
    ]);

    return (int)$stmt->fetchColumn();
}


/* ==========================================================
   JÓVENES SIN SEGUIMIENTO
========================================================== */

function obtenerJovenesSinSeguimiento(): array
{
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT

            j.id,

            j.nombre_completo,

            j.telefono,

            j.genero,

            j.estado_espiritual

        FROM jovenes j

        WHERE j.estado_actividad = 'ACTIVO'

        AND j.estado_espiritual = 'NUEVO'


        /*
        |--------------------------------------------------------------------------
        | NO TIENE SEGUIMIENTO REAL ESTE MES
        |--------------------------------------------------------------------------
        */

        AND NOT EXISTS (

            SELECT 1

            FROM seguimientos s

            WHERE s.joven_id = j.id

            AND MONTH(s.fecha_contacto)
                = MONTH(CURDATE())

            AND YEAR(s.fecha_contacto)
                = YEAR(CURDATE())
        )


        /*
        |--------------------------------------------------------------------------
        | NO TIENE EXCEPCIÓN ESTE MES
        |--------------------------------------------------------------------------
        */

        AND NOT EXISTS (

            SELECT 1

            FROM excepciones_seguimiento e

            WHERE e.joven_id = j.id

            AND e.anio = YEAR(CURDATE())

            AND e.mes = MONTH(CURDATE())
        )

        ORDER BY

            j.nombre_completo ASC
    ");

    $stmt->execute();

    return $stmt->fetchAll(
        PDO::FETCH_ASSOC
    );
}


/* ==========================================================
   OBTENER SEGUIMIENTO POR ID
========================================================== */

function obtenerSeguimientoPorId(
    PDO $pdo,
    int $id
): ?array
{
    if ($id <= 0) {

        return null;
    }

    $stmt = $pdo->prepare("
        SELECT

            s.*,

            'SEGUIMIENTO' AS tipo_registro,

            j.nombre_completo AS joven_nombre,

            u.nombre AS responsable_nombre

        FROM seguimientos s

        INNER JOIN jovenes j
            ON s.joven_id = j.id

        LEFT JOIN usuarios u
            ON s.responsable_id = u.id

        WHERE s.id = :id

        LIMIT 1
    ");

    $stmt->execute([

        ':id' =>
            $id
    ]);

    return $stmt->fetch(
        PDO::FETCH_ASSOC
    ) ?: null;
}


/* ==========================================================
   EXISTE SEGUIMIENTO
========================================================== */

function existeSeguimiento(
    PDO $pdo,
    int $id
): bool
{
    if ($id <= 0) {

        return false;
    }

    $stmt = $pdo->prepare("
        SELECT id

        FROM seguimientos

        WHERE id = :id

        LIMIT 1
    ");

    $stmt->execute([

        ':id' =>
            $id
    ]);

    return (bool)$stmt->fetchColumn();
}


/* ==========================================================
   VALIDAR SEGUIMIENTO
========================================================== */

function validarSeguimiento(
    PDO $pdo,
    int $id
): void
{
    if ($id <= 0) {

        throw new Exception(
            'Seguimiento inválido.'
        );
    }

    if (!existeSeguimiento(
        $pdo,
        $id
    )) {

        throw new Exception(
            'El seguimiento no existe.'
        );
    }
}


/* ==========================================================
   ELIMINAR SEGUIMIENTO
========================================================== */

function eliminarSeguimiento(
    PDO $pdo,
    int $id
): void
{
    exigirPermiso(
        'gestionar_seguimientos'
    );

    validarSeguimiento(
        $pdo,
        $id
    );

    $stmt = $pdo->prepare("
        DELETE
        FROM seguimientos
        WHERE id = :id
    ");

    $stmt->execute([

        ':id' =>
            $id
    ]);
}