<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;

// =========================
// DATOS
// =========================

$totalJovenes = $pdo->query("SELECT COUNT(*) FROM jovenes")->fetchColumn();

$activos = $pdo->query("
    SELECT COUNT(*) FROM jovenes
    WHERE estado_actividad = 'ACTIVO'
")->fetchColumn();

$inactivos = $pdo->query("
    SELECT COUNT(*) FROM jovenes
    WHERE estado_actividad = 'INACTIVO'
")->fetchColumn();

$reuniones = $pdo->query("SELECT COUNT(*) FROM reuniones")->fetchColumn();

// Asistencia
$data = $pdo->query("
    SELECT COUNT(*) as total,
           SUM(asistio) as presentes
    FROM asistencia
")->fetch(PDO::FETCH_ASSOC);

$totalRegistros = $data["total"] ?? 0;
$totalPresentes = $data["presentes"] ?? 0;

$porcentaje = $totalRegistros > 0
    ? round(($totalPresentes / $totalRegistros) * 100, 1)
    : 0;

// Top 5
$ranking = $pdo->query("
    SELECT j.nombre_completo,
           SUM(a.asistio) as total_presentes
    FROM asistencia a
    JOIN jovenes j ON a.joven_id = j.id
    GROUP BY j.id
    ORDER BY total_presentes DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Fecha actual
$fecha = date("d/m/Y");

// =========================
// HTML BONITO
// =========================

$html = '
<style>
body{
  font-family: Arial;
  background:#f4f6f9;
}

h1{
  text-align:center;
  background: linear-gradient(90deg,#00ffff,#ff00ff,#00ff88);
  -webkit-background-clip:text;
  color:transparent;
}

.header{
  text-align:center;
  margin-bottom:20px;
}

.fecha{
  text-align:right;
  font-size:12px;
  color:#555;
}

/* CARDS */
.card{
  border-radius:12px;
  padding:15px;
  margin:10px 0;
  color:#000;
  background: linear-gradient(135deg, #DB1A1A, #FFF6F6, #8CC7C4, #85409D);
}

/* GRID */
.grid{
  display:flex;
  flex-wrap:wrap;
  gap:10px;
}

.grid .card{
  width:48%;
}

/* TABLA */
table{
  width:100%;
  border-collapse:collapse;
  margin-top:15px;
}

th, td{
  border:1px solid #ddd;
  padding:8px;
  text-align:left;
}

th{
  background:#333;
  color:#fff;
}
</style>

<div class="header">
  <h1>Reporte General</h1>
</div>

<div class="fecha">
  Fecha: '.$fecha.'
</div>

<div class="grid">
  <div class="card"><b>Total jóvenes:</b> '.$totalJovenes.'</div>
  <div class="card"><b>Activos:</b> '.$activos.'</div>
  <div class="card"><b>Inactivos:</b> '.$inactivos.'</div>
  <div class="card"><b>Reuniones:</b> '.$reuniones.'</div>
</div>

<div class="card">
  <b>Porcentaje de asistencia:</b> '.$porcentaje.'%
</div>

<h3>Top 5 más constantes</h3>

<table>
<tr>
  <th>Nombre</th>
  <th>Asistencias</th>
</tr>';

foreach($ranking as $r){
  $html .= '
  <tr>
    <td>'.$r["nombre_completo"].'</td>
    <td>'.$r["total_presentes"].'</td>
  </tr>';
}

$html .= '
</table>
';

// =========================
// GENERAR PDF
// =========================

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$dompdf->stream("reporte_dashboard.pdf", ["Attachment" => true]);