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

/* CSS */
$extraCSS = '
<link rel="stylesheet" href="' . BASE_URL . '/assets/css/modules/jovenes/editar.css">
';

require_once __DIR__ . "/../../includes/header.php";
?>

<div class="card form-card">

    <h2>✏️ Editar Joven</h2>

<form action="<?= BASE_URL ?>/controllers/jovenController.php" method="POST">

    <input type="hidden" name="id" value="<?= (int)$joven["id"] ?>">

    <!-- FILA 1 -->
    <div class="form-row">

        <div class="form-group">
            <label>Nombre Completo</label>
            <input
                type="text"
                name="nombre_completo"
                value="<?= htmlspecialchars($joven["nombre_completo"] ?? "") ?>"
                required
            >
        </div>

        <div class="form-group">
            <label>Teléfono</label>

            <input
                type="tel"
                name="telefono"
                id="telefono"
                value="<?= htmlspecialchars($joven["telefono"] ?? "") ?>"
                maxlength="10"
                pattern="^3[0-9]{9}$"
                placeholder="3001234567"
                title="Debe ser un celular colombiano válido (10 dígitos y empezar por 3)"
            >

            <div class="check-wrapper">
                <label class="check-custom">

                    <input
                        type="checkbox"
                        name="sinTelefono"
                        id="sinTelefono"
                        <?= empty($joven["telefono"]) ? "checked" : "" ?>
                    >

                    <span class="checkmark"></span>
                    <span>No tiene teléfono</span>

                </label>
            </div>
        </div>

    </div>

    <!-- FILA 2 -->
    <div class="form-row">

        <div class="form-group">
            <label>Fecha de Nacimiento</label>

            <input
                type="date"
                name="fecha_nacimiento"
                value="<?= htmlspecialchars($joven["fecha_nacimiento"] ?? "") ?>"
            >
        </div>

        <div class="form-group">
            <label>Fecha de Ingreso</label>

            <input
                type="date"
                name="fecha_ingreso"
                value="<?= htmlspecialchars($joven["fecha_ingreso"] ?? "") ?>"
            >
        </div>

    </div>

    <!-- FILA 3 -->
    <div class="form-row">

        <div class="form-group">
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
        </div>

        <div class="form-group">
            <label>Estado Espiritual</label>

            <select name="estado_espiritual">
                <option value="NUEVO" <?= ($joven["estado_espiritual"] ?? "") === "NUEVO" ? "selected" : "" ?>>
                    Nuevo
                </option>

                <option value="ANTIGUO" <?= ($joven["estado_espiritual"] ?? "") === "ANTIGUO" ? "selected" : "" ?>>
                    Antiguo
                </option>
            </select>
        </div>

    </div>

    <!-- OBSERVACIONES -->
    <div class="form-group full-width">
        <label>Observaciones</label>
        <textarea name="observaciones"><?= htmlspecialchars($joven["observaciones"] ?? "") ?></textarea>
    </div>

    <!-- BOTÓN -->
    <button type="submit" name="editar_joven">
        Guardar Cambios
    </button>

</form>

<button type="button" class="btn-volver"
    onclick="window.location.href='<?= BASE_URL ?>/views/jovenes/index.php'">
    Volver
</button>

</div>

<!-- JS TELÉFONO PRO -->
<script>

document.addEventListener("DOMContentLoaded", () => {

    const inputTel = document.getElementById("telefono");

    const check = document.getElementById("sinTelefono");

    const error = document.getElementById("telefonoError");

    const form = document.querySelector("form");

    /* =========================
       VALIDAR TELEFONO
    ========================= */

    function telefonoValido(numero){

        // FORMATO
        if (!/^3\d{9}$/.test(numero)) {
            return false;
        }

        // TODOS IGUALES
        if (/^(\d)\1+$/.test(numero)) {
            return false;
        }

        // NUMEROS FALSOS
        const invalidos = [
            "1234567890",
            "0123456789",
            "1111111111",
            "2222222222",
            "3333333333",
            "4444444444",
            "5555555555",
            "6666666666",
            "7777777777",
            "8888888888",
            "9999999999",
            "0000000000",
            "1212121212",
            "1231231231"
        ];

        return !invalidos.includes(numero);
    }

    /* =========================
       ESTADO INICIAL
    ========================= */

    if (check.checked) {

        inputTel.disabled = true;
    } else {

        const numeroInicial = inputTel.value.trim();

        if (
            numeroInicial.length === 10 &&
            telefonoValido(numeroInicial)
        ) {

            error.textContent = "✔ Número válido";

            error.classList.add("success");

            inputTel.classList.add("input-success");
        }
    }

    /* =========================
       INPUT
    ========================= */

    inputTel.addEventListener("input", () => {

        inputTel.value = inputTel.value.replace(/\D/g, '');

        const numero = inputTel.value.trim();

        if (numero !== "") {

            check.checked = false;

            inputTel.disabled = false;
        }

        error.textContent = "";

        inputTel.classList.remove("input-error");
        inputTel.classList.remove("input-success");

        if (numero.length === 10) {

            if (telefonoValido(numero)) {

                error.textContent = "✔ Número válido";

                error.classList.remove("error");

                error.classList.add("success");

                inputTel.classList.add("input-success");

            } else {

                error.textContent = "❌ Número inválido";

                error.classList.remove("success");

                error.classList.add("error");

                inputTel.classList.add("input-error");
            }
        }
    });

    /* =========================
       CHECKBOX
    ========================= */

    check.addEventListener("change", () => {

        if (check.checked) {

            inputTel.value = "";

            inputTel.disabled = true;

            error.textContent = "";

            inputTel.classList.remove("input-error");
            inputTel.classList.remove("input-success");

        } else {

            inputTel.disabled = false;
        }
    });

    /* =========================
       SUBMIT
    ========================= */

    form.addEventListener("submit", (e) => {

        if (check.checked) return;

        const numero = inputTel.value.trim();

        if (!telefonoValido(numero)) {

            e.preventDefault();

            error.textContent = "❌ Ingresa un número válido";

            error.classList.remove("success");

            error.classList.add("error");

            inputTel.classList.add("input-error");

            inputTel.focus();
        }
    });

});

</script>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>