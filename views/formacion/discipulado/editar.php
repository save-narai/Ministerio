<?php

declare(strict_types=1);

require_once __DIR__ . "/../../../middleware/auth.php";
require_once __DIR__ . "/../../../middleware/permiso.php";
require_once __DIR__ . "/../../../config/conexion.php";
require_once __DIR__ . "/../../../services/discipuladoService.php";

if (!tienePermiso('gestionar_reuniones')) {

    header("Location: ../../dashboard.php");
    exit;

}

$cicloId = (int)($_GET['ciclo_id'] ?? 0);

$ciclo = obtenerCicloDiscipuladoPorId($pdo, $cicloId);

if (!$ciclo) {

    die('Ciclo no encontrado');

}

$usuarios = obtenerUsuarios($pdo);

$responsablesActuales = array_column(
    obtenerResponsablesCicloDiscipulado($pdo, $cicloId),
    'id'
);

require_once __DIR__ . "/../../../includes/header.php";

?>

<div class="form-card">

    <div class="form-header">

        <div class="form-header-icon">
            <i class="fa-solid fa-pen"></i>
        </div>

        <div class="form-header-content">

            <h1 class="form-title">
                Editar ciclo
            </h1>

            <p class="form-subtitle">
                <?= htmlspecialchars($ciclo['nombre']) ?>
            </p>

        </div>

    </div>

    <form
        class="form"
        action="<?= BASE_URL ?>/controllers/discipuladoCicloController.php"
        method="POST"
    >

        <?= csrfField(); ?>

        <input type="hidden" name="action" value="actualizar_ciclo_discipulado">
        <input type="hidden" name="id" value="<?= (int)$cicloId ?>">

        <div class="form-grid">

            <div class="form-group form-group-full">
                <label class="form-label" for="nombre">Nombre del ciclo</label>
                <input class="form-input" id="nombre" name="nombre" required maxlength="150" value="<?= htmlspecialchars($ciclo['nombre']) ?>">
            </div>

            <div class="form-group">
                <label class="form-label" for="fecha_inicio">Fecha de inicio</label>
                <input class="form-input" id="fecha_inicio" name="fecha_inicio" type="date" required value="<?= htmlspecialchars($ciclo['fecha_inicio']) ?>">
            </div>

            <div class="form-group">
                <label class="form-label" for="fecha_fin">Fecha de finalización</label>
                <input class="form-input" id="fecha_fin" name="fecha_fin" type="date" value="<?= htmlspecialchars((string)($ciclo['fecha_fin'] ?? '')) ?>">
            </div>

            <div class="form-group form-group-full">

                <label class="form-label">Responsables del ciclo</label>

                <div class="discipulado-checkbox-list">

                    <?php foreach ($usuarios as $usuario): ?>

                        <label class="discipulado-checkbox-item">
                            <input
                                type="checkbox"
                                name="responsables[]"
                                value="<?= (int)$usuario['id'] ?>"
                                <?= in_array((int)$usuario['id'], $responsablesActuales, true) ? 'checked' : '' ?>
                            >
                            <?= htmlspecialchars($usuario['nombre']) ?>
                        </label>

                    <?php endforeach; ?>

                </div>

            </div>

            <div class="form-group form-group-full">
                <label class="form-label" for="descripcion">Descripción</label>
                <textarea class="form-textarea" id="descripcion" name="descripcion" rows="3"><?= htmlspecialchars((string)($ciclo['descripcion'] ?? '')) ?></textarea>
            </div>

        </div>

        <p class="form-hint">
            El monitor y el encargado principal se definen al crear el ciclo. Para cambiarlos, contacta a un administrador.
        </p>

        <div class="form-actions">

            <a href="ver.php?ciclo_id=<?= (int)$cicloId ?>" class="btn btn-back">
                Cancelar
            </a>

            <button type="submit" class="btn btn-primary">
                Guardar cambios
            </button>

        </div>

    </form>

</div>

<?php require_once __DIR__ . "/../../../includes/footer.php"; ?>
