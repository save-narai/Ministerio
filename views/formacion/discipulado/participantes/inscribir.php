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

$jovenesDisponibles = obtenerJovenesDisponiblesParaInscripcionDiscipulado(
    $pdo,
    $cicloId
);

$usuarios = $pdo->query("
    SELECT id, nombre
    FROM usuarios
    WHERE activo = 1
    ORDER BY nombre ASC
")->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . "/../../../../includes/header.php";

?>

<div class="form-card">

    <div class="form-header">

        <div class="form-header-icon">
            <i class="fa-solid fa-user-plus"></i>
        </div>

        <div class="form-header-content">

            <h1 class="form-title">
                Inscribir joven
            </h1>

            <p class="form-subtitle">
                Ciclo: <?= htmlspecialchars($ciclo['nombre']) ?>
            </p>

        </div>

    </div>

    <?php if (empty($jovenesDisponibles)): ?>

        <p class="text-center">
            No hay jóvenes disponibles para inscribir en este momento.
            (Ya están inscritos en este ciclo, o tienen una inscripción activa en otro ciclo.)
        </p>

        <div class="form-actions">

            <a href="index.php?ciclo_id=<?= (int)$cicloId ?>" class="btn btn-back">
                Volver
            </a>

        </div>

    <?php else: ?>

    <form
        class="form"
        action="<?= BASE_URL ?>/controllers/discipuladoInscripcionController.php"
        method="POST"
    >

        <?= csrfField(); ?>

        <input type="hidden" name="action" value="inscribir_joven_discipulado">
        <input type="hidden" name="ciclo_id" value="<?= (int)$cicloId ?>">

        <div class="form-grid">

            <!-- JOVEN -->

            <div class="form-group form-group-full">

                <label class="form-label">
                    Joven
                </label>

                <select
                    class="form-select"
                    name="joven_id"
                    required
                >

                    <option value="">Seleccionar joven</option>

                    <?php foreach ($jovenesDisponibles as $joven): ?>

                        <option value="<?= (int)$joven['id'] ?>">
                            <?= htmlspecialchars($joven['nombre_completo']) ?>
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>

            <!-- MODALIDAD PRINCIPAL -->

            <div class="form-group">

                <label class="form-label">
                    Modalidad principal
                </label>

                <select
                    class="form-select"
                    name="modalidad_principal"
                    required
                >

                    <option value="">Seleccionar</option>
                    <option value="PRESENCIAL">Presencial</option>
                    <option value="VIRTUAL">Virtual</option>

                </select>

            </div>

            <!-- RESPONSABLE -->

            <div class="form-group">

                <label class="form-label">
                    Responsable (opcional)
                </label>

                <select
                    class="form-select"
                    name="responsable_id"
                >

                    <option value="">Sin asignar</option>

                    <?php foreach ($usuarios as $usuario): ?>

                        <option value="<?= (int)$usuario['id'] ?>">
                            <?= htmlspecialchars($usuario['nombre']) ?>
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>

            <!-- FECHA DE INSCRIPCIÓN -->

            <div class="form-group">

                <label class="form-label">
                    Fecha de inscripción
                </label>

                <input
                    class="form-input"
                    type="date"
                    name="fecha_inscripcion"
                    value="<?= date('Y-m-d') ?>"
                >

            </div>

            <!-- OBSERVACIÓN INICIAL -->

            <div class="form-group form-group-full">

                <label class="form-label">
                    Observación inicial (opcional)
                </label>

                <textarea
                    class="form-textarea"
                    name="observacion"
                    rows="3"
                    placeholder="Ej: Viene referido del Grupo Conexión de los jueves."
                ></textarea>

            </div>

        </div>

        <div class="form-actions">

            <a
                href="index.php?ciclo_id=<?= (int)$cicloId ?>"
                class="btn btn-back"
            >
                Volver
            </a>

            <button
                type="submit"
                class="btn btn-primary"
            >
                Inscribir joven
            </button>

        </div>

    </form>

    <?php endif; ?>

</div>

<?php require_once __DIR__ . "/../../../../includes/footer.php"; ?>
