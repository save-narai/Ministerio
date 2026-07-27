<?php

<<<<<<< HEAD
require_once __DIR__ . "/../config/conexion.php";

/* ======================================================
   RESUMEN GENERAL DEL MES
====================================================== */

function obtenerResumenSeguimientosMes() {

    global $pdo;

    /*
    |--------------------------------------------------------------------------
    | MES ACTUAL
    |--------------------------------------------------------------------------
    */

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
    | TOTAL JÓVENES ACTIVOS DEL MINISTERIO
    |--------------------------------------------------------------------------
    */

    $totalActivos = (int)$pdo
        ->query("
            SELECT COUNT(*)

            FROM jovenes

            WHERE estado_actividad != 'ELIMINADO'
        ")
        ->fetchColumn();

    /*
    |--------------------------------------------------------------------------
    | SEGUIMIENTOS DEL MES
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT

            j.id AS joven_id,

            j.nombre_completo,

            CASE

                WHEN j.fecha_nacimiento IS NOT NULL

                THEN TIMESTAMPDIFF(
                    YEAR,
                    j.fecha_nacimiento,
                    CURDATE()
                )

                ELSE j.edad_manual

            END AS edad,

            j.telefono,

            j.genero,
=======
declare(strict_types=1);

/* =========================================================
   CONFIGURACIÓN
========================================================= */

const ESTADOS_SEGUIMIENTO = [

    "PENDIENTE",

    "EN_PROCESO",

    "FINALIZADO"

];

const MODALIDADES_SEGUIMIENTO = [

    "WHATSAPP",

    "LLAMADA",

    "VISITA"

];

/* =========================================================
   RESUMEN GENERAL DEL MES
========================================================= */

function obtenerResumenSeguimientosMes(PDO $pdo): array
{
    $mes = (int) date("m");

    $anio = (int) date("Y");

    $mesTexto = obtenerNombreMes($mes) . " de " . $anio;

    /* =====================================
       TOTAL JÓVENES ACTIVOS
    ====================================== */

    $stmt = $pdo->query("
        SELECT COUNT(*)

        FROM jovenes

        WHERE estado_actividad = 'ACTIVO'
    ");

    $totalActivos = (int) $stmt->fetchColumn();

    /* =====================================
       TOTAL CON SEGUIMIENTO
    ====================================== */

    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT joven_id)

        FROM seguimientos

        WHERE MONTH(fecha_contacto) = :mes

        AND YEAR(fecha_contacto) = :anio
    ");

    $stmt->execute([

        ":mes"  => $mes,

        ":anio" => $anio

    ]);

    $totalConSeguimiento = (int) $stmt->fetchColumn();

    /* =====================================
       TOTAL PENDIENTES
    ====================================== */

    $totalSinSeguimiento = max(

        0,

        $totalActivos - $totalConSeguimiento

    );

    /* =====================================
       PORCENTAJE
    ====================================== */

    $porcentaje = calcularPorcentajeSeguimiento(

        $totalActivos,

        $totalConSeguimiento

    );

    /* =====================================
       COLOR
    ====================================== */

    $color = obtenerColorCumplimiento(

        $porcentaje

    );

    return [

        "mes"                 => $mes,

        "anio"                => $anio,

        "mesTexto"            => $mesTexto,

        "totalActivos"        => $totalActivos,

        "totalConSeguimiento" => $totalConSeguimiento,

        "totalSinSeguimiento" => $totalSinSeguimiento,

        "porcentaje"          => $porcentaje,

        "color"               => $color

    ];
}

/* =========================================================
   NOMBRE DEL MES
========================================================= */

function obtenerNombreMes(int $mes): string
{
    $meses = [

        1  => "Enero",

        2  => "Febrero",

        3  => "Marzo",

        4  => "Abril",

        5  => "Mayo",

        6  => "Junio",

        7  => "Julio",

        8  => "Agosto",

        9  => "Septiembre",

        10 => "Octubre",

        11 => "Noviembre",

        12 => "Diciembre"

    ];

    return $meses[$mes] ?? "";
}

/* =========================================================
   CALCULAR PORCENTAJE
========================================================= */

function calcularPorcentajeSeguimiento(
    int $total,
    int $seguimiento
): int
{
    if ($total === 0) {

        return 0;

    }

    return (int) round(

        ($seguimiento / $total) * 100

    );
}

/* =========================================================
   COLOR DEL CUMPLIMIENTO
========================================================= */

function obtenerColorCumplimiento(
    int $porcentaje
): string
{
    if ($porcentaje >= 90) {

        return "success";

    }

    if ($porcentaje >= 70) {

        return "warning";

    }

    return "danger";
}

/* =========================================================
   HISTORIAL DEL MES
========================================================= */

function obtenerHistorialSeguimientos(
    PDO $pdo
): array
{
    $stmt = $pdo->prepare("
        SELECT

            s.id,

            s.joven_id,

            j.nombre_completo,

            j.genero,

            j.telefono,

            s.fecha_contacto,
>>>>>>> 3e2d89c (Actualización del proyecto)

            s.modalidad_contacto,

            s.estado_proceso,

            s.observaciones,

<<<<<<< HEAD
            s.fecha_contacto,

=======
>>>>>>> 3e2d89c (Actualización del proyecto)
            u.nombre AS responsable_nombre

        FROM seguimientos s

        INNER JOIN jovenes j
<<<<<<< HEAD
            ON s.joven_id = j.id

        LEFT JOIN usuarios u
            ON s.responsable_id = u.id

        WHERE MONTH(
            s.fecha_contacto
        ) = MONTH(CURDATE())

        AND YEAR(
            s.fecha_contacto
        ) = YEAR(CURDATE())
=======
            ON j.id = s.joven_id

        LEFT JOIN usuarios u
            ON u.id = s.responsable_id

        WHERE

            MONTH(s.fecha_contacto) = MONTH(CURDATE())

        AND

            YEAR(s.fecha_contacto) = YEAR(CURDATE())
>>>>>>> 3e2d89c (Actualización del proyecto)

        ORDER BY

            s.fecha_contacto DESC,

            j.nombre_completo ASC
    ");

    $stmt->execute();

<<<<<<< HEAD
    $seguimientosMes =
        $stmt->fetchAll(PDO::FETCH_ASSOC);

    /*
    |--------------------------------------------------------------------------
    | JÓVENES ÚNICOS CON SEGUIMIENTO
    |--------------------------------------------------------------------------
    */

    $jovenesConSeguimiento =
        array_unique(
            array_column(
                $seguimientosMes,
                'joven_id'
            )
        );

    $totalConSeguimiento =
        count($jovenesConSeguimiento);

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
        );

    /*
    |--------------------------------------------------------------------------
    | PORCENTAJE
    |--------------------------------------------------------------------------
    */

    $porcentaje =
        $totalActivos > 0

        ? round(
            (
                $totalConSeguimiento
                / $totalActivos
            ) * 100
        )

        : 0;

    /*
    |--------------------------------------------------------------------------
    | SEMÁFORO
    |--------------------------------------------------------------------------
    */

    $color = "danger";

    if ($porcentaje >= 90) {

        $color = "success";

    } elseif ($porcentaje >= 70) {

        $color = "warning";
    }

    /*
    |--------------------------------------------------------------------------
    | RESPUESTA
    |--------------------------------------------------------------------------
    */

    return [

        "mesTexto" => $mesTexto,

        "totalActivos" => $totalActivos,

        "seguimientosMes" => $seguimientosMes,

        "totalConSeguimiento" => $totalConSeguimiento,

        "totalSinSeguimiento" => $totalSinSeguimiento,

        "porcentaje" => $porcentaje,

        "color" => $color
    ];
}

/* ======================================================
   OBTENER SEGUIMIENTOS POR JOVEN
====================================================== */

function obtenerSeguimientosPorJoven($joven_id) {

    global $pdo;

=======
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/* =========================================================
   JÓVENES PENDIENTES
========================================================= */

function obtenerJovenesPendientes(
    PDO $pdo
): array
{
    $stmt = $pdo->prepare("
        SELECT

            j.id,

            j.nombre_completo,

            j.telefono,

            j.genero,

            j.estado_espiritual,

            j.estado_actividad

        FROM jovenes j

        WHERE

            j.estado_actividad = 'ACTIVO'

        AND

            j.id NOT IN (

                SELECT s.joven_id

                FROM seguimientos s

                WHERE

                    MONTH(s.fecha_contacto) = MONTH(CURDATE())

                AND

                    YEAR(s.fecha_contacto) = YEAR(CURDATE())

            )

        ORDER BY

            j.nombre_completo ASC
    ");

    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/* =========================================================
   OBTENER SEGUIMIENTO
========================================================= */

function obtenerSeguimientoPorId(
    PDO $pdo,
    int $id
): ?array
{
    $stmt = $pdo->prepare("
        SELECT

            s.*,

            j.nombre_completo,

            u.nombre AS responsable_nombre

        FROM seguimientos s

        INNER JOIN jovenes j
            ON j.id = s.joven_id

        LEFT JOIN usuarios u
            ON u.id = s.responsable_id

        WHERE

            s.id = :id

        LIMIT 1
    ");

    $stmt->execute([

        ":id" => $id

    ]);

    $seguimiento = $stmt->fetch(PDO::FETCH_ASSOC);

    return $seguimiento ?: null;
}

/* =========================================================
   HISTORIAL DE UN JOVEN
========================================================= */

function obtenerHistorialJoven(
    PDO $pdo,
    int $jovenId
): array
{
>>>>>>> 3e2d89c (Actualización del proyecto)
    $stmt = $pdo->prepare("
        SELECT

            s.*,

            u.nombre AS responsable_nombre

        FROM seguimientos s

        LEFT JOIN usuarios u
<<<<<<< HEAD
            ON s.responsable_id = u.id

        WHERE s.joven_id = :joven_id

        ORDER BY s.fecha_contacto DESC
    ");

    $stmt->execute([
        "joven_id" => $joven_id
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
/* ======================================================
   CONTAR SEGUIMIENTOS DEL MES
====================================================== */

function contarSeguimientosMes($joven_id) {

    global $pdo;

    $stmt = $pdo->prepare("
        SELECT COUNT(*)

        FROM seguimientos

        WHERE joven_id = :id

        AND MONTH(
            fecha_contacto
        ) = MONTH(CURDATE())

        AND YEAR(
            fecha_contacto
        ) = YEAR(CURDATE())
    ");

    $stmt->execute([
        "id" => $joven_id
    ]);

    return (int)$stmt->fetchColumn();
}

/* ======================================================
   JÓVENES SIN SEGUIMIENTO
====================================================== */

function obtenerJovenesSinSeguimiento(): array
{
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT

            id,
            nombre_completo,
            telefono,
            genero

        FROM jovenes

        WHERE estado_actividad = 'ACTIVO'

        AND id NOT IN (

            SELECT joven_id

            FROM seguimientos

            WHERE DATE_FORMAT(
                fecha_contacto,
                '%Y-%m'
            ) = DATE_FORMAT(
                CURDATE(),
                '%Y-%m'
            )

        )

        ORDER BY nombre_completo ASC
    ");

    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

=======
            ON u.id = s.responsable_id

        WHERE

            s.joven_id = :joven

        ORDER BY

            s.fecha_contacto DESC
    ");

    $stmt->execute([

        ":joven" => $jovenId

    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}                                                                                                                                                                          /* =========================================================
   CREAR SEGUIMIENTO
========================================================= */

function crearSeguimiento(
    PDO $pdo,
    array $datos
): int
{
    /* =====================================
       VALIDAR JOVEN
    ====================================== */

    if (!existeJoven(
        $pdo,
        (int) $datos["joven_id"]
    )) {

        throw new Exception(
            "El joven seleccionado no existe."
        );

    }

    /* =====================================
       VALIDAR RESPONSABLE
    ====================================== */

    if (!existeResponsable(
        $pdo,
        $datos["responsable_id"]
    )) {

        throw new Exception(
            "El responsable seleccionado no existe."
        );

    }

    /* =====================================
       VALIDAR DUPLICADO
    ====================================== */

    if (existeSeguimientoMes(
        $pdo,
        (int) $datos["joven_id"],
        $datos["fecha_contacto"]
    )) {

        throw new Exception(
            "Este joven ya tiene un seguimiento registrado durante este mes."
        );

    }

    /* =====================================
       INICIAR TRANSACCIÓN
    ====================================== */

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

                :joven,

                :fecha,

                :modalidad,

                :estado,

                :responsable,

                :observaciones

            )
        ");

        $stmt->execute([

            ":joven"         => $datos["joven_id"],

            ":fecha"         => $datos["fecha_contacto"],

            ":modalidad"     => $datos["modalidad_contacto"],

            ":estado"        => $datos["estado_proceso"],

            ":responsable"   => $datos["responsable_id"],

            ":observaciones" => $datos["observaciones"]

        ]);

        $seguimientoId = (int) $pdo->lastInsertId();

        $stmt = $pdo->prepare("
            UPDATE jovenes

            SET

                ultima_actividad = NOW(),

                estado_actividad = 'ACTIVO'

            WHERE id = :id
        ");

        $stmt->execute([

            ":id" => $datos["joven_id"]

        ]);

        $pdo->commit();

        return $seguimientoId;

    } catch (Throwable $e) {

        if ($pdo->inTransaction()) {

            $pdo->rollBack();

        }

        throw $e;
    }
}

/* =========================================================
   ELIMINAR SEGUIMIENTO
========================================================= */

function eliminarSeguimiento(
    PDO $pdo,
    int $id
): bool
{
    $stmt = $pdo->prepare("
        DELETE

        FROM seguimientos

        WHERE id = :id
    ");

    $stmt->execute([

        ":id" => $id

    ]);

    return $stmt->rowCount() > 0;
}

/* =========================================================
   ACTUALIZAR SEGUIMIENTO
========================================================= */

function actualizarSeguimiento(
    PDO $pdo,
    int $id,
    array $datos
): bool
{
    if (!existeSeguimiento($pdo, $id)) {

        throw new Exception(
            "El seguimiento no existe."
        );

    }

    $stmt = $pdo->prepare("
        UPDATE seguimientos

        SET

            fecha_contacto      = :fecha,

            modalidad_contacto  = :modalidad,

            estado_proceso      = :estado,

            responsable_id      = :responsable,

            observaciones       = :observaciones

        WHERE

            id = :id
    ");

    $stmt->execute([

        ":fecha"         => $datos["fecha_contacto"],

        ":modalidad"     => $datos["modalidad_contacto"],

        ":estado"        => $datos["estado_proceso"],

        ":responsable"   => $datos["responsable_id"],

        ":observaciones" => $datos["observaciones"],

        ":id"            => $id

    ]);

    return $stmt->rowCount() > 0;
}                                                                                                                                                  /* =========================================================
   EXISTE SEGUIMIENTO DEL MES
========================================================= */

function existeSeguimientoMes(
    PDO $pdo,
    int $jovenId,
    string $fecha
): bool
{
    $stmt = $pdo->prepare("
        SELECT 1

        FROM seguimientos

        WHERE

            joven_id = :joven

        AND

            MONTH(fecha_contacto) = MONTH(:fecha)

        AND

            YEAR(fecha_contacto) = YEAR(:fecha)

        LIMIT 1
    ");

    $stmt->execute([

        ":joven" => $jovenId,

        ":fecha" => $fecha

    ]);

    return (bool) $stmt->fetchColumn();
}

/* =========================================================
   EXISTE SEGUIMIENTO
========================================================= */

function existeSeguimiento(
    PDO $pdo,
    int $id
): bool
{
    $stmt = $pdo->prepare("
        SELECT 1

        FROM seguimientos

        WHERE

            id = :id

        LIMIT 1
    ");

    $stmt->execute([

        ":id" => $id

    ]);

    return (bool) $stmt->fetchColumn();
}

/* =========================================================
   EXISTE JOVEN
========================================================= */

function existeJoven(
    PDO $pdo,
    int $id
): bool
{
    $stmt = $pdo->prepare("
        SELECT 1

        FROM jovenes

        WHERE

            id = :id

        AND

            estado_actividad <> 'ELIMINADO'

        LIMIT 1
    ");

    $stmt->execute([

        ":id" => $id

    ]);

    return (bool) $stmt->fetchColumn();
}

/* =========================================================
   EXISTE RESPONSABLE
========================================================= */

function existeResponsable(
    PDO $pdo,
    ?int $id
): bool
{
    if ($id === null) {

        return true;

    }

    $stmt = $pdo->prepare("
        SELECT 1

        FROM usuarios

        WHERE

            id = :id

        LIMIT 1
    ");

    $stmt->execute([

        ":id" => $id

    ]);

    return (bool) $stmt->fetchColumn();
}
>>>>>>> 3e2d89c (Actualización del proyecto)
