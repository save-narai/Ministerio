<?php
declare(strict_types=1);

require_once __DIR__ . '/controller.php';
require_once __DIR__ . '/../services/discipuladoService.php';

controllerInit();
$pdo = controllerPdo();

controllerRun([
    'crear_evento_discipulado' => function () use ($pdo) {
        controllerRequirePermission('gestionar_reuniones');
        $cicloId = (int)($_POST['ciclo_id'] ?? 0);
        crearEventoDiscipulado($pdo, $cicloId, $_POST);
        return controllerRedirect('../views/formacion/discipulado/ver.php?ciclo_id=' . $cicloId . '#fechas', 'Fecha importante agregada correctamente.');
    },
    'eliminar_evento_discipulado' => function () use ($pdo) {
        controllerRequirePermission('gestionar_reuniones');
        $cicloId = (int)($_POST['ciclo_id'] ?? 0);
        eliminarEventoDiscipulado($pdo, $cicloId, (int)($_POST['id'] ?? 0));
        return controllerRedirect('../views/formacion/discipulado/ver.php?ciclo_id=' . $cicloId . '#fechas', 'Fecha importante eliminada correctamente.');
    },
    'actualizar_evento_discipulado' => function () use ($pdo) {
        controllerRequirePermission('gestionar_reuniones');
        $cicloId = (int)($_POST['ciclo_id'] ?? 0);
        actualizarEventoDiscipulado($pdo, $cicloId, (int)($_POST['id'] ?? 0), $_POST);
        return controllerRedirect('../views/formacion/discipulado/ver.php?ciclo_id=' . $cicloId . '#fechas', 'Fecha importante actualizada correctamente.');
    }
], ['redirect' => '../views/formacion/discipulado/index.php']);
