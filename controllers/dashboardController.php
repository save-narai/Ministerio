<?php

require_once __DIR__
    . "/../middleware/permiso.php";

require_once __DIR__
    . "/../config/conexion.php";

require_once __DIR__
    . "/../services/actividadService.php";

require_once __DIR__
    . "/../services/dashboardService.php";

/* =========================================================
   SEGURIDAD
========================================================= */

if (!tienePermiso('ver_dashboard')) {

    die("Acceso denegado.");
}

/* =========================================================
   ACTIVIDAD
========================================================= */

actualizarEstadoActividad($pdo);

/* =========================================================
   DASHBOARD
========================================================= */

$data = obtenerDashboardData($pdo);