<?php
require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/permiso.php";

if (!tienePermiso('gestionar_jovenes')) {
    header("Location: ../dashboard.php");
    exit;
}

/* ✅ CSS EXTRA */
$extraCSS = '
<link rel="stylesheet" href="' . BASE_URL . '/assets/css/modules/jovenes/crear.css">
';

require_once __DIR__ . "/../../includes/header.php";
?>

<div class="card card-form">
<div id="toast" class="toast hidden"></div>

    <h2>➕ Crear Joven</h2>

    <div id="toast" class="toast hidden"></div>

<?php if(isset($_SESSION["error"])): ?>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        showToast("<?= $_SESSION["error"]; ?>");
    });
</script>
<?php unset($_SESSION["error"]); endif; ?>

    <form action="<?= BASE_URL ?>/controllers/jovenController.php" method="POST">

        <!-- 🔥 FILA 1 -->
        <div class="form-row">
            <div class="form-group">
                <label>Nombre Completo:</label>
                <input type="text" name="nombre_completo" required>
            </div>

            <div class="form-group">
                <label>Fecha de Nacimiento:</label>
                <input type="date" name="fecha_nacimiento" required>
            </div>
        </div>

        <!-- 🔥 FILA 2 -->
        <div class="form-row">
            <div class="form-group">
                <label>Teléfono:</label>
                <input type="text" name="telefono" placeholder="3001234567" maxlength="10">
            </div>

            <div class="form-group">
                <label>Género:</label>
                <select name="genero">
                    <option value="">Seleccionar</option>
                    <option value="Masculino">Masculino</option>
                    <option value="Femenino">Femenino</option>
                </select>
            </div>
        </div>

        <!-- 🔥 FILA 3 -->
        <div class="form-row">
            <div class="form-group">
                <label>Estado Espiritual:</label>
                <select name="estado_espiritual">
                    <option value="">Seleccionar</option>
                    <option value="Nuevo">Nuevo</option>
                    <option value="Antiguo">Antiguo</option>
                </select>
            </div>

            <div class="form-group">
                <label>Fecha de Ingreso:</label>
                <input type="date" name="fecha_ingreso" value="<?= date('Y-m-d') ?>" required>
            </div>
        </div>

        <!-- 🔥 FILA 4 -->
        <div class="form-group">
            <label>¿Es Servidor?</label>
            <select name="es_servidor">
                <option value="0">No</option>
                <option value="1">Sí</option>
            </select>
        </div>

        <button type="submit" name="crear_joven">
            Guardar
        </button>

    </form>

  <button type="button" class="btn-volver"
    onclick="window.location.href='<?= BASE_URL ?>/views/jovenes/index.php'">
     Volver
</button>


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