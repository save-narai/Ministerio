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

    die("Inscripción no encontrada");

}

$cicloId = (int)$_GET["ciclo_id"];

$inscripcionId = (int)$_GET["id"];

$ciclo = obtenerCicloDiscipuladoPorId($pdo, $cicloId);

if (!$ciclo) {

    die("Ciclo no encontrado");

}

$inscripcion = obtenerInscripcionDiscipuladoDeCiclo($pdo, $cicloId, $inscripcionId);

if (!$inscripcion) {

    die("Inscripción no encontrada");

}

$joven = obtenerJovenPorId($pdo, (int)$inscripcion['joven_id']);

$responsable =
    $inscripcion['responsable_id']
        ? obtenerUsuarioPorId($pdo, (int)$inscripcion['responsable_id'])
        : null;

$checklist = obtenerProgresoInscripcionDiscipulado($pdo, $cicloId, $inscripcionId);

$resumen = obtenerResumenProgresoInscripcionDiscipulado($pdo, $cicloId, $inscripcionId);

$alerta = obtenerAlertaInscripcionDiscipulado($pdo, $cicloId, $inscripcionId);

$observaciones = obtenerObservacionesInscripcionDiscipulado($pdo, $inscripcionId);

$historial = array_values(
    array_filter(
        $checklist,
        fn (array $c) => $c['completada']
    )
);

usort(
    $historial,
    fn (array $a, array $b) => strcmp((string)$b['fecha_completado'], (string)$a['fecha_completado'])
);

require_once __DIR__ . "/../../../../includes/header.php";

?>

