<?php

require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/permiso.php";
require_once __DIR__ . "/../../config/conexion.php";
require_once __DIR__ . "/../../vendor/autoload.php";

use Dompdf\Dompdf;
use Dompdf\Options;

/* =========================
   PERMISOS
========================= */

if (!tienePermiso('gestionar_jovenes')) {

    $_SESSION["error"] = "Acceso denegado";

    header("Location: ../dashboard.php");

    exit;
}

/* =========================
   VALIDAR ID
========================= */

$id = isset($_GET["id"])
    ? (int) $_GET["id"]
    : 0;

if ($id <= 0) {

    $_SESSION["error"] = "Joven inválido";

    header("Location: ../jovenes/index.php");

    exit;
}

/* =========================
   CONSULTAR JOVEN
========================= */

$stmt = $pdo->prepare("
    SELECT

        nombre_completo,

        documento,

        TIMESTAMPDIFF(
            YEAR,
            fecha_nacimiento,
            CURDATE()
        ) AS edad,

        telefono,

        direccion,

        genero,

        estado_actividad,

        fecha_ingreso

    FROM jovenes

    WHERE id = ?
");

$stmt->execute([$id]);

$joven = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$joven) {

    $_SESSION["error"] = "Joven no encontrado";

    header("Location: ../jovenes/index.php");

    exit;
}

/* =========================
   SEGUIMIENTOS
========================= */

