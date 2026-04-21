<?php
require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/permiso.php";
require_once __DIR__ . "/../../config/conexion.php";

if (!tienePermiso('gestionar_jovenes')) {
    die("No tienes permiso.");
}

$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

if ($id <= 0) {
    die("ID inválido.");
}

/* =============================== */
/* DATOS */
/* =============================== */

$stmt = $pdo->prepare("SELECT * FROM jovenes WHERE id = :id");
$stmt->execute(["id" => $id]);
$joven = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$joven) {
    die("Joven no encontrado.");
}

/* =============================== */
/* RESUMEN */
/* =============================== */

$stmt = $pdo->prepare("
    SELECT 
        SUM(asistio = 1) AS presentes,
        SUM(asistio = 0) AS ausentes
    FROM asistencia
    WHERE joven_id = :joven_id
");
$stmt->execute(["joven_id" => $id]);
$resumen = $stmt->fetch(PDO::FETCH_ASSOC);

$presentes = $resumen["presentes"] ?? 0;
$ausentes  = $resumen["ausentes"] ?? 0;

/* =============================== */
/* EDAD */
/* =============================== */

$edad = "—";
if (!empty($joven["fecha_nacimiento"])) {
    $edad = (new DateTime($joven["fecha_nacimiento"]))->diff(new DateTime())->y;
}

/* =============================== */
/* SEGUIMIENTOS */
/* =============================== */

$stmt = $pdo->prepare("
    SELECT s.*, u.nombre AS responsable_nombre
    FROM seguimientos s
    LEFT JOIN usuarios u ON s.responsable_id = u.id
    WHERE s.joven_id = :joven_id
    ORDER BY s.fecha_contacto DESC
");
$stmt->execute(["joven_id" => $id]);
$seguimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$responsables = $pdo->query("
    SELECT id, nombre FROM usuarios ORDER BY nombre ASC
")->fetchAll(PDO::FETCH_ASSOC);

?>

<?php require_once __DIR__ . "/../../includes/header.php"; ?>

<h2>👤 Perfil del Joven</h2>

<div class="card">

<h3>📌 Información General</h3>

<p><strong>Nombre:</strong> <?= htmlspecialchars($joven["nombre_completo"]) ?></p>
<p><strong>Edad:</strong> <?= $edad ?></p>
<p><strong>Género:</strong> <?= htmlspecialchars($joven["genero"] ?? "—") ?></p>
<p><strong>Teléfono:</strong> <?= htmlspecialchars($joven["telefono"] ?? "—") ?></p>
<p><strong>Estado espiritual:</strong> <?= htmlspecialchars($joven["estado_espiritual"] ?? "—") ?></p>

<p><strong>Estado actividad:</strong>
<?= $joven["estado_actividad"] === "INACTIVO"
    ? "<span class='badge-inactivo'>🔴 INACTIVO</span>"
    : "<span class='badge-activo'>🟢 ACTIVO</span>" ?>
</p>

<p><strong>Asistencias:</strong>
<span class="badge-presente">✅ <?= $presentes ?></span> |
<span class="badge-ausente">❌ <?= $ausentes ?></span>
</p>

<p><strong>Observaciones:</strong><br>
<?= nl2br(htmlspecialchars($joven["observaciones"] ?? "—")) ?>
</p>

</div>


<div class="card">
<h3>📞 Registrar Seguimiento</h3>

<form action="<?= BASE_URL ?>/controllers/seguimientoController.php" method="POST">

<input type="hidden" name="joven_id" value="<?= $id ?>">

<label>Fecha contacto:</label>
<input type="date" name="fecha_contacto" required>

<label>Modalidad:</label>
<select name="modalidad_contacto">
<option value="WHATSAPP">WhatsApp</option>
<option value="LLAMADA">Llamada</option>
<option value="VISITA">Visita</option>
<option value="MENSAJE">Mensaje</option>
</select>

<label>Estado:</label>
<select name="estado_proceso">
<option value="PENDIENTE">Pendiente</option>
<option value="EN_PROCESO">En Proceso</option>
<option value="FINALIZADO">Finalizado</option>
</select>

<label>Responsable:</label>
<select name="responsable_id">
<?php foreach($responsables as $r): ?>
<option value="<?= (int)$r["id"] ?>">
<?= htmlspecialchars($r["nombre"]) ?>
</option>
<?php endforeach; ?>
</select>

<label>Observaciones:</label>
<textarea name="observaciones"></textarea>

<br><br>
<button type="submit" name="crear_seguimiento">
Guardar Seguimiento
</button>

</form>
</div>


<div class="card">
<h3>📋 Historial de Seguimiento</h3>

<?php if(count($seguimientos) > 0): ?>

<table class="tabla">
<tr>
<th>Mes</th>
<th>Fecha</th>
<th>Modalidad</th>
<th>Estado</th>
<th>Responsable</th>
<th>Observaciones</th>
</tr>

<?php foreach($seguimientos as $s): ?>
<tr>
<td><?= htmlspecialchars($s["mes"]) ?></td>
<td><?= htmlspecialchars($s["fecha_contacto"]) ?></td>
<td><?= htmlspecialchars($s["modalidad_contacto"]) ?></td>
<td><?= htmlspecialchars($s["estado_proceso"]) ?></td>
<td><?= htmlspecialchars($s["responsable_nombre"]) ?></td>
<td><?= htmlspecialchars($s["observaciones"] ?? "-") ?></td>
</tr>
<?php endforeach; ?>

</table>

<?php else: ?>
<p class="text-center">No hay seguimientos registrados.</p>
<?php endif; ?>

</div>


<br>

<a href="<?= BASE_URL ?>/views/jovenes/index.php" class="btn">⬅ Volver</a>

<a href="<?= BASE_URL ?>/views/jovenes/perfil_pdf.php?id=<?= $joven['id'] ?>"
   target="_blank"
   class="btn-primary">
📄 Descargar Perfil en PDF
</a>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>