<?php

/* ==========================================================
   VISTA ÚNICA DEL CICLO DE DISCIPULADO
   ----------------------------------------------------------
   Reemplaza el archivo anterior, que era una copia accidental
   de participantes/ver.php (misma ruta rota de requires, mismo
   die("Inscripción no encontrada") esperando un $_GET['id']
   que nunca llega desde los controladores).

   Todos los controladores (discipuladoCicloController,
   discipuladoInscripcionController, discipuladoClaseController,
   discipuladoProgresoController, discipuladoEventoController)
   YA redirigían aquí pasando solo ?ciclo_id=, así que esta vista
   se construyó para recibir exactamente eso: un ciclo completo
   en una sola pantalla (resumen, matriz de asistencia, progreso
   del ciclo con accesos a clases/material/maestros, fechas).
   El detalle de cada joven vive en participantes/progreso.php,
   al que se llega desde el nombre en la matriz de asistencia.

   No se elimina ninguna función del service: todo aquí reutiliza
   obtenerResumenCicloDiscipulado(), obtenerClasesDiscipulado(),
   obtenerEventosDiscipulado(), obtenerProgresoInscripcionDiscipulado(),
   etc., ya existentes.
========================================================== */

require_once __DIR__ . "/../../../middleware/auth.php";
require_once __DIR__ . "/../../../middleware/permiso.php";
require_once __DIR__ . "/../../../config/conexion.php";
require_once __DIR__ . "/../../../services/discipuladoService.php";

if (!tienePermiso('gestionar_reuniones')) {
    header("Location: ../../../dashboard.php");
    exit;
}

if (!isset($_GET['ciclo_id'])) {
    die('Ciclo no encontrado');
}

$cicloId = (int)$_GET['ciclo_id'];

$ciclo = obtenerCicloDiscipuladoPorId($pdo, $cicloId);

if (!$ciclo) {
    die('Ciclo no encontrado');
}

$resumen = obtenerResumenCicloDiscipulado($pdo, $cicloId);
$clases = obtenerClasesDiscipulado($pdo, $cicloId);
$eventos = obtenerEventosDiscipulado($pdo, $cicloId);

$clasesCompletadas = count(array_filter($clases, fn (array $c) => ($c['estado'] ?? '') === 'REALIZADA'));
$totalClasesCiclo = count($clases);

// Matriz de asistencia (joven × clase), la misma que usa asistencia.php,
// para mostrarla directamente en el dashboard del ciclo.
$checklistCiclo = obtenerChecklistDiscipulado($pdo, $cicloId);
$clasesMatriz = $checklistCiclo['clases'];
$filasMatriz = $checklistCiclo['inscripciones'];
$totalClasesMatriz = count($clasesMatriz);
$posicionRepaso1 = min(4, $totalClasesMatriz);

$ultimaObservacionPorInscripcion = [];
foreach ($filasMatriz as $fila) {
    $observacionesFila = obtenerObservacionesInscripcionDiscipulado($pdo, (int)$fila['id']);
    $ultimaObservacionPorInscripcion[(int)$fila['id']] = $observacionesFila[0]['observacion'] ?? '';
}

$estadoBadge = [
    'ACTIVO' => 'badge-success',
    'PLANIFICADO' => 'badge-info',
    'FINALIZADO' => 'badge-info',
    'CANCELADO' => 'badge-back',
][$ciclo['estado']] ?? 'badge-info';

require_once __DIR__ . "/../../../includes/header.php";

