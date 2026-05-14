
<?php

require_once __DIR__ . "/../middleware/permiso.php";
require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../middleware/actividad.php";

/* =========================
   SEGURIDAD
========================= */

if (!tienePermiso('gestionar_usuarios')) {
    die("Acceso denegado.");
}

/* =========================
   ACTIVIDAD
========================= */

actualizarEstadoActividad();

/* =========================
   RESUMEN GENERAL
========================= */

$stmtResumen = $pdo->prepare("
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
        ) as servidores

    FROM jovenes
");

$stmtResumen->execute();

$resumen = $stmtResumen->fetch(PDO::FETCH_ASSOC);

/* =========================
   VARIABLES RESUMEN
========================= */

$totalJovenes = (int)($resumen['total'] ?? 0);

$activos = (int)($resumen['activos'] ?? 0);

$inactivos = (int)($resumen['inactivos'] ?? 0);

$totalServidores = (int)($resumen['servidores'] ?? 0);

/* =========================
   TOTAL REUNIONES
========================= */

$stmtReuniones = $pdo->prepare("
    SELECT COUNT(*)
    FROM reuniones
");

$stmtReuniones->execute();

$totalReuniones = (int)$stmtReuniones->fetchColumn();

/* =========================
   ASISTENCIA GENERAL
========================= */

$stmtAsistencia = $pdo->prepare("
    SELECT 
        COUNT(*) as total,
        SUM(asistio = 1) as presentes
    FROM asistencia
");

$stmtAsistencia->execute();

$asistencia = $stmtAsistencia->fetch(PDO::FETCH_ASSOC);

$totalRegistros = (int)($asistencia['total'] ?? 0);

$totalPresentes = (int)($asistencia['presentes'] ?? 0);

$porcentajeGeneral = $totalRegistros > 0
    ? round(($totalPresentes / $totalRegistros) * 100, 1)
    : 0;

/* =========================
   📊 ASISTENCIA MENSUAL
========================= */

$stmtMensual = $pdo->prepare("
    SELECT 

        DATE_FORMAT(r.fecha, '%b %Y') as mes,

        COUNT(
            CASE
                WHEN a.asistio = 1 THEN 1
            END
        ) as presentes

    FROM asistencia a

    INNER JOIN reuniones r
        ON a.reunion_id = r.id

    GROUP BY
        YEAR(r.fecha),
        MONTH(r.fecha)

    ORDER BY
        YEAR(r.fecha) ASC,
        MONTH(r.fecha) ASC
");

$stmtMensual->execute();

$reporteMensual = $stmtMensual->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   🥧 ESTADO ESPIRITUAL
========================= */

$stmtEspiritual = $pdo->prepare("
    SELECT

        CASE

            WHEN estado_espiritual IS NULL
                 OR estado_espiritual = ''
            THEN 'Sin definir'

            ELSE estado_espiritual

        END as tipo,

        COUNT(*) as total

    FROM jovenes

    GROUP BY estado_espiritual

    ORDER BY total DESC
");

$stmtEspiritual->execute();

$estadoEspiritual = $stmtEspiritual->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   🍩 NUEVOS VS ANTIGUOS
========================= */

$stmtEstado = $pdo->prepare("
    SELECT

        CASE

            WHEN fecha_ingreso IS NULL
            THEN 'Sin fecha'

            WHEN TIMESTAMPDIFF(
                MONTH,
                fecha_ingreso,
                CURDATE()
            ) <= 3
            THEN 'Nuevos'

            ELSE 'Antiguos'

        END as tipo,

        COUNT(*) as total

    FROM jovenes

    GROUP BY tipo

    ORDER BY total DESC
");

$stmtEstado->execute();

$estado = $stmtEstado->fetchAll(PDO::FETCH_ASSOC);
/* =========================
   🤖 ALERTAS AUTOMÁTICAS
========================= */

$stmtAlertas = $pdo->prepare("
    SELECT j.id

    FROM jovenes j

    INNER JOIN asistencia a
        ON a.joven_id = j.id

    WHERE

        TIMESTAMPDIFF(
            MONTH,
            j.fecha_ingreso,
            CURDATE()
        ) <= 3

        AND a.asistio = 0

    GROUP BY j.id

    HAVING COUNT(a.id) >= 2
");

$stmtAlertas->execute();

$alertas = count(
    $stmtAlertas->fetchAll(PDO::FETCH_ASSOC)
);


/* =========================
   RESPUESTA FINAL
========================= */

return [

    "resumen" => [

        "totalJovenes" => $totalJovenes,
        "activos" => $activos,
        "inactivos" => $inactivos,
        "servidores" => $totalServidores,
        "reuniones" => $totalReuniones,
        "asistencia" => $porcentajeGeneral
    ],

    "graficas" => [

        "mensual" => $reporteMensual,

        "tipos" => $estadoEspiritual,

        "estado" => $estado
    ],

    "alertas" => $alertas

];