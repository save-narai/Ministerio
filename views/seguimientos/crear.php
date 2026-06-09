
<?php

require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/permiso.php";
require_once __DIR__ . "/../../middleware/actividad.php";
require_once __DIR__ . "/../../config/conexion.php";

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

$jovenes = $pdo->query("
    SELECT

        id,
        nombre_completo

    FROM jovenes

    WHERE estado_actividad != 'ELIMINADO'

    ORDER BY nombre_completo ASC
")->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   RESPONSABLES
========================= */

$responsables = $pdo->query("
    SELECT

        id,
        nombre

    FROM usuarios

    ORDER BY nombre ASC
")->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   USUARIO ACTUAL
========================= */

$usuarioActual =
    (int)($_SESSION["user_id"] ?? 0);

/* =========================
   CSS
========================= */

$extraCSS = '
<link rel="stylesheet" href="' . BASE_URL . '/assets/css/modules/seguimientos/registrar.css">
';

/* =========================
   HEADER
========================= */

require_once __DIR__ . "/../../includes/header.php";

?>

<div class="form-page">

    <div class="form-card">

        <!-- =========================
             HEADER
        ========================== -->

        <div class="form-header">

            <div>

                <h2>
                    Crear Seguimiento
                </h2>

                <p>
                    Registrar contacto y consolidación
                </p>

            </div>

        </div>

        <!-- =========================
             FORM
        ========================== -->

        <form
            action="../../controllers/seguimientoController.php"
            method="POST"
            class="modern-form"
        >

            <div class="form-grid">

                <!-- JOVEN -->

                <div class="form-group">

                    <label>
                        Joven
                    </label>

                    <select
                        name="joven_id"
                        required
                    >

                        <option value="">
                            Seleccionar joven
                        </option>

                        <?php foreach($jovenes as $j): ?>

                        <option
                            value="<?= $j["id"] ?>"

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

                    <label>
                        Fecha de contacto
                    </label>

                    <input
                        type="date"
                        name="fecha_contacto"
                        value="<?= date('Y-m-d') ?>"
                        required
                    >

                </div>

                <!-- MODALIDAD -->

                <div class="form-group">

                    <label>
                        Modalidad
                    </label>

                    <select
                        name="modalidad_contacto"
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

                    <label>
                        Estado
                    </label>

                    <select
                        name="estado_proceso"
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

                    <label>
                        Responsable
                    </label>

                    <select
                        name="responsable_id"
                    >

                        <option value="">
                            Seleccionar responsable
                        </option>

                        <?php foreach($responsables as $r): ?>

                        <option
                            value="<?= $r["id"] ?>"

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

            <div class="form-group">

                <label>
                    Observaciones
                </label>

                <textarea
                    name="observaciones"
                    rows="5"
                    placeholder="Escribe observaciones del seguimiento..."
                ></textarea>

            </div>

            <!-- BOTONES -->

            <div class="form-actions">

                <a
                    href="index.php"
                    class="btn btn-secondary"
                >

                    Volver

                </a>

                <button
                    type="submit"
                    name="crear_seguimiento"
                    class="btn btn-primary"
                >

                    Guardar seguimiento

                </button>

            </div>

        </form>

    </div>

</div>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>