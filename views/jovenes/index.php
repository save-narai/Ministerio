<?php
require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/permiso.php";
require_once __DIR__ . "/../../middleware/actividad.php";
require_once __DIR__ . "/../../config/conexion.php";

if (!tienePermiso('gestionar_jovenes')) {
    header("Location: ../dashboard.php");
    exit;
}

actualizarEstadoActividad();

/* FILTRO */
$permitidos = ["todos", "activos", "inactivos", "riesgo2", "riesgo3"];
$filtro = $_GET["filtro"] ?? "todos";

if (!in_array($filtro, $permitidos)) {
    $filtro = "todos";
}

/* QUERY */
$query = "
    SELECT
        j.id,
        j.nombre_completo,
        j.fecha_nacimiento,
        j.estado_espiritual,
        j.estado_actividad,
        j.fecha_ingreso,
        TIMESTAMPDIFF(YEAR, j.fecha_nacimiento, CURDATE()) AS edad,
        COALESCE(SUM(CASE WHEN a.asistio = 0 THEN 1 ELSE 0 END),0) AS faltas
    FROM jovenes j
    LEFT JOIN asistencia a ON j.id = a.joven_id
    GROUP BY j.id
";

$having = [];

if ($filtro === "activos") $having[] = "j.estado_actividad = 'ACTIVO'";
if ($filtro === "inactivos") $having[] = "j.estado_actividad = 'INACTIVO'";
if ($filtro === "riesgo2") $having[] = "faltas = 2";
if ($filtro === "riesgo3") $having[] = "faltas >= 3";

if (!empty($having)) {
    $query .= " HAVING " . implode(" AND ", $having);
}

$query .= " ORDER BY j.nombre_completo ASC";

$stmt = $pdo->query($query);
$jovenes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php require_once __DIR__ . "/../../includes/header.php"; ?>

<h2>👤 Gestión de Jóvenes</h2>

<div class="top-actions">
    <a href="<?= BASE_URL ?>/views/jovenes/crear.php" class="btn-primary">➕ Nuevo</a>
    <a href="<?= BASE_URL ?>/views/jovenes/reporte_jovenes_pdf.php" target="_blank" class="btn-secondary">📄 PDF</a>
</div>

<hr>

<h3>Filtros</h3>

<div class="filtros">
    <a href="?filtro=todos" class="filtro-btn">👥 Todos</a>
    <a href="?filtro=activos" class="filtro-btn">🟢 Activos</a>
    <a href="?filtro=inactivos" class="filtro-btn">🔴 Inactivos</a>
    <a href="?filtro=riesgo2" class="filtro-btn">🟡 Riesgo</a>
    <a href="?filtro=riesgo3" class="filtro-btn">🚨 Alto</a>
</div>

<input type="text" id="buscador" placeholder="🔍 Buscar joven..." class="buscador">

<hr>

<div class="card-static tabla-container">

<table id="tablaJovenes" class="tabla display">
<thead>
<tr>
<th>Nombre</th>
<th>Edad</th>
<th>Estado Espiritual</th>
<th>Actividad</th>
<th>Conexión</th>
<th>Tiempo Iglesia</th>
<th>Acciones</th>
</tr>
</thead>

<tbody>
<?php foreach($jovenes as $j): ?>

<?php
$faltas = (int)$j["faltas"];

$meses = 0;
if (!empty($j["fecha_ingreso"])) {
    $inicio = new DateTime($j["fecha_ingreso"]);
    $hoy = new DateTime();

    if ($inicio <= $hoy) {
        $diff = $hoy->diff($inicio);
        $meses = ($diff->y * 12) + $diff->m;
    }
}
?>

<tr class="<?= $j["estado_actividad"] === "ACTIVO" ? 'activo-row' : 'inactivo-row' ?>">

<td><?= htmlspecialchars($j["nombre_completo"]) ?></td>
<td><?= htmlspecialchars($j["edad"] ?? "-") ?></td>
<td><?= htmlspecialchars($j["estado_espiritual"] ?? "-") ?></td>

<td>
<?= $j["estado_actividad"] === "INACTIVO"
    ? "<span class='inactivo'>🔴</span>"
    : "<span class='activo'>🟢</span>" ?>
</td>

<td>
<?php
if ($faltas >= 3) {
    echo "<span class='riesgo3'>🔴 $faltas</span>";
} elseif ($faltas == 2) {
    echo "<span class='riesgo2'>🟡 2</span>";
} else {
    echo "✔️";
}
?>
</td>

<td><?= $meses ?> meses</td>

<td class="acciones">

<a href="<?= BASE_URL ?>/views/jovenes/ver.php?id=<?= (int)$j["id"] ?>" class="btn-icon ver">👁</a>

<a href="<?= BASE_URL ?>/views/jovenes/editar.php?id=<?= (int)$j["id"] ?>" class="btn-icon editar">✏️</a>

<?php if(tienePermiso('eliminar_jovenes')): ?>
<form action="<?= BASE_URL ?>/controllers/jovenController.php"
      method="POST"
      class="inline-form"
      onsubmit="return confirm('¿Eliminar este joven?');">

    <input type="hidden" name="id" value="<?= (int)$j["id"] ?>">

    <button type="submit" name="eliminar_joven" class="btn-icon eliminar">
        🗑️
    </button>

</form>
<?php endif; ?>

</td>

</tr>

<?php endforeach; ?>
</tbody>
</table>

</div>

<br>

<a href="<?= BASE_URL ?>/views/dashboard.php" class="btn-back">⬅ Volver</a>

<script>
document.addEventListener("DOMContentLoaded", function () {

    let tabla;

    if (typeof $ !== "undefined") {
        tabla = $('#tablaJovenes').DataTable({
            pageLength: 8
        });
    }

    const input = document.getElementById("buscador");

    if(input && tabla){
        input.addEventListener("keyup", function(){
            tabla.search(this.value).draw();
        });
    }

});
</script>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>