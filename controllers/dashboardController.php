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
$resumen = $pdo->query("
    SELECT
        COUNT(*) as total,
        SUM(estado_actividad='ACTIVO') as activos,
        SUM(estado_actividad='INACTIVO') as inactivos,
        SUM(es_servidor=1) as servidores
    FROM jovenes
")->fetch(PDO::FETCH_ASSOC);

$totalJovenes     = (int)($resumen['total'] ?? 0);
$activos          = (int)($resumen['activos'] ?? 0);
$inactivos        = (int)($resumen['inactivos'] ?? 0);
$totalServidores  = (int)($resumen['servidores'] ?? 0);

/* =========================
   REUNIONES
========================= */
$totalReuniones = (int)$pdo->query("
    SELECT COUNT(*) FROM reuniones
")->fetchColumn();

/* =========================
   ASISTENCIA GENERAL
========================= */
$asistencia = $pdo->query("
    SELECT 
        COUNT(*) as total, 
        SUM(asistio) as presentes
    FROM asistencia
")->fetch(PDO::FETCH_ASSOC);

$totalRegistros = (int)($asistencia['total'] ?? 0);
$totalPresentes = (int)($asistencia['presentes'] ?? 0);

$porcentajeGeneral = $totalRegistros > 0
    ? round(($totalPresentes / $totalRegistros) * 100, 1)
    : 0;

/* =========================
   REPORTE MENSUAL
========================= */
$reporteMensual = $pdo->query("
    SELECT 
        DATE_FORMAT(r.fecha, '%Y-%m') as mes,
        SUM(a.asistio) as presentes
    FROM asistencia a
    INNER JOIN reuniones r ON a.reunion_id = r.id
    GROUP BY mes
    ORDER BY mes ASC
")->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   COMPARACIÓN POR TIPO
========================= */
$comparacion = $pdo->query("
    SELECT 
        r.tipo,
        SUM(a.asistio) as total_presentes
    FROM asistencia a
    INNER JOIN reuniones r ON a.reunion_id = r.id
    GROUP BY r.tipo
")->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   RESPUESTA FINAL
========================= */
return [
    "resumen" => [
        "totalJovenes"    => $totalJovenes,
        "activos"         => $activos,
        "inactivos"       => $inactivos,
        "servidores"      => $totalServidores,
        "reuniones"       => $totalReuniones,
        "asistencia"      => $porcentajeGeneral
    ],
    "graficas" => [
        "mensual" => $reporteMensual,
        "tipos"   => $comparacion
    ]
];