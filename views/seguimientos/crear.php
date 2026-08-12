<?php

declare(strict_types=1);

require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/permiso.php";
require_once __DIR__ . "/../../config/conexion.php";
require_once __DIR__ . "/../../services/actividadService.php";
require_once __DIR__ . "/../../services/seguimientoService.php";
require_once __DIR__ . "/../../helpers/csrf.php";

generarCsrf();

/* ==========================================================
   PERMISOS
========================================================== */

if (!tienePermiso('gestionar_seguimientos')) {

    header("Location: ../dashboard.php");

    exit;
}


/* ==========================================================
   ACTIVIDAD
========================================================== */

actualizarEstadoActividad($pdo);


/* ==========================================================
   JOVEN PRESELECCIONADO
========================================================== */

$jovenSeleccionado =
    (int)($_GET['id'] ?? 0);


/* ==========================================================
   JÓVENES ACTIVOS
========================================================== */

$stmt = $pdo->prepare("
    SELECT
        id,
        nombre_completo
    FROM jovenes
    WHERE estado_actividad = 'ACTIVO'
    ORDER BY nombre_completo ASC
");

$stmt->execute();

$jovenes =
    $stmt->fetchAll(PDO::FETCH_ASSOC);


/* ==========================================================
   RESPONSABLES
========================================================== */

$stmt = $pdo->prepare("
    SELECT
        id,
        nombre
    FROM usuarios
    ORDER BY nombre ASC
");

$stmt->execute();

$responsables =
    $stmt->fetchAll(PDO::FETCH_ASSOC);


/* ==========================================================
   USUARIO ACTUAL
========================================================== */

$usuarioActual =
    (int)($_SESSION['user_id'] ?? 0);


/* ==========================================================
   HEADER
========================================================== */

require_once __DIR__ . "/../../includes/header.php";

?>

<div class="form-card">

    <!-- =====================================================
         HEADER
    ====================================================== -->

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


    <!-- =====================================================
         INFORMACIÓN
    ====================================================== -->

    <div class="form-info">

        <i class="fa-solid fa-circle-info"></i>

        Cada seguimiento registra el acompañamiento realizado,
        los acuerdos alcanzados y el estado actual del proceso
        del joven.

    </div>


    <!-- =====================================================
         FORMULARIO
    ====================================================== -->

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

    <input
        type="hidden"
        name="action"
        value="crear_seguimiento"
    >

    <!-- resto del formulario -->


        <div class="form-grid">


            <!-- =================================================
                 JOVEN
            ================================================== -->

            <div class="form-group">

                <label
                    class="form-label"
                    for="joven_id"
                >

                    <i class="fa-solid fa-user"></i>

                    Joven

                </label>


                <select
                    id="joven_id"
                    class="form-select"
                    name="joven_id"
                    autocomplete="off"
                    required
                >

                    <option value="">
                        Seleccionar joven
                    </option>


                    <?php foreach ($jovenes as $joven): ?>

                        <?php
                        $jovenId =
                            (int)$joven['id'];
                        ?>

                        <option
                            value="<?= $jovenId ?>"
                            <?= $jovenSeleccionado === $jovenId
                                ? 'selected'
                                : '' ?>
                        >

                            <?= htmlspecialchars(
                                $joven['nombre_completo'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <!-- =================================================
                 FECHA
            ================================================== -->

            <div class="form-group">

                <label
                    class="form-label"
                    for="fecha_contacto"
                >

                    <i class="fa-solid fa-calendar-days"></i>

                    Fecha de contacto

                </label>


                <input
                    id="fecha_contacto"
                    class="form-input"
                    type="date"
                    name="fecha_contacto"
                    value="<?= htmlspecialchars(
                        date('Y-m-d'),
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                    max="<?= htmlspecialchars(
                        date('Y-m-d'),
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                    required
                >

            </div>


            <!-- =================================================
                 MODALIDAD
            ================================================== -->

            <div class="form-group">

                <label
                    class="form-label"
                    for="modalidad_contacto"
                >

                    <i class="fa-solid fa-comments"></i>

                    Modalidad

                </label>


                <select
                    id="modalidad_contacto"
                    class="form-select"
                    name="modalidad_contacto"
                    autocomplete="off"
                    required
                >

                    <?php foreach (
                        MODALIDADES_SEGUIMIENTO
                        as $modalidad
                    ): ?>

                        <option
                            value="<?= htmlspecialchars(
                                $modalidad,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                        >

                            <?= match ($modalidad) {

                                'WHATSAPP' =>
                                    'WhatsApp',

                                'LLAMADA' =>
                                    'Llamada',

                                'VISITA' =>
                                    'Visita',

                                'MENSAJE' =>
                                    'Mensaje',

                                default =>
                                    $modalidad

                            } ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <!-- =================================================
                 ESTADO
            ================================================== -->

            <div class="form-group">

                <label
                    class="form-label"
                    for="estado_proceso"
                >

                    <i class="fa-solid fa-list-check"></i>

                    Estado

                </label>


                <select
                    id="estado_proceso"
                    class="form-select"
                    name="estado_proceso"
                    autocomplete="off"
                    required
                >

                    <?php foreach (
                        ESTADOS_SEGUIMIENTO
                        as $estado
                    ): ?>

                        <option
                            value="<?= htmlspecialchars(
                                $estado,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            <?= $estado === 'PENDIENTE'
                                ? 'selected'
                                : '' ?>
                        >

                            <?= match ($estado) {

                                'PENDIENTE' =>
                                    'Pendiente',

                                'EN_PROCESO' =>
                                    'En proceso',

                                'FINALIZADO' =>
                                    'Finalizado',

                                default =>
                                    $estado

                            } ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <!-- =================================================
                 RESPONSABLE
            ================================================== -->

            <div class="form-group">

                <label
                    class="form-label"
                    for="responsable_id"
                >

                    <i class="fa-solid fa-user-tie"></i>

                    Responsable

                </label>


                <select
                    id="responsable_id"
                    class="form-select"
                    name="responsable_id"
                    autocomplete="off"
                >

                    <option value="">
                        Seleccionar responsable
                    </option>


                    <?php foreach (
                        $responsables
                        as $responsable
                    ): ?>

                        <?php
                        $responsableId =
                            (int)$responsable['id'];
                        ?>

                        <option
                            value="<?= $responsableId ?>"
                            <?= $usuarioActual === $responsableId
                                ? 'selected'
                                : '' ?>
                        >

                            <?= htmlspecialchars(
                                $responsable['nombre'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>

        </div>


        <!-- =====================================================
             OBSERVACIONES
        ====================================================== -->

        <div class="form-group form-group-full">

            <label
                class="form-label"
                for="observaciones"
            >

                <i class="fa-solid fa-comment-dots"></i>

                Observaciones

            </label>


            <textarea
                id="observaciones"
                class="form-textarea"
                name="observaciones"
                rows="6"
                maxlength="2000"
                placeholder="Describe la conversación, acuerdos, necesidades detectadas o compromisos establecidos..."
            ></textarea>

        </div>


        <!-- =====================================================
             BOTONES
        ====================================================== -->

        <div class="form-actions">

            <a
                href="index.php"
                class="btn btn-back"
            >
                Volver
            </a>


            <button
                type="submit"
                class="btn btn-primary"
            >

                Guardar Seguimiento

            </button>

        </div>

    </form>

</div>


<script
    src="<?= BASE_URL ?>/assets/js/modulos/seguimientos/crear.js"
></script>


<?php require_once __DIR__ . "/../../includes/footer.php"; ?>