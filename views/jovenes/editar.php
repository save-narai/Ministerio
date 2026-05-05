<?php
require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/permiso.php";
require_once __DIR__ . "/../../config/conexion.php";

if (!tienePermiso('gestionar_jovenes')) {
    header("Location: ../dashboard.php");
    exit();
}

$id = isset($_GET["id"]) ? (int)$id = $_GET["id"] : 0;

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

/* ✅ CSS EXTRA */
$extraCSS = '
<link rel="stylesheet" href="' . BASE_URL . '/assets/css/modules/jovenes/editar.css">
';

require_once __DIR__ . "/../../includes/header.php";
?>

<div class="card form-card">

    <h2>✏️ Editar Joven</h2>

    <form action="<?= BASE_URL ?>/controllers/jovenController.php" method="POST">

        <input type="hidden" name="id" value="<?= (int)$joven["id"] ?>">

        <!-- 🔥 FILA 1 -->
        <div class="form-row">
            <div class="form-group">
                <label>Nombre Completo</label>
                <input type="text" name="nombre_completo"
                    value="<?= htmlspecialchars($joven["nombre_completo"] ?? "") ?>" required>
            </div>

            <div class="form-group">
                <label>Teléfono</label>
                <input type="text" name="telefono"
                    value="<?= htmlspecialchars($joven["telefono"] ?? "") ?>">
            </div>
        </div>

        <!-- 🔥 FILA 2 -->
        <div class="form-row">
            <div class="form-group">
                <label>Fecha de Nacimiento</label>
                <input type="date" name="fecha_nacimiento"
                    value="<?= htmlspecialchars($joven["fecha_nacimiento"] ?? "") ?>">
            </div>

            <div class="form-group">
                <label>Fecha de Ingreso</label>
                <input type="date" name="fecha_ingreso"
                    value="<?= htmlspecialchars($joven["fecha_ingreso"] ?? "") ?>">
            </div>
        </div>

        <!-- 🔥 FILA 3 -->
        <div class="form-row">
            <div class="form-group">
                <label>Género</label>
                <select name="genero">
                    <option value="">Seleccionar</option>
                    <option value="MASCULINO" <?= ($joven["genero"] ?? "") === "MASCULINO" ? "selected" : "" ?>>Masculino</option>
                    <option value="FEMENINO" <?= ($joven["genero"] ?? "") === "FEMENINO" ? "selected" : "" ?>>Femenino</option>
                </select>
            </div>

            <div class="form-group">
                <label>Estado Espiritual</label>
                <select name="estado_espiritual">
                    <option value="NUEVO" <?= ($joven["estado_espiritual"] ?? "") === "NUEVO" ? "selected" : "" ?>>Nuevo</option>
                    <option value="ANTIGUO" <?= ($joven["estado_espiritual"] ?? "") === "ANTIGUO" ? "selected" : "" ?>>Antiguo</option>
                </select>
            </div>
        </div>

        <!-- 🔥 OBSERVACIONES -->
        <div class="form-group">
            <label>Observaciones</label>
            <textarea name="observaciones"><?= htmlspecialchars($joven["observaciones"] ?? "") ?></textarea>
        </div>

        <button type="submit" name="editar_joven">
            💾 Guardar Cambios
        </button>

    </form>

    <button type="button" class="btn-volver"
        onclick="window.location.href='<?= BASE_URL ?>/views/jovenes/index.php'">
         Volver
    </button>

</div>


<script>
function showToast(message){

    const toast = document.getElementById("toast");

    toast.textContent = message;
    toast.classList.remove("hidden");

    setTimeout(() => {
        toast.classList.add("show");
    }, 50);

    setTimeout(() => {
        toast.classList.remove("show");

        setTimeout(() => {
            toast.classList.add("hidden");
        }, 300);

    }, 3000);
}
</script>



<?php require_once __DIR__ . "/../../includes/footer.php"; ?>