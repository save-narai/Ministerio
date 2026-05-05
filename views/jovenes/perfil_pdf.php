<?php
require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/permiso.php";
require_once __DIR__ . "/../../config/conexion.php";
require_once __DIR__ . "/../../vendor/autoload.php";

use Dompdf\Dompdf;
use Dompdf\Options;

if (!tienePermiso('gestionar_jovenes')) {
    die("Acceso denegado");
}

/* =========================
   VALIDAR ID
========================= */
if (!isset($_GET["id"])) {
    die("ID no especificado");
}

$id = intval($_GET["id"]);

/* =========================
   CONSULTAR JOVEN
========================= */
$stmt = $pdo->prepare("
    SELECT
        nombre_completo,
        documento,
        TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) AS edad,
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
    die("Joven no encontrado");
}

/* =========================
   CONSULTAR SEGUIMIENTOS
========================= */
$stmt = $pdo->prepare("
    SELECT
        s.modalidad_contacto,
        s.estado_proceso,
        s.observaciones,
        s.fecha_contacto,
        u.nombre AS responsable
    FROM seguimientos s
    LEFT JOIN usuarios u ON s.responsable_id = u.id
    WHERE s.joven_id = ?
    ORDER BY s.fecha_contacto DESC
");
$stmt->execute([$id]);
$seguimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   RESUMEN
========================= */
$totalSeguimientos = count($seguimientos);
$totalFinalizados = 0;
$totalEnProceso = 0;

foreach ($seguimientos as $s) {
    if ($s["estado_proceso"] === "FINALIZADO") {
        $totalFinalizados++;
    } elseif ($s["estado_proceso"] === "EN_PROCESO") {
        $totalEnProceso++;
    }
}

/* =========================
   FUNCIONES
========================= */
function e($text) {
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}

function fecha($f) {
    return !empty($f) ? date("d/m/Y", strtotime($f)) : "-";
}

/* =========================
   COLOR POR GÉNERO 🔥
========================= */
$colorHeader = ($joven["genero"] === "chico")
    ? "#dc2626"   // rojo
    : "#9333ea";  // morado

/* =========================
   HTML PDF
========================= */
$html = '
<style>
body{
    font-family: Arial, sans-serif;
    font-size: 12px;
    color: #111;
}

h2{
    text-align:center;
    color: '.$colorHeader.';
}

h3{
    margin-top:20px;
    border-bottom:2px solid '.$colorHeader.';
    padding-bottom:5px;
}

.table{
    width:100%;
    border-collapse: collapse;
}

.table th{
    background: '.$colorHeader.';
    color: #fff;
}

.table td, .table th{
    border:1px solid #ccc;
    padding:6px;
    text-align:left;
}
</style>

<h2>Perfil Individual del Joven</h2>

<h3>Datos Personales</h3>
<p><strong>Nombre:</strong> '.e($joven["nombre_completo"]).'</p>
<p><strong>Documento:</strong> '.e($joven["documento"]).'</p>
<p><strong>Edad:</strong> '.e($joven["edad"]).'</p>
<p><strong>Teléfono:</strong> '.e($joven["telefono"]).'</p>
<p><strong>Dirección:</strong> '.e($joven["direccion"]).'</p>
<p><strong>Género:</strong> '.e($joven["genero"]).'</p>
<p><strong>Estado:</strong> '.e($joven["estado_actividad"]).'</p>
<p><strong>Fecha de Ingreso:</strong> '.fecha($joven["fecha_ingreso"]).'</p>

<h3>Resumen</h3>
<p><strong>Total:</strong> '.$totalSeguimientos.'</p>
<p><strong>Finalizados:</strong> '.$totalFinalizados.'</p>
<p><strong>En Proceso:</strong> '.$totalEnProceso.'</p>

<h3>Historial de Seguimientos</h3>

<table class="table">
<tr>
<th>Fecha</th>
<th>Modalidad</th>
<th>Estado</th>
<th>Responsable</th>
<th>Observaciones</th>
</tr>';

/* =========================
   FILAS
========================= */
foreach ($seguimientos as $s) {

    $color = "#111";
    if ($s["estado_proceso"] == "FINALIZADO") $color = "green";
    elseif ($s["estado_proceso"] == "EN_PROCESO") $color = "orange";
    elseif ($s["estado_proceso"] == "PENDIENTE") $color = "red";

    $html .= '
    <tr>
        <td>'.fecha($s["fecha_contacto"]).'</td>
        <td>'.e($s["modalidad_contacto"]).'</td>
        <td style="color:'.$color.';"><strong>'.e($s["estado_proceso"]).'</strong></td>
        <td>'.e($s["responsable"]).'</td>
        <td>'.e($s["observaciones"]).'</td>
    </tr>';
}

$html .= '</table>';

/* =========================
   GENERAR PDF
========================= */
$options = new Options();
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$nombreArchivo = preg_replace('/[^A-Za-z0-9_\-]/', '_', $joven["nombre_completo"]);

$dompdf->stream("Perfil_" . $nombreArchivo . ".pdf", ["Attachment" => true]);
exit;