<div class="progreso-discipulado-page">

    <!-- =====================================
         PAGE HEADER
    ===================================== -->

    <div class="page-header">

        <div class="page-header-left">

            <h1 class="page-title">
                Progreso · <?= htmlspecialchars($joven['nombre_completo'] ?? 'Joven') ?>
            </h1>

            <p class="page-subtitle">
                Ciclo: <?= htmlspecialchars($ciclo['nombre']) ?>
            </p>

        </div>

        <div class="page-header-right">

            <a
                href="../asistencia.php?ciclo_id=<?= (int)$cicloId ?>"
                class="btn btn-back"
            >
                <i class="fa-solid fa-arrow-left"></i>
                Volver a asistencia
            </a>

        </div>

    </div>

    <!-- =====================================
         INFORMACIÓN
    ===================================== -->

    <div class="page-section">
        <p class="discipulado-header-meta">
            <span><strong>Modalidad principal:</strong> <?= htmlspecialchars($inscripcion['modalidad_principal']) ?></span>
            <span><strong>Estado:</strong> <?= htmlspecialchars($inscripcion['estado']) ?></span>
            <span><strong>Responsable:</strong> <?= htmlspecialchars($responsable['nombre'] ?? 'Sin asignar') ?></span>
        </p>

        <div class="discipulado-action-links">
            <form action="<?= BASE_URL ?>/controllers/discipuladoInscripcionController.php" method="POST" style="display:flex;gap:8px;align-items:center">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="cambiar_modalidad_inscripcion_discipulado">
                <input type="hidden" name="ciclo_id" value="<?= (int)$cicloId ?>">
                <input type="hidden" name="id" value="<?= (int)$inscripcionId ?>">
                <select name="modalidad_principal" class="form-select" style="width:auto">
                    <option value="PRESENCIAL" <?= $inscripcion['modalidad_principal'] === 'PRESENCIAL' ? 'selected' : '' ?>>Presencial</option>
                    <option value="VIRTUAL" <?= $inscripcion['modalidad_principal'] === 'VIRTUAL' ? 'selected' : '' ?>>Virtual</option>
                </select>
                <button type="submit" class="btn btn-back btn-sm">Cambiar modalidad</button>
            </form>

            <form action="<?= BASE_URL ?>/controllers/discipuladoInscripcionController.php" method="POST"
                  onsubmit="return confirm('¿Confirmar el cambio de estado de esta inscripción?');">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="cambiar_estado_inscripcion_discipulado">
                <input type="hidden" name="ciclo_id" value="<?= (int)$cicloId ?>">
                <input type="hidden" name="id" value="<?= (int)$inscripcionId ?>">
                <?php if ($inscripcion['estado'] === 'ACTIVO'): ?>
                    <input type="hidden" name="estado" value="CANCELADO">
                    <button type="submit" class="btn btn-back btn-sm">Retirar del ciclo</button>
                <?php elseif ($inscripcion['estado'] === 'CANCELADO'): ?>
                    <input type="hidden" name="estado" value="ACTIVO">
                    <button type="submit" class="btn btn-primary btn-sm">Reactivar</button>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- =====================================
         RESUMEN
    ===================================== -->

    <div class="gx-stats">

        <div class="gx-stat-card info">
            <div class="gx-stat-value"><?= (int)$resumen['total_clases'] ?></div>
            <div class="gx-stat-label">Total de clases</div>
        </div>

        <div class="gx-stat-card success">
            <div class="gx-stat-value"><?= (int)$resumen['clases_completadas'] ?></div>
            <div class="gx-stat-label">Completadas</div>
        </div>

        <div class="gx-stat-card warning">
            <div class="gx-stat-value"><?= (int)$resumen['clases_pendientes'] ?></div>
            <div class="gx-stat-label">Pendientes</div>
        </div>

        <div class="gx-stat-card info">
            <div class="gx-stat-value"><?= htmlspecialchars((string)$resumen['progreso_porcentaje']) ?>%</div>
            <div class="gx-stat-label">Progreso</div>
        </div>

    </div>

    <?php if ($resumen['completo']): ?>

        <p>
            <span class="badge badge-success">
                <i class="fa-solid fa-circle-check"></i>
                Completado — listo para finalizar
            </span>
            <br>
            <small>La acción de finalizar el proceso se habilita en una fase posterior.</small>
        </p>

    <?php endif; ?>

    <p>
        <strong>Seguimiento:</strong>
        <span class="badge <?= $alerta['estado'] === 'COMPLETADO' ? 'badge-success' : ($alerta['estado'] === 'SIN_ALERTAS' ? 'badge-info' : 'badge-warning') ?>">
            <?= htmlspecialchars(str_replace('_', ' ', $alerta['estado'])) ?>
        </span>
        <?= htmlspecialchars($alerta['mensaje']) ?>.
        <?php if ($alerta['recuperaciones'] > 0): ?>
            <?= (int)$alerta['recuperaciones'] ?> recuperación(es) registrada(s).
        <?php endif; ?>
    </p>

    <!-- =====================================
         PENDIENTES
         (la marcación clase por clase ya vive en la
         matriz de Asistencia del ciclo; aquí solo se
         resume qué le falta a este joven)
    ===================================== -->

    <?php if (!empty($resumen['pendientes'])): ?>

        <div class="page-section">

            <div class="section-header">
                <h2 class="section-title">Pendientes</h2>
            </div>

            <p>
                <?php foreach ($resumen['pendientes'] as $pendiente): ?>
                    <span class="badge badge-info" title="<?= htmlspecialchars($pendiente['clase_nombre']) ?>">
                        ○ <?= (int)$pendiente['numero_orden'] ?>
                    </span>
                <?php endforeach; ?>
            </p>

        </div>

    <?php endif; ?>

    <!-- =====================================
         RECUPERACIONES (solo si hay alguna)
    ===================================== -->

    <?php $recuperaciones = array_values(array_filter($historial, fn (array $c) => !empty($c['es_recuperacion']))); ?>

    <?php if (!empty($recuperaciones)): ?>
        <div class="page-section">
            <div class="section-header"><h2 class="section-title">Recuperaciones</h2></div>
            <ul>
                <?php foreach ($recuperaciones as $recuperacion): ?>
                    <li><?= htmlspecialchars($recuperacion['clase_nombre']) ?> — <?= htmlspecialchars((string)$recuperacion['fecha_completado']) ?> · <?= htmlspecialchars((string)$recuperacion['modalidad_completado']) ?><?= !empty($recuperacion['observaciones']) ? ' · ' . htmlspecialchars($recuperacion['observaciones']) : '' ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- =====================================
         OBSERVACIONES
    ===================================== -->

    <div class="page-section">
        <div class="section-header"><h2 class="section-title">Observaciones</h2></div>

        <?php if ($observaciones): ?>
            <ul class="discipulado-list">
                <?php foreach ($observaciones as $obs): ?>
                    <li class="discipulado-list-row">
                        <span><?= nl2br(htmlspecialchars($obs['observacion'])) ?></span>
                        <small><?= htmlspecialchars($obs['fecha_creacion']) ?></small>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>/controllers/discipuladoInscripcionController.php" method="POST" class="form" style="margin-top:10px">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="agregar_observacion_inscripcion_discipulado">
            <input type="hidden" name="ciclo_id" value="<?= (int)$cicloId ?>">
            <input type="hidden" name="id" value="<?= (int)$inscripcionId ?>">
            <textarea name="observacion" class="form-textarea" rows="2" placeholder="Nueva observación..." required></textarea>
            <button type="submit" class="btn btn-primary btn-sm" style="margin-top:8px">Agregar</button>
        </form>
    </div>

</div>

<?php require_once __DIR__ . "/../../../../includes/footer.php"; ?>
