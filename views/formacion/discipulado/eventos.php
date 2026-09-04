<?php
require_once __DIR__ . '/../../../middleware/auth.php';
require_once __DIR__ . '/../../../middleware/permiso.php';
require_once __DIR__ . '/../../../config/conexion.php';
require_once __DIR__ . '/../../../services/discipuladoService.php';
if (!tienePermiso('gestionar_reuniones')) { header('Location: ../../../dashboard.php'); exit; }
$cicloId = (int)($_GET['ciclo_id'] ?? 0);
$ciclo = obtenerCicloDiscipuladoPorId($pdo, $cicloId);
if (!$ciclo) { die('Ciclo no encontrado'); }
$eventos = obtenerEventosDiscipulado($pdo, $cicloId);
$editarId = (int)($_GET['editar_id'] ?? 0);
$eventoEditar = null;
foreach ($eventos as $evento) { if ((int)$evento['id'] === $editarId) { $eventoEditar = $evento; break; } }
require_once __DIR__ . '/../../../includes/header.php';
?>
<div class="discipulado-page">
    <div class="page-header">
        <div class="page-header-left"><h1 class="page-title">Fechas importantes · <?= htmlspecialchars($ciclo['nombre']) ?></h1><p class="page-subtitle">Eventos configurables, ordenados cronológicamente.</p></div>
        <div class="page-header-right"><a class="btn btn-back" href="ver.php?ciclo_id=<?= $cicloId ?>">Volver al ciclo</a></div>
    </div>
    <div class="page-section">
        <h2 class="section-title">Próximas fechas</h2>
        <?php if (!$eventos): ?><p>Todavía no hay fechas configuradas.</p><?php else: ?>
            <div class="table-responsive"><table class="table gx-table"><thead><tr><th>Evento</th><th>Fecha</th><th>Hora</th><th>Descripción</th><th>Acciones</th></tr></thead><tbody>
            <?php foreach ($eventos as $evento): ?><tr><td><?= htmlspecialchars($evento['nombre']) ?></td><td><?= htmlspecialchars($evento['fecha']) ?></td><td><?= htmlspecialchars($evento['hora'] ?: '—') ?></td><td><?= htmlspecialchars($evento['descripcion'] ?: '—') ?></td><td><div class="table-actions"><a class="btn btn-primary btn-sm" href="eventos.php?ciclo_id=<?= $cicloId ?>&editar_id=<?= (int)$evento['id'] ?>">Editar</a><form action="<?= BASE_URL ?>/controllers/discipuladoEventoController.php" method="POST" onsubmit="return confirm('¿Eliminar esta fecha?');"><?= csrfField() ?><input type="hidden" name="action" value="eliminar_evento_discipulado"><input type="hidden" name="ciclo_id" value="<?= $cicloId ?>"><input type="hidden" name="id" value="<?= (int)$evento['id'] ?>"><button class="btn btn-back btn-sm">Eliminar</button></form></div></td></tr><?php endforeach; ?>
            </tbody></table></div>
        <?php endif; ?>
    </div>
    <div class="page-section">
        <h2 class="section-title"><?= $eventoEditar ? 'Editar fecha importante' : 'Agregar fecha importante' ?></h2>
        <form class="form" action="<?= BASE_URL ?>/controllers/discipuladoEventoController.php" method="POST">
            <?= csrfField() ?><input type="hidden" name="action" value="<?= $eventoEditar ? 'actualizar_evento_discipulado' : 'crear_evento_discipulado' ?>"><input type="hidden" name="ciclo_id" value="<?= $cicloId ?>"><?php if ($eventoEditar): ?><input type="hidden" name="id" value="<?= (int)$eventoEditar['id'] ?>"><?php endif; ?>
            <div class="form-grid"><div class="form-group"><label class="form-label">Evento</label><input class="form-input" name="nombre" value="<?= htmlspecialchars($eventoEditar['nombre'] ?? '') ?>" required></div><div class="form-group"><label class="form-label">Fecha</label><input class="form-input" type="date" name="fecha" value="<?= htmlspecialchars($eventoEditar['fecha'] ?? '') ?>" required></div><div class="form-group"><label class="form-label">Hora (opcional)</label><input class="form-input" type="time" name="hora" value="<?= htmlspecialchars($eventoEditar['hora'] ?? '') ?>"></div><div class="form-group form-group-full"><label class="form-label">Descripción (opcional)</label><textarea class="form-textarea" name="descripcion" rows="2"><?= htmlspecialchars($eventoEditar['descripcion'] ?? '') ?></textarea></div></div>
            <div class="form-actions"><button class="btn btn-primary"><?= $eventoEditar ? 'Actualizar fecha' : 'Guardar fecha' ?></button><?php if ($eventoEditar): ?><a class="btn btn-back" href="eventos.php?ciclo_id=<?= $cicloId ?>">Cancelar</a><?php endif; ?></div>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>