$stmt = $pdo->prepare("
    SELECT

        s.modalidad_contacto,

        s.estado_proceso,

        s.observaciones,

        s.fecha_contacto,

        u.nombre AS responsable

    FROM seguimientos s

    LEFT JOIN usuarios u
        ON s.responsable_id = u.id

    WHERE s.joven_id = ?

    ORDER BY s.fecha_contacto DESC
");

$stmt->execute([$id]);

$seguimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   HISTORIAL ASISTENCIA
========================= */

$stmt = $pdo->prepare("
    SELECT

        r.tipo,

        r.fecha,

        a.asistio

    FROM asistencia a

    INNER JOIN reuniones r
        ON a.reunion_id = r.id

    WHERE a.joven_id = ?

    ORDER BY r.fecha DESC
");

$stmt->execute([$id]);

$asistencias = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   RESUMEN SEGUIMIENTO
========================= */

$totalSeguimientos = count($seguimientos);

$totalFinalizados = 0;

$totalEnProceso = 0;

$totalPendientes = 0;

foreach ($seguimientos as $s) {

    if ($s["estado_proceso"] === "FINALIZADO") {

        $totalFinalizados++;

    } elseif ($s["estado_proceso"] === "EN_PROCESO") {

        $totalEnProceso++;

    } elseif ($s["estado_proceso"] === "PENDIENTE") {

        $totalPendientes++;
    }
}

/* =========================
   RESUMEN ASISTENCIA
========================= */

$totalAsistencias = count($asistencias);

$totalPresentes = count(
    array_filter(
        $asistencias,
        fn($a) => (int)$a["asistio"] === 1
    )
);

$totalAusencias = $totalAsistencias - $totalPresentes;

$porcentajeAsistencia = $totalAsistencias > 0
    ? round(($totalPresentes / $totalAsistencias) * 100)
    : 0;

/* =========================
   ESTADO CONEXION
========================= */

$estadoConexion = "🟢 Conectado";

if ($totalAusencias >= 5) {

    $estadoConexion = "🔴 Alto Riesgo";

} elseif ($totalAusencias >= 3) {

    $estadoConexion = "🟡 Riesgo";
}

/* =========================
   HELPERS
========================= */

function e($text) {

    return htmlspecialchars(
        $text ?? '',
        ENT_QUOTES,
        'UTF-8'
    );
}

function fecha($f) {

    return !empty($f)
        ? date("d/m/Y", strtotime($f))
        : "-";
}

/* =========================
   COLOR HEADER
========================= */

$genero = strtoupper($joven["genero"] ?? '');

$colorHeader = $genero === "MASCULINO"
    ? "#dc2626"
    : "#9333ea";

/* =========================
   HTML
========================= */

$html = '

<style>

body{
    font-family: Arial, sans-serif;
    font-size: 12px;
    color:#111;
    padding:20px;
}

.header{
    text-align:center;
    margin-bottom:25px;
}

.header h1{
    color: '.$colorHeader.';
    margin-bottom:5px;
}

.estado{
    font-size:13px;
    font-weight:bold;
}

.section{
    margin-top:25px;
}

.section-title{
    font-size:16px;
    font-weight:bold;
    color: '.$colorHeader.';
    border-bottom:2px solid '.$colorHeader.';
    padding-bottom:5px;
    margin-bottom:12px;
}

.info-grid{
    width:100%;
}

.info-grid td{
    padding:6px 0;
}

.stats{
    width:100%;
    margin-top:10px;
}

.stats td{
    width:25%;
    padding:12px;
    text-align:center;
    border-radius:8px;
    color:#fff;
    font-weight:bold;
}

.bg-blue{
    background:#2563eb;
}

.bg-green{
    background:#16a34a;
}

.bg-yellow{
    background:#d97706;
}

.bg-red{
    background:#dc2626;
}

.table{
    width:100%;
    border-collapse:collapse;
    margin-top:10px;
}

.table th{
    background: '.$colorHeader.';
    color:#fff;
}

.table td,
.table th{
    border:1px solid #d1d5db;
    padding:8px;
    text-align:left;
    font-size:11px;
}

.badge{
    padding:4px 8px;
    border-radius:6px;
    font-weight:bold;
    color:#fff;
    display:inline-block;
}

.badge-green{
    background:#16a34a;
}

.badge-yellow{
    background:#d97706;
}

.badge-red{
    background:#dc2626;
}

.empty{
    padding:18px;
    background:#f3f4f6;
    border-radius:10px;
    text-align:center;
    color:#555;
    margin-top:10px;
}

.alerta{
    margin-top:15px;
    padding:12px;
    background:#fee2e2;
    border:1px solid #fecaca;
    color:#991b1b;
    border-radius:8px;
    font-weight:bold;
}

</style>

<div class="header">

    <h1>
        Perfil Integral del Joven
    </h1>

    <div class="estado">
        '.$estadoConexion.'
    </div>

</div>

<div class="section">

    <div class="section-title">
        Datos Personales
    </div>

    <table class="info-grid">

        <tr>
            <td><strong>Nombre:</strong></td>
            <td>'.e($joven["nombre_completo"]).'</td>
        </tr>

        <tr>
            <td><strong>Documento:</strong></td>
            <td>'.e($joven["documento"]).'</td>
        </tr>

        <tr>
            <td><strong>Edad:</strong></td>
            <td>'.e($joven["edad"]).' años</td>
        </tr>

        <tr>
            <td><strong>Teléfono:</strong></td>
            <td>'.e($joven["telefono"]).'</td>
        </tr>

        <tr>
            <td><strong>Dirección:</strong></td>
            <td>'.e($joven["direccion"]).'</td>
        </tr>

        <tr>
            <td><strong>Género:</strong></td>
            <td>'.e($joven["genero"]).'</td>
        </tr>

        <tr>
            <td><strong>Estado:</strong></td>
            <td>'.e($joven["estado_actividad"]).'</td>
        </tr>

        <tr>
            <td><strong>Ingreso:</strong></td>
            <td>'.fecha($joven["fecha_ingreso"]).'</td>
        </tr>

    </table>

</div>

<div class="section">

    <div class="section-title">
        Resumen General
    </div>

    <table class="stats">

        <tr>

            <td class="bg-blue">
                Seguimientos<br>
                '.$totalSeguimientos.'
            </td>

            <td class="bg-green">
                Finalizados<br>
                '.$totalFinalizados.'
            </td>

            <td class="bg-yellow">
                En Proceso<br>
                '.$totalEnProceso.'
            </td>

            <td class="bg-red">
                Ausencias<br>
                '.$totalAusencias.'
            </td>

        </tr>

    </table>

</div>';

if ($totalAusencias >= 3) {

    $html .= '

    <div class="alerta">
        🚨 Este joven necesita seguimiento por inasistencias frecuentes
    </div>';
}

/* =========================
   SEGUIMIENTOS
========================= */

$html .= '

<div class="section">

    <div class="section-title">
        Historial de Seguimientos
    </div>';

if (count($seguimientos) > 0) {

    $html .= '

    <table class="table">

        <tr>

            <th>Fecha</th>

            <th>Modalidad</th>

            <th>Estado</th>

            <th>Responsable</th>

            <th>Observaciones</th>

        </tr>';

    foreach ($seguimientos as $s) {

        $badge = "badge-red";

        if ($s["estado_proceso"] === "FINALIZADO") {

            $badge = "badge-green";

        } elseif ($s["estado_proceso"] === "EN_PROCESO") {

            $badge = "badge-yellow";
        }

        $html .= '

        <tr>

            <td>'.fecha($s["fecha_contacto"]).'</td>

            <td>'.e($s["modalidad_contacto"]).'</td>

            <td>
                <span class="badge '.$badge.'">
                    '.e($s["estado_proceso"]).'
                </span>
            </td>

            <td>'.e($s["responsable"]).'</td>

            <td>'.e($s["observaciones"]).'</td>

        </tr>';
    }

    $html .= '</table>';

} else {

    $html .= '

    <div class="empty">
        📭 No hay seguimientos registrados
    </div>';
}

/* =========================
   ASISTENCIAS
========================= */

$html .= '

</div>

<div class="section">

    <div class="section-title">
        Historial de Asistencia
    </div>';

if (count($asistencias) > 0) {

    $html .= '

    <table class="table">

        <tr>

            <th>Fecha</th>

            <th>Reunión</th>

            <th>Estado</th>

        </tr>';

    foreach ($asistencias as $a) {

        $badge = $a["asistio"]
            ? "badge-green"
            : "badge-red";

        $texto = $a["asistio"]
            ? "Presente"
            : "Ausente";

        $html .= '

        <tr>

            <td>'.fecha($a["fecha"]).'</td>

            <td>'.e($a["tipo"]).'</td>

            <td>
                <span class="badge '.$badge.'">
                    '.$texto.'
                </span>
            </td>

        </tr>';
    }

    $html .= '</table>';

} else {

    $html .= '

    <div class="empty">
        📭 No hay asistencias registradas
    </div>';
}

$html .= '</div>';

/* =========================
   PDF
========================= */

$options = new Options();

$dompdf = new Dompdf($options);

$dompdf->loadHtml($html);

$dompdf->setPaper('A4', 'portrait');

$dompdf->render();

$nombreArchivo = preg_replace(
    '/[^A-Za-z0-9_\-]/',
    '_',
    $joven["nombre_completo"]
);

$fechaActual = date("Y-m-d");

$dompdf->stream(

    "Perfil_" .
    $nombreArchivo .
    "_" .
    $fechaActual .
    ".pdf",

    [
        "Attachment" => true
    ]
);

exit;