
<?php
require_once __DIR__ . "/../middleware/permiso.php";
require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../middleware/actividad.php";

/* =========================
   SEGURIDAD
========================= */
if (!tienePermiso('ver_dashboard')) {
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
            AND estado_actividad != 'ELIMINADO'
        ) as servidores

    FROM jovenes

    WHERE estado_actividad != 'ELIMINADO'
");

$stmtResumen->execute();

$resumen = $stmtResumen->fetch(PDO::FETCH_ASSOC);

/* =========================
   VARIABLES
========================= */
$totalJovenes = (int)($resumen['total'] ?? 0);
$activos = (int)($resumen['activos'] ?? 0);
$inactivos = (int)($resumen['inactivos'] ?? 0);
$totalServidores = (int)($resumen['servidores'] ?? 0);

/* =========================
   TOTAL REUNIONES
========================= */
$totalReuniones = (int)$pdo
->query("SELECT COUNT(*) FROM reuniones")
->fetchColumn();

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
   NUEVOS VS ANTIGUOS
========================= */
$stmtNuevos = $pdo->prepare("
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

$stmtNuevos->execute();

$nuevosData = $stmtNuevos->fetch(PDO::FETCH_ASSOC);

/* =========================
   ALERTAS
========================= */
$stmtAlertas = $pdo->prepare("
SELECT 
    j.id,

    SUM(CASE 
        WHEN MONTH(r.fecha) = MONTH(CURDATE())
        AND YEAR(r.fecha) = YEAR(CURDATE())
        AND a.asistio = 1
        THEN 1 ELSE 0 END
    ) as mes0,

    SUM(CASE 
        WHEN MONTH(r.fecha) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
        AND YEAR(r.fecha) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
        AND a.asistio = 1
        THEN 1 ELSE 0 END
    ) as mes1,

    SUM(CASE 
        WHEN MONTH(r.fecha) = MONTH(DATE_SUB(CURDATE(), INTERVAL 2 MONTH))
        AND YEAR(r.fecha) = YEAR(DATE_SUB(CURDATE(), INTERVAL 2 MONTH))
        AND a.asistio = 1
        THEN 1 ELSE 0 END
    ) as mes2

FROM jovenes j

LEFT JOIN asistencia a
    ON a.joven_id = j.id

LEFT JOIN reuniones r
    ON r.id = a.reunion_id

WHERE j.estado_actividad != 'ELIMINADO'

GROUP BY j.id
");

$stmtAlertas->execute();

$dataAlertas = $stmtAlertas->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   PROCESAR ALERTAS
========================= */
$riesgo_count = 0;
$alto_count = 0;

foreach($dataAlertas as $j){

    $mes0 = $j["mes0"];
    $mes1 = $j["mes1"];
    $mes2 = $j["mes2"];

    if ($mes1 <= 1 && $mes2 <= 1) {

        $alto_count++;

    } elseif ($mes0 <= 1) {

        $riesgo_count++;
    }
}

$alertas = $riesgo_count + $alto_count;


/* =========================
   DATA FINAL
========================= */

$data = [

    "resumen" => [

        "totalJovenes" => $totalJovenes,

        "activos" => $activos,

        "inactivos" => $inactivos,

        "servidores" => $totalServidores,

        "reuniones" => $totalReuniones,

        "asistencia" => $porcentajeGeneral,

        "nuevos" => (int)($nuevosData["nuevos"] ?? 0),

        "antiguos" => (int)($nuevosData["antiguos"] ?? 0)
    ],

    "graficas" => [
        "mensual" => [],
        "tipos" => [],
        "estado" => []
    ],

    "alertas" => $alertas,

    "riesgo" => $riesgo_count,

    "alto" => $alto_count
];
