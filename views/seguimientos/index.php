<?php
require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/permiso.php";
require_once __DIR__ . "/../../config/conexion.php";

if (!tienePermiso('gestionar_seguimientos')) {
    header("Location: ../dashboard.php");
    exit;
}

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
   JÓVENES ACTIVOS
========================= */

$stmt = $pdo->query("
    SELECT id, nombre_completo
    FROM jovenes
    WHERE estado_actividad = 'ACTIVO'
");
$jovenes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalActivos = count($jovenes);

/* =========================
   SEGUIMIENTOS DEL MES
========================= */

$stmt = $pdo->prepare("
    SELECT 
        j.id AS joven_id,
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
$seguimientosMes = $stmt->fetchAll(PDO::FETCH_ASSOC);
$totalRegistros = count($seguimientosMes);

$totalFinalizados = 0;
$totalEnProceso = 0;

foreach ($seguimientosMes as $s) {
    if ($s["estado_proceso"] === "FINALIZADO") {
        $totalFinalizados++;
    } elseif ($s["estado_proceso"] === "EN_PROCESO") {
        $totalEnProceso++;
    }
}
/* =========================
   ESTADÍSTICAS
========================= */

$jovenesConSeguimiento = array_unique(array_column($seguimientosMes, 'joven_id'));

$totalConSeguimiento = count($jovenesConSeguimiento);
$totalSinSeguimiento = $totalActivos - $totalConSeguimiento;

$porcentaje = $totalActivos > 0
    ? round(($totalConSeguimiento / $totalActivos) * 100)
    : 0;

$color = "red";
if ($porcentaje >= 90) $color = "green";
elseif ($porcentaje >= 70) $color = "orange";
?>


<?php include("../../includes/header.php"); ?>


<h2>📋 Consolidado de Seguimientos</h2>
<p>Mes: <strong><?= $mesTexto ?></strong></p>

<a href="reporte_pdf.php" target="_blank" 
style="background:#28a745;color:white;padding:10px 15px;border-radius:5px;text-decoration:none;">
📄 Descargar PDF
</a>

<div class="grid">

<div class="card">
<p>Total Activos</p>
<p class="stat"><?= $totalActivos ?></p>
</div>

<div class="card">
<p>Con Seguimiento</p>
<p class="stat ok"><?= $totalConSeguimiento ?></p>
</div>

<div class="card">
<p>Sin Seguimiento</p>
<p class="stat bad"><?= $totalSinSeguimiento ?></p>
</div>

<div class="card" style="background:<?= $color ?>; color:white;">
<p>Cumplimiento</p>
<p class="stat"><?= $porcentaje ?>%</p>
</div>

</div>

<hr>

<h3>Detalle por Joven</h3>

<table>
<tr>
<th>Nombre</th>
<th>Edad</th>
<th>Teléfono</th>
<th>Género</th>
<th>Modalidad</th>
<th>Estado</th>
<th>Responsable</th>
<th>Observaciones</th>
<th>Fecha</th>
</tr>




<?php foreach($seguimientosMes as $s): ?>
<tr>
<td><?= htmlspecialchars($s["nombre_completo"]) ?></td>
<td><?= $s["edad"] ?></td>
<td><?= htmlspecialchars($s["telefono"] ?? "-") ?></td>
<td><?= htmlspecialchars($s["genero"] ?? "-") ?></td>
<td><?= htmlspecialchars($s["modalidad_contacto"]) ?></td>

<td>
<?php
$estado = $s["estado_proceso"];
if ($estado === "FINALIZADO") {
    echo "<span class='ok'>FINALIZADO</span>";
} elseif ($estado === "EN_PROCESO") {
    echo "<span style='color:orange;font-weight:bold;'>EN PROCESO</span>";
} else {
    echo "<span class='bad'>PENDIENTE</span>";
}
?>
</td>

<td><?= htmlspecialchars($s["responsable_nombre"] ?? "-") ?></td>
<td><?= htmlspecialchars($s["observaciones"] ?? "-") ?></td>
<td><?= date("d/m/Y", strtotime($s["fecha_contacto"])) ?></td>
</tr>
<?php endforeach; ?>

</table>

<br>
<a href="../dashboard.php">⬅ Volver al Dashboard</a>

<?php include("../../includes/footer.php"); ?>