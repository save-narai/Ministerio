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

/* ✅ CSS */
$extraCSS = '
<link rel="stylesheet" href="' . BASE_URL . '/assets/css/modules/jovenes/ver.css">
';

/* ===============================
   DATOS
=============================== */
$stmt = $pdo->prepare("SELECT * FROM jovenes WHERE id = :id");
$stmt->execute(["id" => $id]);
$joven = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$joven) {
    die("Joven no encontrado.");
}

/* ===============================
   CLASE POR GÉNERO 🔥
=============================== */
$claseGenero = ($joven["genero"] === "FEMENINO") 
    ? "perfil-chica" 
    : "perfil-chico";

/* ===============================
   RESUMEN
=============================== */
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

/* ===============================
   EDAD
=============================== */
$edad = "—";
if (!empty($joven["fecha_nacimiento"])) {
    $edad = (new DateTime($joven["fecha_nacimiento"]))->diff(new DateTime())->y;
}

/* ===============================
   SEGUIMIENTOS
=============================== */
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
<!-- =========================
     PERFIL
========================= -->
<div class="perfil-card <?= $claseGenero ?>">

    <div class="perfil-header">
        <h2><?= htmlspecialchars($joven["nombre_completo"]) ?></h2>

        <?php if($joven["estado_actividad"] === "ACTIVO"): ?>
            <span class="badge-activo">
                <svg class="icon" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="6" fill="currentColor"/>
                </svg>
                ACTIVO
            </span>
        <?php else: ?>
            <span class="badge-inactivo">
                <svg class="icon" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="6" fill="currentColor"/>
                </svg>
                INACTIVO
            </span>
        <?php endif; ?>

    </div>

    <div class="perfil-grid">
        <div><strong>Edad:</strong> <?= $edad ?></div>
        <div><strong>Género:</strong> <?= htmlspecialchars($joven["genero"] ?? "—") ?></div>
        <div><strong>Teléfono:</strong> <?= htmlspecialchars($joven["telefono"] ?? "—") ?></div>
        <div><strong>Estado:</strong> <?= htmlspecialchars($joven["estado_espiritual"] ?? "—") ?></div>
    </div>

    <div class="perfil-stats">

        <!-- ✅ PRESENTES -->
        <span class="badge-presente">
            <svg class="icon" viewBox="0 0 24 24">
                <path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2" fill="none"/>
            </svg>
            <?= $presentes ?>
        </span>

        <!-- ❌ AUSENTES -->
        <span class="badge-ausente">
            <svg class="icon" viewBox="0 0 24 24">
                <path d="M6 6l12 12M6 18L18 6" stroke="currentColor" stroke-width="2" fill="none"/>
            </svg>
            <?= $ausentes ?>
        </span>

    </div>

    <div class="perfil-obs">
        <strong>Observaciones:</strong>
        <p><?= nl2br(htmlspecialchars($joven["observaciones"] ?? "—")) ?></p>
    </div>

</div>
<!-- =========================
     FORM SEGUIMIENTO
========================= -->
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

<!-- =========================
     TIMELINE 🔥
========================= -->
<div class="card">
<h3>📋 Historial de Seguimiento</h3>

<?php if(count($seguimientos) > 0): ?>

<div class="timeline">

<?php foreach($seguimientos as $s): ?>

<div class="timeline-item">

    <div class="timeline-dot"></div>

    <div class="timeline-content">

        <div class="timeline-header">
            <strong><?= htmlspecialchars($s["modalidad_contacto"]) ?></strong>

            <span class="estado <?= strtolower($s["estado_proceso"]) ?>">
                <?= htmlspecialchars($s["estado_proceso"]) ?>
            </span>
        </div>

        <!-- ✅ META PRO (SIN EMOJIS) -->
        <div class="timeline-meta">

            <span class="meta-item">
                <svg class="icon" viewBox="0 0 24 24">
                    <rect x="3" y="5" width="18" height="16" rx="2" stroke="currentColor" fill="none"/>
                    <path d="M3 10h18" stroke="currentColor"/>
                </svg>
                <?= htmlspecialchars($s["fecha_contacto"]) ?>
            </span>

            <span class="meta-item">
                <svg class="icon" viewBox="0 0 24 24">
                    <circle cx="12" cy="8" r="4" stroke="currentColor" fill="none"/>
                    <path d="M4 20c2-4 6-6 8-6s6 2 8 6" stroke="currentColor" fill="none"/>
                </svg>
                <?= htmlspecialchars($s["responsable_nombre"] ?? "—") ?>
            </span>

        </div>

        <div class="timeline-body">
            <?= nl2br(htmlspecialchars($s["observaciones"] ?? "Sin observaciones")) ?>
        </div>

    </div>

</div>

<?php endforeach; ?>

</div>

<?php else: ?>
<p class="text-center">No hay seguimientos registrados.</p>
<?php endif; ?>

</div>

<a href="<?= BASE_URL ?>/views/jovenes/index.php" class="btn"> Volver</a>

<a href="<?= BASE_URL ?>/views/jovenes/perfil_pdf.php?id=<?= $joven['id'] ?>"
   target="_blank"
   class="btn-primary">
📄 Descargar Perfil en PDF
</a>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>