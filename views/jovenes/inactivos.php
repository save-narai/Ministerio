<?php
require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/permiso.php";
require_once __DIR__ . "/../../middleware/actividad.php";
require_once __DIR__ . "/../../config/conexion.php";

if (!tienePermiso('gestionar_jovenes')) {
    header("Location: ../dashboard.php");
    exit;
}

// actualizar actividad
actualizarEstadoActividad();

/* =========================
   OBTENER INACTIVOS
========================= */
$stmt = $pdo->prepare("
    SELECT nombre_completo, ultima_actividad
    FROM jovenes
    WHERE estado_actividad = 'INACTIVO'
    ORDER BY nombre_completo ASC
");
$stmt->execute();
$inactivos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php require_once __DIR__ . "/../../includes/header.php"; ?>

<h2>⚠ Jóvenes Inactivos</h2>

<p class="text-center text-muted">
Más de 2 meses sin actividad
</p>

<div class="card">

<?php if (count($inactivos) > 0): ?>

<table id="tablaInactivos" class="tabla display">
<thead>
<tr>
    <th>Nombre</th>
    <th>Última Actividad</th>
</tr>
</thead>

<tbody>
<?php foreach ($inactivos as $j): ?>
<tr>
    <td><?= htmlspecialchars($j["nombre_completo"]) ?></td>
    <td>
        <?= !empty($j["ultima_actividad"])
            ? htmlspecialchars($j["ultima_actividad"])
            : "<span class='badge'>Nunca</span>" ?>
    </td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<?php else: ?>

<p class="text-center">✅ Todo al día, no hay inactivos</p>

<?php endif; ?>

</div>

<br>

<a href="<?= BASE_URL ?>/views/jovenes/index.php" class="btn-report">⬅ Volver</a>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>