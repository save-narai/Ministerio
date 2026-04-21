<?php
require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/permiso.php";
require_once __DIR__ . "/../../config/conexion.php";

if (!tienePermiso('gestionar_jovenes')) {
    header("Location: ../dashboard.php");
    exit;
}

$joven_id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

if ($joven_id <= 0) {
    die("Joven no válido.");
}

/* =========================
   DATOS DEL JOVEN
========================= */
$stmt = $pdo->prepare("SELECT nombre_completo FROM jovenes WHERE id = :id");
$stmt->execute(["id" => $joven_id]);
$joven = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$joven) {
    die("Joven no encontrado.");
}

/* =========================
   HISTORIAL
========================= */
$stmt = $pdo->prepare("
    SELECT r.tipo, r.fecha, a.asistio
    FROM asistencia a
    JOIN reuniones r ON a.reunion_id = r.id
    WHERE a.joven_id = :id
    ORDER BY r.fecha DESC
");
$stmt->execute(["id" => $joven_id]);
$historial = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php require_once __DIR__ . "/../../includes/header.php"; ?>

<h2>📊 Historial de <?= htmlspecialchars($joven["nombre_completo"]) ?></h2>

<div class="card">

<?php if (count($historial) > 0): ?>

<table id="tablaHistorial" class="tabla display">
<thead>
<tr>
    <th>Tipo</th>
    <th>Fecha</th>
    <th>Estado</th>
</tr>
</thead>

<tbody>
<?php foreach ($historial as $h): ?>
<tr>
    <td><?= htmlspecialchars($h["tipo"]) ?></td>
    <td><?= htmlspecialchars($h["fecha"]) ?></td>
    <td>
        <?php if($h["asistio"]): ?>
            <span class="badge-ok">✅ Presente</span>
        <?php else: ?>
            <span class="badge-no">❌ Ausente</span>
        <?php endif; ?>
    </td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<?php else: ?>

<p class="text-center">📭 No hay asistencias registradas</p>

<?php endif; ?>

</div>

<br>
<a href="<?= BASE_URL ?>/views/jovenes/index.php" class="btn-report">⬅ Volver</a>

<!-- SCRIPT SOLO PARA ESTA TABLA -->
<script>
document.addEventListener("DOMContentLoaded", function () {

    if (typeof $ !== "undefined" && $('#tablaHistorial').length) {
        $('#tablaHistorial').DataTable({
            pageLength: 8,
            order: [[1, "desc"]],
            language: {
                search: "🔍 Buscar:",
                lengthMenu: "Mostrar _MENU_",
                info: "_START_ a _END_ de _TOTAL_",
                paginate: {
                    next: "➡",
                    previous: "⬅"
                }
            }
        });
    }

});
</script>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>