<?php

declare(strict_types=1);

require_once __DIR__ . '/../middleware/permiso.php';
require_once __DIR__ . '/jovenService.php';

/*
|--------------------------------------------------------------------------
| Excepción de Seguimiento Service
|--------------------------------------------------------------------------
|
| Las excepciones NO son seguimientos normales.
|
| Son registros administrativos que justifican por qué un joven
| no recibió seguimiento durante un período determinado.
|
| Este archivo es el ÚNICO responsable de:
|
|   - validar excepciones
|   - crear excepciones
|   - consultar excepciones
|   - actualizar excepciones
|   - eliminar excepciones
|
| IMPORTANTE:
|
| seguimientoService.php puede consultar la tabla
| excepciones_seguimiento para construir historiales y resúmenes,
| pero NO debe declarar funciones CRUD de excepciones.
|
|--------------------------------------------------------------------------
*/


/* ==========================================================
   CONSTANTES
========================================================== */

const MOTIVOS_EXCEPCION_SEGUIMIENTO = [

    'SIN_TELEFONO',
    'JOVEN_ANTIGUO',
    'REGRESO',
    'TRASLADO',
    'NO_CORRESPONDE',
    'OTRO'

];


/* ==========================================================
   NOMBRE LEGIBLE DEL MOTIVO
========================================================== */

function nombreMotivoExcepcionSeguimiento(
    string $motivo
): string
{
    $motivos = [

        'SIN_TELEFONO' =>
            'No tiene teléfono',

        'JOVEN_ANTIGUO' =>
            'Joven antiguo',

        'REGRESO' =>
            'Regresó al ministerio',

        'TRASLADO' =>
            'Viene de otra iglesia',

        'NO_CORRESPONDE' =>
            'No corresponde seguimiento de nuevo',

        'OTRO' =>
            'Otro motivo'
    ];

    return $motivos[$motivo] ?? $motivo;
}


/* ==========================================================
   VALIDAR MOTIVO
========================================================== */

function validarMotivoExcepcionSeguimiento(
    ?string $motivo
): string
{
    $motivo = strtoupper(
        trim((string)$motivo)
    );

    if ($motivo === '') {

        throw new Exception(
            'Debe seleccionar un motivo para la excepción.'
        );
    }

    if (!in_array(
        $motivo,
        MOTIVOS_EXCEPCION_SEGUIMIENTO,
        true
    )) {

        throw new Exception(
            'El motivo seleccionado no es válido.'
        );
    }

    return $motivo;
}


/* ==========================================================
   VALIDAR OBSERVACIONES
========================================================== */

function validarObservacionesExcepcionSeguimiento(
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
   VALIDAR JOVEN
========================================================== */

function validarJovenExcepcionSeguimiento(
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
   VALIDAR PERÍODO
========================================================== */

function validarPeriodoExcepcionSeguimiento(
    int $anio,
    int $mes
): void
{
    if (
        $anio < 2000 ||
        $anio > 2100
    ) {

        throw new Exception(
            'El año de la excepción no es válido.'
        );
    }

    if (
        $mes < 1 ||
        $mes > 12
    ) {

        throw new Exception(
            'El mes de la excepción no es válido.'
        );
    }
}


/* ==========================================================
   VALIDAR AÑO
========================================================== */

function validarAnioExcepcionSeguimiento(
    mixed $anio
): int
{
    if (
        $anio === null ||
        $anio === '' ||
        !is_numeric($anio)
    ) {

        throw new Exception(
            'El año de la excepción no es válido.'
        );
    }

    $anio = (int)$anio;

    if (
        $anio < 2000 ||
        $anio > 2100
    ) {

        throw new Exception(
            'El año de la excepción no es válido.'
        );
    }

    return $anio;
}


/* ==========================================================
   VALIDAR MES
========================================================== */

function validarMesExcepcionSeguimiento(
    mixed $mes
): int
{
    if (
        $mes === null ||
        $mes === '' ||
        !is_numeric($mes)
    ) {

        throw new Exception(
            'El mes de la excepción no es válido.'
        );
    }

    $mes = (int)$mes;

    if (
        $mes < 1 ||
        $mes > 12
    ) {

        throw new Exception(
            'El mes de la excepción no es válido.'
        );
    }

    return $mes;
}


/* ==========================================================
   VALIDAR RESPONSABLE
========================================================== */

function validarResponsableExcepcionSeguimiento(
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

        ':id' =>
            $responsableId
    ]);

    if (!$stmt->fetchColumn()) {

        throw new Exception(
            'El responsable seleccionado no existe.'
        );
    }

    return $responsableId;
}


/* ==========================================================
   PREPARAR DATOS DE EXCEPCIÓN
========================================================== */

