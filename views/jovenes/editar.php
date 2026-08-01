<?php

require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/permiso.php";
require_once __DIR__ . "/../../config/conexion.php";
require_once __DIR__ . "/../../helpers/csrf.php";

/* =====================================
   CSRF
===================================== */

generarCsrf();

/* =====================================
   PERMISOS
===================================== */

if (!tienePermiso('gestionar_jovenes')) {

    header("Location: ../dashboard.php");
    exit();

}

/* =====================================
   ID DEL JOVEN
===================================== */

$id = (int) ($_GET["id"] ?? 0);

if ($id <= 0) {

    header("Location: index.php");
    exit();

}

/* =====================================
   OBTENER JOVEN
===================================== */

$stmt = $pdo->prepare("
    SELECT
        id,
        nombre_completo,
        fecha_nacimiento,
        edad_manual,
        telefono,
        fecha_ingreso,
        genero,
        estado_espiritual,
        es_servidor,
        observaciones
    FROM jovenes
    WHERE id = :id
    LIMIT 1
");

$stmt->execute([
    ":id" => $id
]);

$joven = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$joven) {

    header("Location: index.php");
    exit();

}

/* =====================================
   HEADER
===================================== */

require_once __DIR__ . "/../../includes/header.php";

?>

<div class="form-card">

    <div id="toast" class="toast"></div>

    <!-- =====================================
         HEADER
    ====================================== -->

    <div class="form-header">

        <div class="form-header-icon">

            <i class="fa-solid fa-user-pen"></i>

        </div>

        <div class="form-header-content">

            <h1 class="form-title">

                Editar Joven

            </h1>

            <p class="form-subtitle">

                Actualiza la información del joven dentro del sistema.

            </p>

        </div>

    </div>

    <!-- =====================================
         INFORMACIÓN
    ====================================== -->

    <div class="form-info">

        <i class="fa-solid fa-circle-info"></i>

        <span>

            Actualiza la información del joven. Los cambios se reflejarán inmediatamente en el sistema.

        </span>

    </div>

    <!-- =====================================
         FORMULARIO
    ====================================== -->

   <form

    class="form"

    action="<?= BASE_URL ?>/controllers/jovenController.php"

    method="POST"

    autocomplete="off"

