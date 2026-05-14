<?php
require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/permiso.php";

if (!tienePermiso('gestionar_jovenes')) {
    header("Location: ../dashboard.php");
    exit;
}

$extraCSS = '
<link rel="stylesheet" href="' . BASE_URL . '/assets/css/modules/jovenes/crear.css">
';

require_once __DIR__ . "/../../includes/header.php";
?>

<div class="card card-form">

    <!-- TOAST -->
    <div id="toast" class="toast hidden"></div>

    <h2>Crear Joven</h2>

    <?php if(isset($_SESSION["error"])): ?>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            showToast("<?= $_SESSION["error"]; ?>");
        });
    </script>
    <?php unset($_SESSION["error"]); endif; ?>

    <form action="<?= BASE_URL ?>/controllers/jovenController.php" method="POST">

        <!-- FILA 1 -->
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

        <!-- FILA 2 -->
        <div class="form-row">
            <div class="form-group">
                <label>Teléfono:</label>

<input 
    type="tel"
    name="telefono"
    id="telefono"
    placeholder="3001234567"
    maxlength="10"
    pattern="^3[0-9]{9}$"
    title="Debe ser un número colombiano válido"
>

<small id="telefonoError" class="telefono-error"></small>

                <div class="check-wrapper">
                    <label class="check-custom">
                        <input type="checkbox" name="sinTelefono" id="sinTelefono">
                        <span class="checkmark"></span>
                        <span>No tiene teléfono</span>
                    </label>
                </div>
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

        <!-- FILA 3 -->
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
                <input type="date"
                       name="fecha_ingreso"
                       value="<?= date('Y-m-d') ?>"
                       required>
            </div>
        </div>

        <!-- FILA 4 -->
        <div class="form-group">
            <label>¿Es Servidor?</label>
            <select name="es_servidor">
                <option value="0">No</option>
                <option value="1">Sí</option>
            </select>
        </div>

        <!-- BOTÓN -->
        <button type="submit" name="crear_joven">
            Guardar
        </button>

    </form>

    <button type="button" class="btn-volver"
        onclick="window.location.href='<?= BASE_URL ?>/views/jovenes/index.php'">
        Volver
    </button>

</div>

<!-- TOAST -->
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

        // SOLO 10 DIGITOS
        if (!/^3\d{9}$/.test(numero)) {
            return false;
        }

        // TODOS IGUALES
        if (/^(\d)\1+$/.test(numero)) {
            return false;
        }

        // SECUENCIAS REPETIDAS
        if (
            numero === "1234567890" ||
            numero === "0123456789" ||
            numero === "1231231231" ||
            numero === "1212121212"
        ) {
            return false;
        }

        return true;
    }

    /* =========================
       INPUT
    ========================= */

    inputTel.addEventListener("input", () => {

        // SOLO NUMEROS
        inputTel.value = inputTel.value.replace(/\D/g, '');

        const numero = inputTel.value.trim();

        if (numero !== "") {

            check.checked = false;

            inputTel.disabled = false;
        }

        // LIMPIAR MENSAJE
        error.textContent = "";

        inputTel.classList.remove("input-error");
        inputTel.classList.remove("input-success");

        // VALIDACION
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