function prepararDatosExcepcionSeguimiento(
    PDO $pdo,
    array $datos
): array
{
    $jovenId = (int)(
        $datos['joven_id'] ?? 0
    );

    $joven =
        validarJovenExcepcionSeguimiento(
            $pdo,
            $jovenId
        );


    $motivo =
        validarMotivoExcepcionSeguimiento(
            $datos['motivo'] ?? null
        );


    $observaciones =
        validarObservacionesExcepcionSeguimiento(
            $datos['observaciones'] ?? null
        );


    $anio =
        validarAnioExcepcionSeguimiento(
            $datos['anio'] ?? null
        );


    $mes =
        validarMesExcepcionSeguimiento(
            $datos['mes'] ?? null
        );


    $responsableId = null;

    if (
        isset($datos['responsable_id']) &&
        $datos['responsable_id'] !== ''
    ) {

        $responsableId =
            (int)$datos['responsable_id'];
    }


    $responsableId =
        validarResponsableExcepcionSeguimiento(
            $pdo,
            $responsableId
        );


    return [

        'jovenId' =>
            $jovenId,

        'joven' =>
            $joven,

        'motivo' =>
            $motivo,

        'observaciones' =>
            $observaciones,

        'anio' =>
            $anio,

        'mes' =>
            $mes,

        'responsableId' =>
            $responsableId
    ];
}


/* ==========================================================
   EXISTE EXCEPCIÓN DEL PERÍODO
========================================================== */

