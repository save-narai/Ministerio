<?php

declare(strict_types=1);

require_once __DIR__ . '/controller.php';

require_once __DIR__ . '/../services/actividadService.php';
require_once __DIR__ . '/../services/dashboardService.php';

controllerInit();

controllerRequirePermission('ver_dashboard');

$pdo = controllerPdo();

/* =========================================================
   ACTIVIDAD
========================================================= */

actualizarEstadoActividad($pdo);

/* =========================================================
   DASHBOARD
========================================================= */

$data = obtenerDashboardData($pdo);