<?php
require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/permiso.php";
require_once __DIR__ . "/../../config/conexion.php";

if (!tienePermiso('gestionar_jovenes')) {
    header("Location: ../dashboard.php");
    exit();
}

$id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

if ($id <= 0) {
    header("Location: index.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM jovenes WHERE id = :id");
$stmt->execute(["id" => $id]);
$joven = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$joven) {
    header("Location: index.php");
    exit();
}
?>

<?php require_once __DIR__ . "/../../includes/header.php"; ?>

<h2>✏️ Editar Joven</h2>

<div class="card form-card">

<form action="<?= BASE_URL ?>/controllers/jovenController.php" method="POST">

<input type="hidden" name="id" value="<?= (int)$joven["id"] ?>">

<label>Nombre Completo</label>
<input type="text"
       name="nombre_completo"
       value="<?= htmlspecialchars($joven["nombre_completo"] ?? "") ?>"
       required>

<label>Teléfono</label>
<input type="text"
       name="telefono"
       value="<?= htmlspecialchars($joven["telefono"] ?? "") ?>">

<label>Fecha de Nacimiento</label>
<input type="date"
       name="fecha_nacimiento"
       value="<?= htmlspecialchars($joven["fecha_nacimiento"] ?? "") ?>">

<label>Fecha de Ingreso</label>
<input type="date"
       name="fecha_ingreso"
       value="<?= htmlspecialchars($joven["fecha_ingreso"] ?? "") ?>">

<label>Género</label>
<select name="genero">
    <option value="">Seleccionar</option>
    <option value="MASCULINO" <?= ($joven["genero"] ?? "") === "MASCULINO" ? "selected" : "" ?>>
        Masculino
    </option>
    <option value="FEMENINO" <?= ($joven["genero"] ?? "") === "FEMENINO" ? "selected" : "" ?>>
        Femenino
    </option>
</select>

<label>Estado Espiritual</label>
<select name="estado_espiritual">
    <option value="NUEVO" <?= ($joven["estado_espiritual"] ?? "") === "NUEVO" ? "selected" : "" ?>>
        Nuevo
    </option>
    <option value="ANTIGUO" <?= ($joven["estado_espiritual"] ?? "") === "ANTIGUO" ? "selected" : "" ?>>
        Antiguo
    </option>
</select>

<label>Observaciones</label>
<textarea name="observaciones"><?= htmlspecialchars($joven["observaciones"] ?? "") ?></textarea>

<button type="submit" name="editar_joven" class="btn-report">
    💾 Guardar Cambios
</button>

</form>

<br>
<a href="<?= BASE_URL ?>/views/jovenes/index.php" class="btn-report">⬅ Volver</a>

</div>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>