function existeExcepcionSeguimientoPeriodo(
    PDO $pdo,
    int $jovenId,
    int $anio,
    int $mes,
    ?int $exceptoId = null
): bool
{
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

        FROM excepciones_seguimiento

        WHERE joven_id = :joven_id

        AND anio = :anio

        AND mes = :mes
    ";


    $params = [

        ':joven_id' =>
            $jovenId,

        ':anio' =>
            $anio,

        ':mes' =>
            $mes
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


    $stmt =
        $pdo->prepare($sql);


    $stmt->execute(
        $params
    );


    return (bool)$stmt->fetchColumn();
}


/* ==========================================================
   EXISTE EXCEPCIÓN DEL MES
========================================================== */

/*
| Compatibilidad con código anterior.
|
| Mantiene el nombre que ya existía en la primera versión.
|
*/

function existeExcepcionSeguimientoMes(
    PDO $pdo,
    int $jovenId,
    int $anio,
    int $mes
): bool
{
    return existeExcepcionSeguimientoPeriodo(
        $pdo,
        $jovenId,
        $anio,
        $mes
    );
}


/* ==========================================================
   OBTENER EXCEPCIÓN POR ID
========================================================== */

function obtenerExcepcionSeguimientoPorId(
    PDO $pdo,
    int $id
): ?array
{
    if ($id <= 0) {

        return null;
    }


    $stmt = $pdo->prepare("
        SELECT

            e.*,

            j.nombre_completo AS joven_nombre,

            u.nombre AS responsable_nombre

        FROM excepciones_seguimiento e

        INNER JOIN jovenes j
            ON e.joven_id = j.id

        LEFT JOIN usuarios u
            ON e.responsable_id = u.id

        WHERE e.id = :id

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
   OBTENER EXCEPCIÓN DEL MES
========================================================== */

function obtenerExcepcionSeguimientoMes(
    PDO $pdo,
    int $jovenId,
    ?int $anio = null,
    ?int $mes = null
): ?array
{
    $anio ??=
        (int)date('Y');

    $mes ??=
        (int)date('m');


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

            e.*,

            j.nombre_completo AS joven_nombre,

            u.nombre AS responsable_nombre

        FROM excepciones_seguimiento e

        INNER JOIN jovenes j
            ON e.joven_id = j.id

        LEFT JOIN usuarios u
            ON e.responsable_id = u.id

        WHERE e.joven_id = :joven_id

        AND e.anio = :anio

        AND e.mes = :mes

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


    return $stmt->fetch(
        PDO::FETCH_ASSOC
    ) ?: null;
}


/* ==========================================================
   OBTENER TODAS LAS EXCEPCIONES DE UN JOVEN
========================================================== */

function obtenerExcepcionesPorJoven(
    PDO $pdo,
    int $jovenId
): array
{
    if ($jovenId <= 0) {

        return [];
    }


    $stmt = $pdo->prepare("
        SELECT

            e.*,

            u.nombre AS responsable_nombre

        FROM excepciones_seguimiento e

        LEFT JOIN usuarios u
            ON e.responsable_id = u.id

        WHERE e.joven_id = :joven_id

        ORDER BY

            e.anio DESC,

            e.mes DESC,

            e.id DESC
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
   OBTENER EXCEPCIONES DEL MES ACTUAL
========================================================== */

function obtenerExcepcionesMes(
    PDO $pdo
): array
{
    $stmt = $pdo->prepare("
        SELECT

            e.*,

            j.nombre_completo,

            j.telefono,

            j.genero,

            u.nombre AS responsable_nombre

        FROM excepciones_seguimiento e

        INNER JOIN jovenes j
            ON e.joven_id = j.id

        LEFT JOIN usuarios u
            ON e.responsable_id = u.id

        WHERE e.anio = YEAR(CURDATE())

        AND e.mes = MONTH(CURDATE())

        ORDER BY

            j.nombre_completo ASC
    ");


    $stmt->execute();


    return $stmt->fetchAll(
        PDO::FETCH_ASSOC
    );
}


/* ==========================================================
   CREAR EXCEPCIÓN
========================================================== */

function crearExcepcionSeguimiento(
    PDO $pdo,
    array $datos
): int
{
    exigirPermiso(
        'gestionar_seguimientos'
    );


    $datos =
        prepararDatosExcepcionSeguimiento(
            $pdo,
            $datos
        );


    /*
    |--------------------------------------------------------------------------
    | EVITAR DUPLICADO
    |--------------------------------------------------------------------------
    */

    if (
        existeExcepcionSeguimientoPeriodo(
            $pdo,
            $datos['jovenId'],
            $datos['anio'],
            $datos['mes']
        )
    ) {

        throw new Exception(
            'El joven ya tiene una excepción registrada para este año y mes.'
        );
    }


    $pdo->beginTransaction();


    try {

        $stmt = $pdo->prepare("
            INSERT INTO excepciones_seguimiento
            (
                joven_id,
                motivo,
                observaciones,
                anio,
                mes,
                responsable_id
            )
            VALUES
            (
                :joven_id,
                :motivo,
                :observaciones,
                :anio,
                :mes,
                :responsable_id
            )
        ");


        $stmt->execute([

            ':joven_id' =>
                $datos['jovenId'],

            ':motivo' =>
                $datos['motivo'],

            ':observaciones' =>
                $datos['observaciones'],

            ':anio' =>
                $datos['anio'],

            ':mes' =>
                $datos['mes'],

            ':responsable_id' =>
                $datos['responsableId']
        ]);


        $excepcionId =
            (int)$pdo->lastInsertId();


        $pdo->commit();


        return $excepcionId;

    } catch (Throwable $e) {

        if ($pdo->inTransaction()) {

            $pdo->rollBack();
        }

        throw $e;
    }
}


/* ==========================================================
   ACTUALIZAR EXCEPCIÓN
========================================================== */

function actualizarExcepcionSeguimiento(
    PDO $pdo,
    int $id,
    array $datos
): void
{
    exigirPermiso(
        'gestionar_seguimientos'
    );


    if ($id <= 0) {

        throw new Exception(
            'Excepción inválida.'
        );
    }


    $excepcionActual =
        obtenerExcepcionSeguimientoPorId(
            $pdo,
            $id
        );


    if (!$excepcionActual) {

        throw new Exception(
            'La excepción no existe.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | EL JOVEN NO SE PUEDE CAMBIAR
    |--------------------------------------------------------------------------
    |
    | La excepción pertenece al joven original.
    |
    */

    $datos['joven_id'] =
        (int)$excepcionActual['joven_id'];


    $datos =
        prepararDatosExcepcionSeguimiento(
            $pdo,
            $datos
        );


    /*
    |--------------------------------------------------------------------------
    | EVITAR DUPLICADO AL CAMBIAR AÑO/MES
    |--------------------------------------------------------------------------
    */

    if (
        existeExcepcionSeguimientoPeriodo(
            $pdo,
            $datos['jovenId'],
            $datos['anio'],
            $datos['mes'],
            $id
        )
    ) {

        throw new Exception(
            'El joven ya tiene otra excepción registrada para este año y mes.'
        );
    }


    $stmt = $pdo->prepare("
        UPDATE excepciones_seguimiento

        SET

            motivo = :motivo,

            observaciones = :observaciones,

            anio = :anio,

            mes = :mes,

            responsable_id = :responsable_id

        WHERE id = :id

        LIMIT 1
    ");


    $stmt->execute([

        ':motivo' =>
            $datos['motivo'],

        ':observaciones' =>
            $datos['observaciones'],

        ':anio' =>
            $datos['anio'],

        ':mes' =>
            $datos['mes'],

        ':responsable_id' =>
            $datos['responsableId'],

        ':id' =>
            $id
    ]);
}


/* ==========================================================
   ELIMINAR EXCEPCIÓN
========================================================== */

function eliminarExcepcionSeguimiento(
    PDO $pdo,
    int $id
): void
{
    exigirPermiso(
        'gestionar_seguimientos'
    );


    if ($id <= 0) {

        throw new Exception(
            'Excepción inválida.'
        );
    }


    if (
        !obtenerExcepcionSeguimientoPorId(
            $pdo,
            $id
        )
    ) {

        throw new Exception(
            'La excepción no existe.'
        );
    }


    $stmt = $pdo->prepare("
        DELETE

        FROM excepciones_seguimiento

        WHERE id = :id

        LIMIT 1
    ");


    $stmt->execute([

        ':id' =>
            $id
    ]);
}