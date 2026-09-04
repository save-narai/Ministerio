<?php

declare(strict_types=1);

/* ==========================================================
   CREAR CICLO DE DISCIPULADO
   ----------------------------------------------------------
   Reemplaza el archivo anterior: este mismo path (crear.php)
   era en realidad una copia de clases/crear.php ("Nueva
   clase"), que exigía un ciclo_id y por eso el botón "Crear
   ciclo" de index.php (que enlaza aquí sin ciclo_id) siempre
   terminaba en "Ciclo no encontrado". La acción real de
   crear un ciclo (crear_ciclo_discipulado) ya existía en
   discipuladoCicloController.php pero ningún formulario la
   usaba. Este archivo la conecta.
========================================================== */

require_once __DIR__ . "/../../../middleware/auth.php";
require_once __DIR__ . "/../../../middleware/permiso.php";
require_once __DIR__ . "/../../../config/conexion.php";
require_once __DIR__ . "/../../../services/discipuladoService.php";

if (!tienePermiso('gestionar_reuniones')) {

    header("Location: ../../../dashboard.php");
    exit;

}

$usuarios = obtenerUsuarios($pdo);

require_once __DIR__ . "/../../../includes/header.php";

?>

<div class="form-card">

    <div class="form-header">

        <div class="form-header-icon">
            <i class="fa-solid fa-graduation-cap"></i>
        </div>

        <div class="form-header-content">

            <h1 class="form-title">
                Crear ciclo de discipulado
            </h1>

            <p class="form-subtitle">
                Define los datos básicos; las clases y los jóvenes se agregan después de crear el ciclo.
            </p>

        </div>

    </div>

    <form
        class="form"
        action="<?= BASE_URL ?>/controllers/discipuladoCicloController.php"
        method="POST"
    >

        <?= csrfField(); ?>

        <input type="hidden" name="action" value="crear_ciclo_discipulado">

        <div class="form-grid">

            <div class="form-group form-group-full">
                <label class="form-label" for="nombre">Nombre del ciclo</label>
                <input class="form-input" id="nombre" name="nombre" required maxlength="150" placeholder="Ej: Ciclo de discipulado julio">
            </div>

            <div class="form-group">
                <label class="form-label" for="fecha_inicio">Fecha de inicio</label>
                <input class="form-input" id="fecha_inicio" name="fecha_inicio" type="date" required value="<?= date('Y-m-d') ?>">
            </div>

            <div class="form-group">
                <label class="form-label" for="fecha_fin">Fecha de finalización (opcional)</label>
                <input class="form-input" id="fecha_fin" name="fecha_fin" type="date">
            </div>

            <div class="form-group">
                <label class="form-label" for="monitor_id">Monitor (opcional)</label>
                <select class="form-select" id="monitor_id" name="monitor_id">
                    <option value="">Sin asignar</option>
                    <?php foreach ($usuarios as $usuario): ?>
                        <option value="<?= (int)$usuario['id'] ?>"><?= htmlspecialchars($usuario['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="encargado_principal_id">Encargado principal (opcional)</label>
                <select class="form-select" id="encargado_principal_id" name="encargado_principal_id">
                    <option value="">Sin asignar</option>
                    <?php foreach ($usuarios as $usuario): ?>
                        <option value="<?= (int)$usuario['id'] ?>"><?= htmlspecialchars($usuario['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group form-group-full">

                <label class="form-label">Responsables del ciclo (opcional)</label>

                <div class="discipulado-checkbox-list">

                    <?php foreach ($usuarios as $usuario): ?>

                        <label class="discipulado-checkbox-item">
                            <input
                                type="checkbox"
                                name="responsables[]"
                                value="<?= (int)$usuario['id'] ?>"
                            >
                            <?= htmlspecialchars($usuario['nombre']) ?>
                        </label>

                    <?php endforeach; ?>

                </div>

            </div>

            <div class="form-group form-group-full">
                <label class="form-label" for="descripcion">Descripción (opcional)</label>
                <textarea class="form-textarea" id="descripcion" name="descripcion" rows="3"></textarea>
            </div>

        </div>

        <p class="form-hint">
            El monitor y el encargado principal solo se pueden definir aquí, al crear el ciclo.
        </p>

        <div class="form-actions">

            <a href="index.php" class="btn btn-back">
                Cancelar
            </a>

            <button type="submit" class="btn btn-primary">
                Crear ciclo
            </button>

        </div>

    </form>

</div>

<?php require_once __DIR__ . "/../../../includes/footer.php"; ?>
