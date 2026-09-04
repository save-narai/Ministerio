<?php

require_once __DIR__ . "/../../../../middleware/auth.php";
require_once __DIR__ . "/../../../../middleware/permiso.php";
require_once __DIR__ . "/../../../../config/conexion.php";
require_once __DIR__ . "/../../../../services/discipuladoService.php";

if (!tienePermiso('gestionar_reuniones')) {

    header("Location: ../../../dashboard.php");
    exit;

}

if (!isset($_GET["ciclo_id"]) || !isset($_GET["id"])) {

    die("Clase no encontrada");

}

$cicloId = (int)$_GET["ciclo_id"];

$claseId = (int)$_GET["id"];

$ciclo = obtenerCicloDiscipuladoPorId($pdo, $cicloId);

if (!$ciclo) {

    die("Ciclo no encontrado");

}

$clase = obtenerClaseDiscipuladoDeCiclo($pdo, $cicloId, $claseId);

if (!$clase) {

    die("Clase no encontrada");

}

require_once __DIR__ . "/../../../../includes/header.php";

?>

<div class="form-card">

    <div class="form-header">

        <div class="form-header-icon">
            <i class="fa-solid fa-chalkboard"></i>
        </div>

        <div class="form-header-content">

            <h1 class="form-title">
                Editar clase
            </h1>

            <p class="form-subtitle">
                Ciclo: <?= htmlspecialchars($ciclo['nombre']) ?>
            </p>

        </div>

    </div>

    <form
        class="form"
        method="POST"
        action="<?= BASE_URL ?>/controllers/discipuladoClaseController.php"
    >

        <?= csrfField(); ?>

        <input type="hidden" name="action" value="actualizar_clase_discipulado">
        <input type="hidden" name="ciclo_id" value="<?= (int)$cicloId ?>">
        <input type="hidden" name="id" value="<?= (int)$clase["id"] ?>">

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
                    value="<?= (int)$clase['numero_orden'] ?>"
                    required
                >

            </div>

            <!-- ESTADO (SOLO LECTURA — SE CAMBIA DESDE EL LISTADO) -->

            <div class="form-group">

                <label class="form-label">
                    Estado
                </label>

                <input
                    class="form-input"
                    type="text"
                    value="<?= htmlspecialchars($clase['estado']) ?>"
                    disabled
                >

            </div>

            <!-- NOMBRE -->

            <div class="form-group form-group-full">

                <label class="form-label">
                    Nombre de la clase
                </label>

                <input
                    class="form-input"
                    type="text"
                    name="nombre"
                    value="<?= htmlspecialchars($clase['nombre']) ?>"
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
                    value="<?= htmlspecialchars((string)($clase['fecha_programada'] ?? '')) ?>"
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

                    <option value="" <?= empty($clase['modalidad_programada']) ? 'selected' : '' ?>>
                        Sin definir
                    </option>

                    <option value="PRESENCIAL" <?= $clase['modalidad_programada'] === 'PRESENCIAL' ? 'selected' : '' ?>>
                        Presencial
                    </option>

                    <option value="VIRTUAL" <?= $clase['modalidad_programada'] === 'VIRTUAL' ? 'selected' : '' ?>>
                        Virtual
                    </option>

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
                ><?= htmlspecialchars((string)($clase['descripcion'] ?? '')) ?></textarea>

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
                ><?= htmlspecialchars((string)($clase['observaciones'] ?? '')) ?></textarea>

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
                Guardar cambios
            </button>

        </div>

    </form>

</div>

<?php require_once __DIR__ . "/../../../../includes/footer.php"; ?>
