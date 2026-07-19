<?php

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

            s.modalidad_contacto,

            s.estado_proceso,

            s.observaciones,

            s.fecha_contacto,

            u.nombre AS responsable_nombre

        FROM seguimientos s

        INNER JOIN jovenes j
            ON s.joven_id = j.id

        LEFT JOIN usuarios u
            ON s.responsable_id = u.id

        WHERE MONTH(
            s.fecha_contacto
        ) = MONTH(CURDATE())

        AND YEAR(
            s.fecha_contacto
        ) = YEAR(CURDATE())

        ORDER BY

            s.fecha_contacto DESC,

            j.nombre_completo ASC
    ");

    $stmt->execute();

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

    $stmt = $pdo->prepare("
        SELECT

            s.*,

            u.nombre AS responsable_nombre

        FROM seguimientos s

        LEFT JOIN usuarios u
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

