<?php

require_once __DIR__ . "/../../../../middleware/auth.php";
require_once __DIR__ . "/../../../../middleware/permiso.php";
require_once __DIR__ . "/../../../../config/conexion.php";
require_once __DIR__ . "/../../../../services/discipuladoService.php";

if (!tienePermiso('gestionar_reuniones')) {

    header("Location: ../../../dashboard.php");
    exit;

}

if (!isset($_GET["ciclo_id"])) {

    die("Ciclo no encontrado");

}

$cicloId = (int)$_GET["ciclo_id"];

$ciclo = obtenerCicloDiscipuladoPorId($pdo, $cicloId);

if (!$ciclo) {

    die("Ciclo no encontrado");

}

$siguienteOrden = siguienteNumeroOrdenClaseDiscipulado($pdo, $cicloId);

require_once __DIR__ . "/../../../../includes/header.php";

?>

<div class="form-card">

    <div class="form-header">

        <div class="form-header-icon">
            <i class="fa-solid fa-chalkboard"></i>
        </div>

        <div class="form-header-content">

            <h1 class="form-title">
                Nueva clase
            </h1>

            <p class="form-subtitle">
                Ciclo: <?= htmlspecialchars($ciclo['nombre']) ?>. Esta clase se agregará al catálogo base para los próximos ciclos.
            </p>

        </div>

    </div>

    <form
        class="form"
        action="<?= BASE_URL ?>/controllers/discipuladoClaseController.php"
        method="POST"
    >

        <?= csrfField(); ?>

        <input type="hidden" name="action" value="crear_clase_discipulado">
        <input type="hidden" name="ciclo_id" value="<?= (int)$cicloId ?>">

        <div class="form-grid">

            <!-- NUMERO/ORDEN -->

            <div class="form-group">

                <label class="form-label">
                    Número / orden
                </label>

                <input
                    class="form-input"
                    type="number"
                    name="numero_orden"
                    min="1"
                    value="<?= (int)$siguienteOrden ?>"
                    required
                >

            </div>

            <!-- NOMBRE -->

            <div class="form-group">

                <label class="form-label">
                    Nombre de la clase
                </label>

                <input
                    class="form-input"
                    type="text"
                    name="nombre"
                    placeholder="Ej: Clase 1 — Nuevo nacimiento"
                    required
                >

            </div>

            <!-- FECHA PROGRAMADA -->

            <div class="form-group">

                <label class="form-label">
                    Fecha programada (opcional)
                </label>

                <input
                    class="form-input"
                    type="date"
                    name="fecha_programada"
                >

            </div>

            <!-- MODALIDAD PROGRAMADA -->

            <div class="form-group">

                <label class="form-label">
                    Modalidad programada (opcional)
                </label>

                <select
                    class="form-select"
                    name="modalidad_programada"
                >

                    <option value="">Sin definir</option>
                    <option value="PRESENCIAL">Presencial</option>
                    <option value="VIRTUAL">Virtual</option>

                </select>

            </div>

            <!-- DESCRIPCIÓN -->

            <div class="form-group form-group-full">

                <label class="form-label">
                    Descripción (opcional)
                </label>

                <textarea
                    class="form-textarea"
                    name="descripcion"
                    rows="3"
                    placeholder="Contenido o tema de la clase."
                ></textarea>

            </div>

            <!-- OBSERVACIONES -->

            <div class="form-group form-group-full">

                <label class="form-label">
                    Observaciones (opcional)
                </label>

                <textarea
                    class="form-textarea"
                    name="observaciones"
                    rows="2"
                ></textarea>

            </div>

        </div>

        <div class="form-actions">

            <a
                href="../ver.php?ciclo_id=<?= (int)$cicloId ?>"
                class="btn btn-back"
            >
                Volver
            </a>

            <button
                type="submit"
                class="btn btn-primary"
            >
                Guardar clase
            </button>

        </div>

    </form>

</div>

<?php require_once __DIR__ . "/../../../../includes/footer.php"; ?>
