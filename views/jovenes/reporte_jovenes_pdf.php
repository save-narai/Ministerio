
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
   CONSULTA
========================= */

$stmt = $pdo->prepare("
    SELECT 

        nombre_completo,

        TIMESTAMPDIFF(
            YEAR,
            fecha_nacimiento,
            CURDATE()
        ) AS edad,

        telefono,

        genero,

        estado_actividad,

        fecha_ingreso

    FROM jovenes

    WHERE estado_actividad != 'ELIMINADO'

    ORDER BY nombre_completo ASC
");

$stmt->execute();

$jovenes = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   ESTADISTICAS
========================= */

$totalJovenes = count($jovenes);

$totalActivos = count(
    array_filter(
        $jovenes,
        fn($j) => $j["estado_actividad"] === "ACTIVO"
    )
);

$totalInactivos = count(
    array_filter(
        $jovenes,
        fn($j) => $j["estado_actividad"] === "INACTIVO"
    )
);

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
   HTML
========================= */

$html = '

<style>

body{
    font-family: Arial, sans-serif;
    font-size: 11px;
    color:#111;
    padding:18px;
}

.header{
    text-align:center;
    margin-bottom:20px;
}

.header h1{
    color:#2563eb;
    margin-bottom:5px;
}

.fecha{
    color:#666;
    font-size:11px;
}

.stats{
    width:100%;
    margin:20px 0;
}

.stats td{
    width:33%;
    padding:14px;
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

.bg-red{
    background:#dc2626;
}

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
    border:1px solid #d1d5db;
    padding:7px;
}

.table tbody tr:nth-child(even){
    background:#f3f4f6;
}

.badge{
    padding:4px 8px;
    border-radius:6px;
    color:#fff;
    font-weight:bold;
    display:inline-block;
}

.badge-green{
    background:#16a34a;
}

.badge-red{
    background:#dc2626;
}

.no-phone{
    color:#777;
    font-style:italic;
}

</style>

<div class="header">

    <h1>
        Listado General de Jóvenes
    </h1>

    <div class="fecha">
        Generado el '.date("d/m/Y H:i").'
    </div>

</div>

<table class="stats">

<tr>

    <td class="bg-blue">
        👥 Total<br>
        '.$totalJovenes.'
    </td>

    <td class="bg-green">
        🟢 Activos<br>
        '.$totalActivos.'
    </td>

    <td class="bg-red">
        🔴 Inactivos<br>
        '.$totalInactivos.'
    </td>

</tr>

</table>

<table class="table">

<thead>

<tr>

<th>Nombre</th>

<th>Edad</th>

<th>Teléfono</th>

<th>Género</th>

<th>Estado</th>

<th>Ingreso</th>

</tr>

</thead>

<tbody>';

/* =========================
   FILAS
========================= */

foreach($jovenes as $j){

    $badge = $j["estado_actividad"] === "ACTIVO"
        ? "badge-green"
        : "badge-red";

    $estadoTexto = $j["estado_actividad"] === "ACTIVO"
        ? "🟢 Activo"
        : "🔴 Inactivo";

    $telefono = !empty($j["telefono"])
        ? e($j["telefono"])
        : '<span class="no-phone">No registrado</span>';

    $genero = ($j["genero"] ?? '') === "M"
        ? "Masculino"
        : "Femenino";

    $html .= '

    <tr>

        <td>'.e($j["nombre_completo"]).'</td>

        <td>'.e($j["edad"]).'</td>

        <td>'.$telefono.'</td>

        <td>'.$genero.'</td>

        <td>

            <span class="badge '.$badge.'">

                '.$estadoTexto.'

            </span>

        </td>

        <td>'.fecha($j["fecha_ingreso"]).'</td>

    </tr>';
}

$html .= '

</tbody>

</table>';

/* =========================
   PDF
========================= */

$options = new Options();

$dompdf = new Dompdf($options);

$dompdf->loadHtml($html);

$dompdf->setPaper('A4', 'landscape');

$dompdf->render();

$fechaActual = date("Y-m-d");

$dompdf->stream(

    "Listado_Jovenes_".$fechaActual.".pdf",

    [
        "Attachment" => true
    ]
);

exit;
