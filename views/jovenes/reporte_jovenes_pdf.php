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
   CONSULTA JÓVENES
========================= */

$stmt = $pdo->prepare("
    SELECT 
        nombre_completo,
        documento,
        TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) AS edad,
        telefono,
        genero,
        estado_actividad,
        fecha_ingreso
    FROM jovenes
    ORDER BY nombre_completo ASC
");

$stmt->execute();
$jovenes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalJovenes = count($jovenes);

/* =========================
   FUNCIONES SEGURAS
========================= */

function e($text) {
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}

function fecha($f) {
    return !empty($f) ? date("d/m/Y", strtotime($f)) : "-";
}

/* =========================
   HTML DEL PDF
========================= */

$html = '
<h2 style="text-align:center;">Listado General de Jóvenes</h2>
<p><strong>Total registrados:</strong> '.$totalJovenes.'</p>

<table width="100%" border="1" cellspacing="0" cellpadding="5">
<tr style="background:#007bff;color:white;">
<th>Nombre</th>
<th>Documento</th>
<th>Edad</th>
<th>Teléfono</th>
<th>Género</th>
<th>Estado</th>
<th>Fecha Ingreso</th>
</tr>';

foreach($jovenes as $j){

    $estadoColor = ($j["estado_actividad"] == "ACTIVO") ? "green" : "red";

    $html .= '
    <tr>
    <td>'.e($j["nombre_completo"]).'</td>
    <td>'.e($j["documento"]).'</td>
    <td>'.e($j["edad"]).'</td>
    <td>'.e($j["telefono"]).'</td>
    <td>'.e($j["genero"]).'</td>
    <td style="color:'.$estadoColor.';"><strong>'.e($j["estado_actividad"]).'</strong></td>
    <td>'.fecha($j["fecha_ingreso"]).'</td>
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
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

$dompdf->stream("Listado_Jovenes.pdf", ["Attachment" => true]);
exit;