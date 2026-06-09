
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

$stmt = $pdo->prepare("
    SELECT *
    FROM jovenes
    WHERE id = :id
");

$stmt->execute(["id" => $id]);

$joven = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$joven) {
    header("Location: index.php");
    exit();
}

$extraCSS = '
<link rel="stylesheet" href="' . BASE_URL . '/assets/css/modules/jovenes/editar.css">
';

require_once __DIR__ . "/../../includes/header.php";
?>

<div class="card form-card">

    <div id="toast" class="toast"></div>

    <h2>Editar Joven</h2>

    <?php if(isset($_SESSION["error"])): ?>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            showToast(
                <?= json_encode($_SESSION["error"]); ?>,
                "error"
            );
        });
    </script>
    <?php unset($_SESSION["error"]); endif; ?>

    <?php if(isset($_SESSION["success"])): ?>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            showToast(
                <?= json_encode($_SESSION["success"]); ?>,
                "success"
            );
        });
    </script>
    <?php unset($_SESSION["success"]); endif; ?>

    <form
        action="<?= BASE_URL ?>/controllers/jovenController.php"
        method="POST">

        <input
            type="hidden"
            name="csrf_token"
            value="<?= $_SESSION['csrf_token'] ?>">

        <input
            type="hidden"
            name="id"
            value="<?= (int)$joven["id"] ?>">

        <!-- NOMBRE -->
        <div class="form-group">
            <label>Nombre Completo</label>

            <input
                type="text"
                name="nombre_completo"
                required
                value="<?= htmlspecialchars($joven["nombre_completo"]) ?>">
        </div>

        <!-- FECHA / EDAD -->
        <div class="form-row">

            <div class="form-group">
                <label>Fecha nacimiento</label>

                <input
                    type="date"
                    name="fecha_nacimiento"
                    id="fecha"
                    value="<?= $joven["fecha_nacimiento"] ?>">
            </div>

            <div class="form-group">
                <label>Edad</label>

                <input
                    type="number"
                    name="edad_manual"
                    id="edad"
                    min="1"
                    max="100"
                    value="<?= $joven["edad_manual"] ?>">
            </div>

        </div>

        <!-- TELEFONO / INGRESO -->
        <div class="form-row">

            <div class="form-group">

                <label>Teléfono</label>

                <input
                    type="tel"
                    name="telefono"
                    id="telefono"
                    maxlength="10"
                    value="<?= htmlspecialchars($joven["telefono"] ?? "") ?>"
                    <?= empty($joven["telefono"]) ? 'disabled' : '' ?>>

                <small
                    id="telefonoError"
                    class="telefono-error">
                </small>

                <div class="check-wrapper">

                    <label class="check-custom">

                        <input
                            type="checkbox"
                            name="sinTelefono"
                            id="sinTelefono"
                            <?= empty($joven["telefono"]) ? 'checked' : '' ?>>

                        <span class="checkmark"></span>

                        <span>No tiene teléfono</span>

                    </label>

                </div>

            </div>

            <div class="form-group">

                <label>Fecha Ingreso</label>

                <input
                    type="date"
                    name="fecha_ingreso"
                    required
                    value="<?= $joven["fecha_ingreso"] ?>">

            </div>

        </div>

        <!-- GENERO / ESTADO -->
        <div class="form-row">

            <div class="form-group">

                <label>Género</label>

                <select name="genero">

                    <option value="">Seleccionar</option>

                    <option
                        value="M"
                        <?= $joven["genero"] === "M" ? "selected" : "" ?>>
                        Masculino
                    </option>

                    <option
                        value="F"
                        <?= $joven["genero"] === "F" ? "selected" : "" ?>>
                        Femenino
                    </option>

                </select>

            </div>

            <div class="form-group">

                <label>Estado Espiritual</label>

                <select name="estado_espiritual">

                    <option value="">Seleccionar</option>

                    <?php
                    $estados = [
                        "NUEVO",
                        "CONGREGANTE",
                        "DISCIPULADO",
                        "SERVIDOR",
                        "LIDER"
                    ];

                    foreach($estados as $estado):
                    ?>

                    <option
                        value="<?= $estado ?>"
                        <?= $joven["estado_espiritual"] === $estado ? "selected" : "" ?>>

                        <?= $estado ?>

                    </option>

                    <?php endforeach; ?>

                </select>

            </div>

        </div>

        <!-- SERVIDOR -->
        <div class="form-group">

            <label>¿Es servidor?</label>

            <select name="es_servidor">

                <option
                    value="0"
                    <?= $joven["es_servidor"] == 0 ? "selected" : "" ?>>

                    No

                </option>

                <option
                    value="1"
                    <?= $joven["es_servidor"] == 1 ? "selected" : "" ?>>

                    Sí

                </option>

            </select>

        </div>

        <!-- OBSERVACIONES -->
        <div class="form-group full-width">

            <label>Observaciones</label>

            <textarea name="observaciones"><?= htmlspecialchars($joven["observaciones"] ?? "") ?></textarea>

        </div>

        <!-- BOTONES -->
        <div class="btn-group">

            <button
                type="submit"
                name="editar_joven"
                class="btn btn-primary">

                Guardar cambios

            </button>

            <a
                href="<?= BASE_URL ?>/views/jovenes/index.php"
                class="btn btn-secondary">

                Volver

            </a>

        </div>

    </form>

</div>

<script>

function showToast(msg, type="error") {

    const toast = document.getElementById("toast");

    toast.textContent = msg;

    toast.className = "toast show " + type;

    setTimeout(() => {
        toast.classList.remove("show");
    }, 3000);
}

document.addEventListener("DOMContentLoaded", () => {

    const fecha = document.getElementById("fecha");
    const edad = document.getElementById("edad");
    const tel = document.getElementById("telefono");
    const check = document.getElementById("sinTelefono");
    const error = document.getElementById("telefonoError");

    /* =========================
       FECHA / EDAD
    ========================= */

    function sync() {

        edad.disabled = !!fecha.value;
        fecha.disabled = !!edad.value;
    }

    fecha.addEventListener("change", sync);
    edad.addEventListener("input", sync);

    sync();

    /* =========================
       VALIDAR TEL
    ========================= */

    function telefonoValido(n) {

        return /^3\d{9}$/.test(n)
            && !/^(\d)\1+$/.test(n);
    }

    tel.addEventListener("input", () => {

        tel.value = tel.value.replace(/\D/g,'');

        tel.classList.remove(
            "input-error",
            "input-success"
        );

        if (tel.value.length === 10) {

            if (telefonoValido(tel.value)) {

                error.textContent = "Número válido";

                error.className =
                    "telefono-error success";

                tel.classList.add("input-success");

            } else {

                error.textContent = "Número inválido";

                error.className =
                    "telefono-error error";

                tel.classList.add("input-error");
            }

        } else {

            error.textContent = "";

            tel.classList.remove(
                "input-error",
                "input-success"
            );
        }
    });

    /* =========================
       CHECK TELEFONO
    ========================= */

    check.addEventListener("change", () => {

        tel.disabled = check.checked;

        if (check.checked) {

            tel.value = "";

            error.textContent = "";

            tel.classList.remove(
                "input-error",
                "input-success"
            );
        }
    });

});
</script>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>

