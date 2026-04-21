<?php

require_once __DIR__ . "/../middleware/permiso.php";
require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../middleware/actividad.php";

if (!tienePermiso('gestionar_usuarios')) {
    die("Acceso denegado.");
}

actualizarEstadoActividad();

/* =========================
   RESUMEN OPTIMIZADO
========================= */

$resumen = $pdo->query("
  SELECT
    COUNT(*) as total,
    SUM(estado_actividad='ACTIVO') as activos,
    SUM(estado_actividad='INACTIVO') as inactivos,
    SUM(es_servidor=1) as servidores
  FROM jovenes
")->fetch(PDO::FETCH_ASSOC);

$totalJovenes    = (int)$resumen['total'];
$activos         = (int)$resumen['activos'];
$inactivos       = (int)$resumen['inactivos'];
$totalServidores = (int)$resumen['servidores'];

/* =========================
   REUNIONES
========================= */

$totalReuniones = (int)$pdo->query("SELECT COUNT(*) FROM reuniones")->fetchColumn();

/* =========================
   ASISTENCIA
========================= */

$data = $pdo->query("
  SELECT COUNT(*) as total, SUM(asistio) as presentes
  FROM asistencia
")->fetch(PDO::FETCH_ASSOC);

$totalRegistros = (int)($data["total"] ?? 0);
$totalPresentes = (int)($data["presentes"] ?? 0);

$porcentajeGeneral = $totalRegistros > 0
    ? round(($totalPresentes / $totalRegistros) * 100, 1)
    : 0;

/* =========================
   GRÁFICAS
========================= */

$reporteMensual = $pdo->query("
  SELECT DATE_FORMAT(r.fecha, '%Y-%m') as mes,
         SUM(a.asistio) as presentes
  FROM asistencia a
  JOIN reuniones r ON a.reunion_id = r.id
  GROUP BY mes
  ORDER BY mes ASC
")->fetchAll(PDO::FETCH_ASSOC);

$comparacion = $pdo->query("
  SELECT r.tipo,
         SUM(a.asistio) as total_presentes
  FROM asistencia a
  JOIN reuniones r ON a.reunion_id = r.id
  GROUP BY r.tipo
")->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   RETORNAR DATA
========================= */

return [
  "totalJovenes" => $totalJovenes,
  "activos" => $activos,
  "inactivos" => $inactivos,
  "totalServidores" => $totalServidores,
  "totalReuniones" => $totalReuniones,
  "porcentajeGeneral" => $porcentajeGeneral,
  "reporteMensual" => $reporteMensual,
  "comparacion" => $comparacion
];