?>
<div class="discipulado-page" id="ciclo-<?= (int)$cicloId ?>">

    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title"><?= htmlspecialchars($ciclo['nombre']) ?></h1>
            <p class="page-subtitle">
                <span class="badge <?= $estadoBadge ?>"><?= htmlspecialchars($ciclo['estado']) ?></span>
                <?= (int)$resumen['participantes'] ?> jóvenes · <?= htmlspecialchars((string)$resumen['avance_promedio']) ?>% progreso promedio
            </p>
        </div>
        <div class="page-header-right">
            <a href="index.php" class="btn btn-back">Volver a ciclos</a>
            <a href="editar.php?ciclo_id=<?= (int)$cicloId ?>" class="btn btn-back">Editar ciclo</a>
        </div>
    </div>

    <!-- ===================== RESUMEN ===================== -->
    <div class="gx-stats">
        <div class="gx-stat-card info">
            <div class="gx-stat-value"><?= (int)$resumen['participantes'] ?></div>
            <div class="gx-stat-label">Jóvenes inscritos</div>
        </div>
        <div class="gx-stat-card success">
            <div class="gx-stat-value"><?= (int)$resumen['completados'] ?></div>
            <div class="gx-stat-label">Completaron el discipulado</div>
        </div>
        <div class="gx-stat-card warning">
            <div class="gx-stat-value"><?= (int)$resumen['con_pendientes'] ?></div>
            <div class="gx-stat-label">Con clases pendientes</div>
        </div>
        <div class="gx-stat-card <?= $resumen['requieren_atencion'] > 0 ? 'warning' : 'info' ?>">
            <div class="gx-stat-value"><?= (int)$resumen['requieren_atencion'] ?></div>
            <div class="gx-stat-label">Requieren atención</div>
        </div>
    </div>

    <!-- ===================== INFORMACIÓN DEL CICLO ===================== -->
    <div class="page-section">
        <div class="section-header"><h2 class="section-title">Información del ciclo</h2></div>
        <p>
            <?php if (!empty($ciclo['descripcion'])): ?>
                <?= nl2br(htmlspecialchars($ciclo['descripcion'])) ?><br>
            <?php endif; ?>
            <strong>Inicio:</strong> <?= htmlspecialchars((string)($ciclo['fecha_inicio'] ?? '—')) ?>
            &nbsp;·&nbsp;
            <strong>Fin estimado:</strong> <?= htmlspecialchars((string)($ciclo['fecha_fin'] ?? '—')) ?>
            &nbsp;·&nbsp;
            <strong>Responsables:</strong>
            <?= $ciclo['responsables']
                    ? htmlspecialchars(implode(', ', array_column($ciclo['responsables'], 'nombre')))
                    : 'Sin asignar' ?>
        </p>
    </div>

    <!-- ===================== ASISTENCIA (matriz joven × clase) ===================== -->
    <div class="page-section" id="asistencia">
        <div class="section-header">
            <h2 class="section-title">Asistencia</h2>
            <p class="section-subtitle"><?= (int)count($filasMatriz) ?> inscritos</p>
        </div>

        <?php if (empty($filasMatriz)): ?>
            <p>Todavía no hay jóvenes inscritos en este ciclo.</p>
        <?php else: ?>
            <div class="discipulado-matriz-wrap" data-discipulado-matriz data-ciclo-id="<?= (int)$cicloId ?>">
                <table class="discipulado-matriz">
                    <thead>
                        <tr>
                            <th class="col-numero">#</th>
                            <th class="col-nombre">Nombre y Apellido</th>
                            <th class="col-modalidad">Modalidad</th>
                            <?php foreach ($clasesMatriz as $indice => $clase): ?>
                                <th class="col-clase" title="<?= htmlspecialchars($clase['nombre']) ?>">
                                    Clase <?= (int)$clase['numero_orden'] ?>
                                </th>
                                <?php if ($indice + 1 === $posicionRepaso1): ?>
                                    <th class="col-repaso">Repaso</th>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            <?php if ($totalClasesMatriz > 0): ?>
                                <th class="col-repaso">Repaso</th>
                            <?php endif; ?>
                            <th class="col-pendientes">Pendientes</th>
                            <th class="col-observaciones">Observación</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($filasMatriz as $numeroFila => $fila): ?>
                            <?php
                                $inscripcionIdFila = (int)$fila['id'];
                                $estaActivoFila = $fila['estado'] === 'ACTIVO';
                                $estaRetiradoFila = $fila['estado'] === 'CANCELADO';
                            ?>
                            <tr data-inscripcion-id="<?= $inscripcionIdFila ?>" data-estado="<?= htmlspecialchars($fila['estado']) ?>"
                                class="<?= $estaRetiradoFila ? 'discipulado-fila-retirada' : '' ?>">
                                <td class="col-numero"><?= $numeroFila + 1 ?></td>
                                <td class="col-nombre">
                                    <a href="participantes/progreso.php?ciclo_id=<?= (int)$cicloId ?>&id=<?= $inscripcionIdFila ?>">
                                        <?= htmlspecialchars($fila['nombre_completo']) ?>
                                    </a>
                                </td>
                                <td class="col-modalidad">
                                    <select class="discipulado-matriz-select" data-modalidad-select <?= $estaActivoFila ? '' : 'disabled' ?>>
                                        <option value="PRESENCIAL" <?= $fila['modalidad_principal'] === 'PRESENCIAL' ? 'selected' : '' ?>>Presencial</option>
                                        <option value="VIRTUAL" <?= $fila['modalidad_principal'] === 'VIRTUAL' ? 'selected' : '' ?>>Virtual</option>
                                    </select>
                                </td>
                                <?php foreach ($clasesMatriz as $indice => $clase): ?>
                                    <?php
                                        $claseIdMatriz = (int)$clase['id'];
                                        $celda = $fila['celdas'][$claseIdMatriz] ?? null;
                                        $completada = $celda !== null;
                                    ?>
                                    <td class="col-clase discipulado-matriz-celda <?= $completada ? 'discipulado-cell-completa' : '' ?>" data-matriz-celda>
                                        <input type="checkbox" data-clase-checkbox data-clase-id="<?= $claseIdMatriz ?>"
                                               <?= $completada ? 'checked' : '' ?> <?= $estaActivoFila ? '' : 'disabled' ?>
                                               aria-label="<?= htmlspecialchars($clase['nombre']) ?> — <?= htmlspecialchars($fila['nombre_completo']) ?>">
                                    </td>
                                    <?php if ($indice + 1 === $posicionRepaso1): ?>
                                        <td class="col-repaso">
                                            <input type="checkbox" data-repaso-checkbox data-numero-repaso="1"
                                                   <?= !empty($fila['repaso_1']) ? 'checked' : '' ?> <?= $estaActivoFila ? '' : 'disabled' ?>
                                                   aria-label="Repaso 1 — <?= htmlspecialchars($fila['nombre_completo']) ?>">
                                        </td>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                <?php if ($totalClasesMatriz > 0): ?>
                                    <td class="col-repaso">
                                        <input type="checkbox" data-repaso-checkbox data-numero-repaso="2"
                                               <?= !empty($fila['repaso_2']) ? 'checked' : '' ?> <?= $estaActivoFila ? '' : 'disabled' ?>
                                               aria-label="Repaso 2 — <?= htmlspecialchars($fila['nombre_completo']) ?>">
                                    </td>
                                <?php endif; ?>
                                <td class="col-pendientes" data-pendientes-valor><?= (int)$fila['clases_pendientes'] ?></td>
                                <td class="col-observaciones">
                                    <input type="text" class="discipulado-matriz-input" data-observacion-input
                                           placeholder="Sin observación"
                                           value="<?= htmlspecialchars($ultimaObservacionPorInscripcion[$inscripcionIdFila] ?? '') ?>"
                                           data-ultimo-valor="<?= htmlspecialchars($ultimaObservacionPorInscripcion[$inscripcionIdFila] ?? '') ?>"
                                           <?= $estaActivoFila ? '' : 'disabled' ?>>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div style="margin-top:12px">
            <a href="participantes/inscribir.php?ciclo_id=<?= (int)$cicloId ?>" class="btn btn-primary btn-sm">+ Inscribir joven</a>
        </div>

        <script src="<?= BASE_URL ?>/assets/js/modulos/discipulado-matriz.js" defer></script>
    </div>

    <!-- ===================== PROGRESO DEL CICLO (9 lecciones) ===================== -->
    <div class="page-section" id="progreso">
        <div class="section-header">
            <h2 class="section-title">Progreso del ciclo</h2>
            <p class="section-subtitle"><?= $clasesCompletadas ?> de <?= $totalClasesCiclo ?> lecciones dictadas</p>
        </div>
        <p>
            <?php foreach ($clases as $c): ?>
                <span class="badge <?= ($c['estado'] ?? '') === 'REALIZADA' ? 'badge-success' : 'badge-info' ?>"
                      title="<?= htmlspecialchars($c['nombre']) ?>">
                    <?= ($c['estado'] ?? '') === 'REALIZADA' ? '✓' : '○' ?> <?= (int)$c['numero_orden'] ?>
                </span>
            <?php endforeach; ?>
        </p>
        <div class="discipulado-action-links">
            <a class="btn btn-back btn-sm" href="clases/index.php?ciclo_id=<?= (int)$cicloId ?>">Administrar clases</a>
            <a class="btn btn-back btn-sm" href="materiales.php">Material</a>
            <a class="btn btn-back btn-sm" href="maestros.php?ciclo_id=<?= (int)$cicloId ?>">Maestros</a>
        </div>
    </div>

    <!-- ===================== FECHAS IMPORTANTES ===================== -->
    <div class="page-section" id="fechas">
        <div class="section-header"><h2 class="section-title">Fechas importantes</h2></div>
        <?php if (!$eventos): ?>
            <p>Todavía no hay fechas configuradas para este ciclo.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table gx-table">
                    <thead><tr><th>Evento</th><th>Fecha</th><th>Hora</th><th>Descripción</th><th>Acción</th></tr></thead>
                    <tbody>
                    <?php foreach ($eventos as $evento): ?>
                        <tr>
                            <td><?= htmlspecialchars($evento['nombre']) ?></td>
                            <td><?= htmlspecialchars($evento['fecha']) ?></td>
                            <td><?= htmlspecialchars($evento['hora'] ?: '—') ?></td>
                            <td><?= htmlspecialchars($evento['descripcion'] ?: '—') ?></td>
                            <td>
                                <div class="table-actions">
                                    <a class="btn btn-primary btn-sm" href="eventos.php?ciclo_id=<?= (int)$cicloId ?>&editar_id=<?= (int)$evento['id'] ?>">Editar</a>
                                    <form action="<?= BASE_URL ?>/controllers/discipuladoEventoController.php" method="POST" onsubmit="return confirm('¿Eliminar esta fecha?');">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="action" value="eliminar_evento_discipulado">
                                        <input type="hidden" name="ciclo_id" value="<?= (int)$cicloId ?>">
                                        <input type="hidden" name="id" value="<?= (int)$evento['id'] ?>">
                                        <button class="btn btn-back btn-sm">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <details style="margin-top:12px">
            <summary class="btn btn-primary btn-sm">+ Agregar fecha importante</summary>
            <form action="<?= BASE_URL ?>/controllers/discipuladoEventoController.php" method="POST" class="form" style="margin-top:10px">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="crear_evento_discipulado">
                <input type="hidden" name="ciclo_id" value="<?= (int)$cicloId ?>">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Evento</label>
                        <input class="form-input" name="nombre" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Fecha</label>
                        <input class="form-input" type="date" name="fecha" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Hora (opcional)</label>
                        <input class="form-input" type="time" name="hora">
                    </div>
                    <div class="form-group form-group-full">
                        <label class="form-label">Descripción (opcional)</label>
                        <textarea class="form-textarea" name="descripcion" rows="2"></textarea>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Guardar fecha</button>
                </div>
            </form>
        </details>
    </div>

</div>
<?php require_once __DIR__ . "/../../../includes/footer.php"; ?>
