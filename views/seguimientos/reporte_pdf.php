<?php
require_once __DIR__ . "/../../config/conexion.php";
require_once __DIR__ . "/../../vendor/autoload.php";

use Dompdf\Dompdf;
use Dompdf\Options;

/* =========================
   MES ACTUAL
========================= */

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

$mesTexto = $meses[$mesNumero] . ' ' . $anio;

/* =========================
   CONSULTA
========================= */

$stmt = $pdo->prepare("
    SELECT 
        j.nombre_completo,
        TIMESTAMPDIFF(YEAR, j.fecha_nacimiento, CURDATE()) AS edad,
        j.telefono,
        j.genero,
        s.modalidad_contacto,
        s.estado_proceso,
        s.observaciones,
        u.nombre AS responsable_nombre,
        s.fecha_contacto
    FROM seguimientos s
    INNER JOIN jovenes j ON s.joven_id = j.id
    LEFT JOIN usuarios u ON s.responsable_id = u.id
    WHERE MONTH(s.fecha_contacto) = MONTH(CURDATE())
    AND YEAR(s.fecha_contacto) = YEAR(CURDATE())
    ORDER BY j.nombre_completo ASC, s.fecha_contacto DESC
");

$stmt->execute();
$seguimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   HTML DEL PDF
========================= */

$html = '
<h2 style="text-align:center;">Consolidado de Seguimientos</h2>
<p><strong>Mes:</strong> '.$mesTexto.'</p>

<table width="100%" border="1" cellspacing="0" cellpadding="5">
<tr style="background:#007bff;color:white;">
<th>Nombre</th>
<th>Edad</th>
<th>Teléfono</th>
<th>Género</th>
<th>Modalidad</th>
<th>Estado</th>
<th>Responsable</th>
<th>Observaciones</th>
<th>Fecha</th>
</tr>';

foreach($seguimientos as $s){

    $estadoColor = "black";

    if($s["estado_proceso"] == "FINALIZADO"){
        $estadoColor = "green";
    } elseif($s["estado_proceso"] == "EN_PROCESO"){
        $estadoColor = "orange";
    } else {
        $estadoColor = "red";
    }

    $html .= '
    <tr>
    <td>'.$s["nombre_completo"].'</td>
    <td>'.$s["edad"].'</td>
    <td>'.$s["telefono"].'</td>
    <td>'.$s["genero"].'</td>
    <td>'.$s["modalidad_contacto"].'</td>
    <td style="color:'.$estadoColor.';"><strong>'.$s["estado_proceso"].'</strong></td>
    <td>'.$s["responsable_nombre"].'</td>
    <td>'.$s["observaciones"].'</td>
    <td>'.date("d/m/Y", strtotime($s["fecha_contacto"])).'</td>
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
$dompdf->setPaper('A4', 'landscape'); // Horizontal
$dompdf->render();
$dompdf->stream("Consolidado_".$mesTexto.".pdf", ["Attachment" => true]);
exit;