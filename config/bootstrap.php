<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| BOOTSTRAP
|--------------------------------------------------------------------------
|
| Punto único de inicialización del sistema.
|
| Responsabilidades:
| • Iniciar la sesión.
| • Cargar la configuración.
| • Definir constantes globales.
| • Configurar PHP.
| • Crear la conexión PDO.
| • Cargar Helpers.
| • Cargar Middleware.
|
*/

/* ==========================================================
   SESIÓN
========================================================== */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/* ==========================================================
   CONFIGURACIÓN
========================================================== */

$config = require __DIR__ . '/app.php';

$GLOBALS['config'] = $config;

/* ==========================================================
   BASE URL
========================================================== */

defined('BASE_URL') || define(
    'BASE_URL',
    $config['base_url'] ?? '/ministerio'
);

/* ==========================================================
   ZONA HORARIA
========================================================== */

date_default_timezone_set(
    $config['timezone'] ?? 'America/Bogota'
);

/* ==========================================================
   CONFIGURACIÓN PHP
========================================================== */

ini_set('default_charset', 'UTF-8');

mb_internal_encoding('UTF-8');

/* ==========================================================
   CONEXIÓN PDO
========================================================== */

require_once __DIR__ . '/conexion.php';

/* ==========================================================
   HELPERS
========================================================== */

require_once __DIR__ . '/../helpers/flash.php';
require_once __DIR__ . '/../helpers/redirect.php';
require_once __DIR__ . '/../helpers/format.php';
require_once __DIR__ . '/../helpers/ui.php';
require_once __DIR__ . '/../helpers/validaciones.php';
require_once __DIR__ . '/../helpers/csrf.php';
require_once __DIR__ . '/../helpers/responses.php';
require_once __DIR__ . '/../helpers/toast.php';
require_once __DIR__ . '/../helpers/fechas.php';
/* ==========================================================
   MIDDLEWARE
========================================================== */

require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../middleware/guest.php';
require_once __DIR__ . '/../middleware/permiso.php';

/* ==========================================================
   FIN DEL BOOTSTRAP
========================================================== */