>

    <input
        type="hidden"
        name="csrf_token"
        value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"
    >

    <input
        type="hidden"
        name="action"
        value="editar_joven"
    >

    <input
        type="hidden"
        name="id"
        value="<?= (int) $joven['id'] ?>"
    >

        <div class="form-grid">

            <!-- NOMBRE -->

            <div class="form-group form-group-full">

                <label class="form-label">

                    Nombre Completo

                </label>

                <input

                    class="form-input"

                    type="text"

                    name="nombre_completo"

                    maxlength="120"

                    autocomplete="off"

                    required

                    value="<?= htmlspecialchars($joven['nombre_completo']) ?>"

                >

            </div>

            <!-- FECHA DE NACIMIENTO -->

            <div class="form-group">

                <label class="form-label">

                    Fecha de nacimiento

                </label>

                <input

                    class="form-input"

                    type="date"

                    name="fecha_nacimiento"

                    id="fecha"

                    value="<?= htmlspecialchars($joven['fecha_nacimiento'] ?? '') ?>"

                >

            </div>

            <!-- EDAD -->

            <div class="form-group">

                <label class="form-label">

                    Edad

                </label>

                <input

                    class="form-input"

                    type="number"

                    name="edad_manual"

                    id="edad"

                    min="1"

                    max="120"

                    value="<?= htmlspecialchars($joven['edad_manual'] ?? '') ?>"

                >

            </div>

            <!-- TELÉFONO -->

            <div class="form-group">

                <label class="form-label">

                    Teléfono

                </label>

                <input

                    class="form-input"

                    type="tel"

                    name="telefono"

                    id="telefono"

                    maxlength="10"

                    inputmode="numeric"

                    autocomplete="off"

                    value="<?= htmlspecialchars($joven['telefono'] ?? '') ?>"

                    <?= empty($joven['telefono']) ? 'disabled' : '' ?>

                >

                <small

                    id="telefonoError"

                    class="telefono-error"

                ></small>

                <div class="check-wrapper">

                    <label class="check-custom">

                        <input

                            type="checkbox"

                            id="sinTelefono"

                            name="sinTelefono"

                            <?= empty($joven['telefono']) ? 'checked' : '' ?>

                        >

                        <span class="checkmark"></span>

                        <span>

                            No tiene teléfono

                        </span>

                    </label>

                </div>

            </div>

            <!-- FECHA DE INGRESO -->

            <div class="form-group">

                <label class="form-label">

                    Fecha de ingreso

                </label>

                <input

                    class="form-input"

                    type="date"

                    name="fecha_ingreso"

                    required

                    value="<?= htmlspecialchars($joven['fecha_ingreso']) ?>"

                >

            </div>

            <!-- GÉNERO -->

            <div class="form-group">

                <label class="form-label">

                    Género

                </label>

                <select

                    class="form-select"

                    name="genero"

                    required

                >

                    <option value="">

                        Seleccionar

                    </option>

                    <option

                        value="M"

                        <?= ($joven['genero'] ?? '') === 'M' ? 'selected' : '' ?>

                    >

                        Masculino

                    </option>

                    <option

                        value="F"

                        <?= ($joven['genero'] ?? '') === 'F' ? 'selected' : '' ?>

                    >

                        Femenino

                    </option>

                </select>

            </div>

            <!-- ESTADO ESPIRITUAL -->

            <div class="form-group">

                <label class="form-label">

                    Estado espiritual

                </label>

                <select

                    class="form-select"

                    name="estado_espiritual"

                    required

                >

                    <option value="">

                        Seleccionar

                    </option>

                    <?php

                    $estados = [

                        "NUEVO",
                        "CONGREGANTE",
                        "DISCIPULADO",
                        "SERVIDOR",
                        "LIDER"

                    ];

                    foreach ($estados as $estado):

                    ?>

                        <option

                            value="<?= $estado ?>"

                            <?= ($joven['estado_espiritual'] ?? '') === $estado ? 'selected' : '' ?>

                        >

                            <?= htmlspecialchars($estado) ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>

            <!-- SERVIDOR -->

            <div class="form-group form-group-full">

                <label class="form-label">

                    ¿Es servidor?

                </label>

                <select

                    class="form-select"

                    name="es_servidor"

                >

                    <option

                        value="0"

                        <?= (int) ($joven['es_servidor'] ?? 0) === 0 ? 'selected' : '' ?>

                    >

                        No

                    </option>

                    <option

                        value="1"

                        <?= (int) ($joven['es_servidor'] ?? 0) === 1 ? 'selected' : '' ?>

                    >

                        Sí

                    </option>

                </select>

            </div>

            <!-- OBSERVACIONES -->

            <div class="form-group form-group-full">

                <label class="form-label">

                    Observaciones

                </label>

                <textarea

                    class="form-textarea"

                    name="observaciones"

                    rows="6"

                    maxlength="2000"

                    placeholder="Agrega observaciones relevantes sobre el joven..."

                ><?= htmlspecialchars($joven['observaciones'] ?? '') ?></textarea>

            </div>

        </div>

        <!-- BOTONES -->

        <div class="form-actions">

            <a

                href="<?= BASE_URL ?>/views/jovenes/index.php"

                class="btn btn-back"

            >

            

                Volver

            </a>

            <button

                type="submit"

                name="editar_joven"

                class="btn btn-primary"

            >

                

                Guardar cambios

            </button>

        </div>

    </form>

</div>

<!-- =====================================
     TOAST
===================================== -->

<script>

window.toastMessage = <?= json_encode(
    $_SESSION["success"]
    ?? $_SESSION["error"]
    ?? ""
) ?>;

window.toastType = <?= json_encode(
    isset($_SESSION["success"])
        ? "success"
        : (
            isset($_SESSION["error"])
                ? "error"
                : ""
        )
) ?>;

</script>

<!-- =====================================
     JAVASCRIPT
===================================== -->

<script src="<?= BASE_URL ?>/assets/js/modulos/jovenes/editar.js"></script>

<?php

/* =====================================
   LIMPIAR MENSAJES
===================================== */

unset($_SESSION["success"]);
unset($_SESSION["error"]);

/* =====================================
   FOOTER
===================================== */

require_once __DIR__ . "/../../includes/footer.php";

?>