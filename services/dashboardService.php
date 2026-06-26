<?php

/* =========================================================
   DASHBOARD COMPLETO
========================================================= */

function obtenerDashboardData(PDO $pdo): array
{
    $resumen = obtenerResumenGeneral($pdo);

    $resumen["reuniones"] =
        obtenerTotalReuniones($pdo);

    $resumen["asistencia"] =
        obtenerAsistenciaGeneral($pdo);

    $nuevos = obtenerNuevosAntiguos($pdo);

    $resumen["nuevos"] =
        $nuevos["nuevos"];

    $resumen["antiguos"] =
        $nuevos["antiguos"];

    $alertas = obtenerAlertas($pdo);

    return [

        "resumen" => $resumen,

        "graficas" => [

            "mensual" => [],
            "tipos" => [],
            "estado" => []
        ],

        "alertas" =>
            $alertas["total"],

        "riesgo" =>
            $alertas["riesgo"],

        "alto" =>
            $alertas["alto"]
    ];
}


/* =========================================================
   RESUMEN GENERAL
========================================================= */

function obtenerResumenGeneral(
    PDO $pdo
): array {

    $stmt = $pdo->prepare("
        SELECT

            COUNT(*) as total,

            SUM(
                estado_actividad = 'ACTIVO'
            ) as activos,

            SUM(
                estado_actividad = 'INACTIVO'
            ) as inactivos,

            SUM(
                es_servidor = 1
                AND estado_actividad != 'ELIMINADO'
            ) as servidores

        FROM jovenes

        WHERE estado_actividad != 'ELIMINADO'
    ");

    $stmt->execute();

    $data =
        $stmt->fetch(PDO::FETCH_ASSOC);

    return [

        "totalJovenes" =>
            (int)($data["total"] ?? 0),

        "activos" =>
            (int)($data["activos"] ?? 0),

        "inactivos" =>
            (int)($data["inactivos"] ?? 0),

        "servidores" =>
            (int)($data["servidores"] ?? 0)
    ];
}


/* =========================================================
   TOTAL REUNIONES
========================================================= */

function obtenerTotalReuniones(
    PDO $pdo
): int {

    return (int) $pdo
        ->query("
            SELECT COUNT(*)
            FROM reuniones
        ")
        ->fetchColumn();
}


/* =========================================================
   ASISTENCIA GENERAL
========================================================= */

function obtenerAsistenciaGeneral(
    PDO $pdo
): float {

    $stmt = $pdo->prepare("
        SELECT

            COUNT(*) as total,

            SUM(asistio = 1)
            as presentes

        FROM asistencia
    ");

    $stmt->execute();

    $data =
        $stmt->fetch(PDO::FETCH_ASSOC);

    $total =
        (int)($data["total"] ?? 0);

    $presentes =
        (int)($data["presentes"] ?? 0);

    return $total > 0

        ? round(
            ($presentes / $total) * 100,
            1
        )

        : 0;
}


/* =========================================================
   NUEVOS VS ANTIGUOS
========================================================= */

function obtenerNuevosAntiguos(
    PDO $pdo
): array {

    $stmt = $pdo->prepare("
        SELECT

            SUM(
                TIMESTAMPDIFF(
                    MONTH,
                    fecha_ingreso,
                    CURDATE()
                ) <= 3
            ) as nuevos,

            SUM(
                TIMESTAMPDIFF(
                    MONTH,
                    fecha_ingreso,
                    CURDATE()
                ) > 3
            ) as antiguos

        FROM jovenes

        WHERE estado_actividad != 'ELIMINADO'
    ");

    $stmt->execute();

    $data =
        $stmt->fetch(PDO::FETCH_ASSOC);

    return [

        "nuevos" =>
            (int)($data["nuevos"] ?? 0),

        "antiguos" =>
            (int)($data["antiguos"] ?? 0)
    ];
}


/* =========================================================
   ALERTAS
========================================================= */

function obtenerAlertas(
    PDO $pdo
): array {

    $stmt = $pdo->prepare("

        SELECT

            j.id,

            SUM(
                CASE

                    WHEN MONTH(r.fecha)
                    = MONTH(CURDATE())

                    AND YEAR(r.fecha)
                    = YEAR(CURDATE())

                    AND a.asistio = 1

                    THEN 1

                    ELSE 0

                END

            ) as mes0,

            SUM(
                CASE

                    WHEN MONTH(r.fecha)
                    = MONTH(
                        DATE_SUB(
                            CURDATE(),
                            INTERVAL 1 MONTH
                        )
                    )

                    AND YEAR(r.fecha)
                    = YEAR(
                        DATE_SUB(
                            CURDATE(),
                            INTERVAL 1 MONTH
                        )
                    )

                    AND a.asistio = 1

                    THEN 1

                    ELSE 0

                END

            ) as mes1,

            SUM(
                CASE

                    WHEN MONTH(r.fecha)
                    = MONTH(
                        DATE_SUB(
                            CURDATE(),
                            INTERVAL 2 MONTH
                        )
                    )

                    AND YEAR(r.fecha)
                    = YEAR(
                        DATE_SUB(
                            CURDATE(),
                            INTERVAL 2 MONTH
                        )
                    )

                    AND a.asistio = 1

                    THEN 1

                    ELSE 0

                END

            ) as mes2

        FROM jovenes j

        LEFT JOIN asistencia a

            ON a.joven_id = j.id

        LEFT JOIN reuniones r

            ON r.id = a.reunion_id

        WHERE j.estado_actividad != 'ELIMINADO'

        GROUP BY j.id
    ");

    $stmt->execute();

    $data =
        $stmt->fetchAll(PDO::FETCH_ASSOC);

    $riesgo = 0;

    $alto = 0;

    foreach ($data as $joven) {

        $mes0 = $joven["mes0"];
        $mes1 = $joven["mes1"];
        $mes2 = $joven["mes2"];

        if (
            $mes1 <= 1 &&
            $mes2 <= 1
        ) {

            $alto++;

        } elseif ($mes0 <= 1) {

            $riesgo++;
        }
    }

    return [

        "total" =>
            $riesgo + $alto,

        "riesgo" =>
            $riesgo,

        "alto" =>
            $alto
    ];
}