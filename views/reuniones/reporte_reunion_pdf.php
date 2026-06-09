
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

if (!tienePermiso('gestionar_reuniones')) {

    header("Location: ../dashboard.php");

    exit;
}

/* =========================
   VALIDAR ID
========================= */

if (!isset($_GET["id"])) {

    die("ID inválido");
}

$reunion_id = (int)$_GET["id"];

/* =========================
   REUNIÓN
========================= */

$stmt = $pdo->prepare("
    SELECT *
    FROM reuniones
    WHERE id = ?
");

$stmt->execute([$reunion_id]);

$reunion = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reunion) {

    die("Reunión no encontrada");
}

/* =========================
   DATA
========================= */

$stmt = $pdo->prepare("
SELECT

    j.nombre_completo,

    j.es_servidor,

    a.asistio,

    a.grupo_edad,

    a.participa_discipulado,

    a.primera_vez_discipulado

FROM asistencia a

JOIN jovenes j
    ON j.id = a.joven_id

WHERE a.reunion_id = ?
");

$stmt->execute([$reunion_id]);

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   STATS
========================= */

$total = count($data);

$asistieron = 0;

$servidores = 0;

$servidoresAsist = 0;

$conexion = 0;

$discipulado = 0;

foreach ($data as $d) {

    if ($d["asistio"]) {

        $asistieron++;
    }

    if ($d["es_servidor"]) {

        $servidores++;

        if ($d["asistio"]) {

            $servidoresAsist++;
        }
    }

    if ($d["participa_discipulado"]) {

        $discipulado++;
    }

    if ($d["primera_vez_discipulado"]) {

        $conexion++;
    }
}

$porcentaje = $total > 0
    ? round(($asistieron / $total) * 100)
    : 0;

/* =========================
   HTML PDF
========================= */

$html = '

<style>

body{
    font-family: Arial, sans-serif;
    color:#111;
    font-size:12px;
    padding:20px;
}

.header{
    text-align:center;
    margin-bottom:25px;
}

.header h1{
    color:#2563eb;
    margin-bottom:5px;
}

.fecha{
    color:#666;
}

.stats{
    width:100%;
    margin-bottom:25px;
}

.stats td{
    width:16%;
    text-align:center;
    padding:10px;
    color:#fff;
    font-weight:bold;
    border-radius:8px;
}

.blue{background:#2563eb;}
.green{background:#16a34a;}
.orange{background:#ea580c;}
.purple{background:#9333ea;}

.table{
    width:100%;
    border-collapse:collapse;
}

.table th{
    background:#2563eb;
    color:#fff;
    padding:8px;
}

.table td{
    border:1px solid #ddd;
    padding:7px;
}

.ok{
    color:green;
    font-weight:bold;
}

.no{
    color:red;
    font-weight:bold;
}

</style>

<div class="header">

    <h1>
        Informe de Reunión
    </h1>

    <div class="fecha">

        '.date("d/m/Y", strtotime($reunion["fecha"])).'

    </div>

</div>

<table class="stats">

<tr>

<td class="blue">
Total<br>
'.$total.'
</td>

<td class="green">
Asistencia<br>
'.$asistieron.'
</td>

<td class="orange">
Porcentaje<br>
'.$porcentaje.'%
</td>

<td class="purple">
Servidores<br>
'.$servidoresAsist.'/'.$servidores.'
</td>

<td class="blue">
Discipulado<br>
'.$discipulado.'
</td>

<td class="green">
Primera vez<br>
'.$conexion.'
</td>

</tr>

</table>

<table class="table">

<thead>

<tr>

<th>Nombre</th>

<th>Servidor</th>

<th>Grupo</th>

<th>Discipulado</th>

<th>Primera vez</th>

<th>Asistencia</th>

</tr>

</thead>

<tbody>
';

/* =========================
   FILAS
========================= */

foreach ($data as $d) {

    $html .= '

    <tr>

        <td>'.$d["nombre_completo"].'</td>

        <td>'.($d["es_servidor"] ? 'Sí' : 'No').'</td>

        <td>'.($d["grupo_edad"] ?? '-').'</td>

        <td>'.($d["participa_discipulado"] ? '✔' : '-').'</td>

        <td>'.($d["primera_vez_discipulado"] ? '✔' : '-').'</td>

        <td class="'.($d["asistio"] ? 'ok' : 'no').'">

            '.($d["asistio"] ? 'Asistió' : 'Faltó').'

        </td>

    </tr>
    ';
}

$html .= '

</tbody>

</table>
';

/* =========================
   PDF
========================= */

$options = new Options();

$dompdf = new Dompdf($options);

$dompdf->loadHtml($html);

$dompdf->setPaper('A4', 'landscape');

$dompdf->render();

$dompdf->stream(

    "Informe_Reunion_".$reunion_id.".pdf",

    [
        "Attachment" => true
    ]
);

exit;
