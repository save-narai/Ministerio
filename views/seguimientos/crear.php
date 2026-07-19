<?php

require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/permiso.php";
require_once __DIR__ . "/../../middleware/actividad.php";
require_once __DIR__ . "/../../config/conexion.php";
require_once __DIR__ . "/../../helpers/csrf.php";

/* =========================
   CSRF
========================= */

generarCsrf();

/* =========================
   PERMISOS
========================= */

if (!tienePermiso('gestionar_seguimientos')) {

    header("Location: ../dashboard.php");
    exit;
}

/* =========================
   ACTIVIDAD
========================= */

actualizarEstadoActividad();

/* =========================
   JOVEN PRESELECCIONADO
========================= */

$jovenSeleccionado =
    (int)($_GET["id"] ?? 0);

/* =========================
   JÓVENES ACTIVOS
========================= */

$stmt = $pdo->prepare("
    SELECT
        id,
        nombre_completo
    FROM jovenes
    WHERE estado_actividad = 'ACTIVO'
    ORDER BY nombre_completo ASC
");

$stmt->execute();

$jovenes = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   RESPONSABLES
========================= */

$stmt = $pdo->prepare("
    SELECT
        id,
        nombre
    FROM usuarios
    ORDER BY nombre ASC
");

$stmt->execute();

$responsables = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   USUARIO ACTUAL
========================= */

$usuarioActual =
    (int)($_SESSION["user_id"] ?? 0);

/* =========================
   HEADER
========================= */

require_once __DIR__ . "/../../includes/header.php";

?>

<div class="form-card">

    <!-- =====================================
         HEADER
    ====================================== -->

    <div class="form-header">

        <div class="form-header-icon">

            <i class="fa-solid fa-handshake"></i>

        </div>

        <div class="form-header-content">

            <h1 class="form-title">
                Crear Seguimiento
            </h1>

            <p class="form-subtitle">
                Registra y documenta el acompañamiento realizado a un joven.
            </p>

        </div>

    </div>

    <!-- =====================================
         INFORMACIÓN
    ====================================== -->

    <div class="form-info">

        <i class="fa-solid fa-circle-info"></i>

        Cada seguimiento registra el acompañamiento realizado, los acuerdos alcanzados y el estado actual del proceso del joven.

    </div>

    <!-- =====================================
         FORMULARIO
    ====================================== -->

    <form
        action="<?= BASE_URL ?>/controllers/seguimientoController.php"
        method="POST"
        class="form"
    >

        <input
            type="hidden"
            name="csrf_token"
            value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"
        >

        <div class="form-grid">

            <!-- JOVEN -->

            <div class="form-group">

                <label class="form-label">

                    <i class="fa-solid fa-user"></i>

                    Joven

                </label>

                <select
                    class="form-select"
                    name="joven_id"
                    autocomplete="off"
                    required
                >

                    <option value="">
                        Seleccionar joven
                    </option>

                    <?php foreach($jovenes as $j): ?>

                        <option
                            value="<?= (int)$j["id"] ?>"
                            <?= $jovenSeleccionado === (int)$j["id"]
                                ? 'selected'
                                : '' ?>
                        >

                            <?= htmlspecialchars(
                                $j["nombre_completo"]
                            ) ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>

            <!-- FECHA -->

            <div class="form-group">

                <label class="form-label">

                    <i class="fa-solid fa-calendar-days"></i>

                    Fecha de contacto

                </label>

                <input
                    class="form-input"
                    type="date"
                    name="fecha_contacto"
                    value="<?= htmlspecialchars(date('Y-m-d')) ?>"
                    required
                >

            </div>

            <!-- MODALIDAD -->

            <div class="form-group">

                <label class="form-label">

                    <i class="fa-brands fa-whatsapp"></i>

                    Modalidad

                </label>

                <select
                    class="form-select"
                    name="modalidad_contacto"
                    autocomplete="off"
                    required
                >

                    <option value="WHATSAPP">
                        WhatsApp
                    </option>

                    <option value="LLAMADA">
                        Llamada
                    </option>

                    <option value="VISITA">
                        Visita
                    </option>

                </select>

            </div>

            <!-- ESTADO -->

            <div class="form-group">

                <label class="form-label">

                    <i class="fa-solid fa-list-check"></i>

                    Estado

                </label>

                <select
                    class="form-select"
                    name="estado_proceso"
                    autocomplete="off"
                    required
                >

                    <option value="PENDIENTE">
                        Pendiente
                    </option>

                    <option value="EN_PROCESO">
                        En proceso
                    </option>

                    <option value="FINALIZADO">
                        Finalizado
                    </option>

                </select>

            </div>

            <!-- RESPONSABLE -->

            <div class="form-group">

                <label class="form-label">

                    <i class="fa-solid fa-user-tie"></i>

                    Responsable

                </label>

                <select
                    class="form-select"
                    name="responsable_id"
                    autocomplete="off"
                >

                    <option value="">
                        Seleccionar responsable
                    </option>

                    <?php foreach($responsables as $r): ?>

                        <option
                            value="<?= (int)$r["id"] ?>"
                            <?= $usuarioActual === (int)$r["id"]
                                ? 'selected'
                                : '' ?>
                        >

                            <?= htmlspecialchars(
                                $r["nombre"]
                            ) ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>

        </div>

        <!-- OBSERVACIONES -->

        <div class="form-group form-group-full">

            <label class="form-label">

                <i class="fa-solid fa-comment-dots"></i>

                Observaciones

            </label>

            <textarea
                class="form-textarea"
                name="observaciones"
                rows="6"
                maxlength="2000"
                placeholder="Describe la conversación, acuerdos, necesidades detectadas o compromisos establecidos..."
            ></textarea>

        </div>

        <!-- BOTONES -->

        <div class="form-actions">

            <a
                href="index.php"
                class="btn btn-back"
            >

                <i class="fa-solid fa-arrow-left"></i>

                Volver

            </a>

            <button
                type="submit"
                name="crear_seguimiento"
                class="btn btn-primary"
            >

                <i class="fa-solid fa-floppy-disk"></i>

                Guardar Seguimiento

            </button>

        </div>

    </form>

</div>

<script
    src="<?= BASE_URL ?>/assets/js/modulos/seguimientos/crear.js">
</script>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>