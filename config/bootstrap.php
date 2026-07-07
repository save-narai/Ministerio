<?php

declare(strict_types=1);

/* ==========================================================
   SESIÓN
========================================================== */

if (session_status() === PHP_SESSION_NONE) {

    session_start();

}

/* ==========================================================
   CONFIGURACIÓN
========================================================== */

$config = require __DIR__ . '/app.php';

/* ==========================================================
   BASE URL
========================================================== */

if (!defined('BASE_URL')) {

    define(

        'BASE_URL',

        '/ministerio'

    );

}

/* ==========================================================
   TIMEZONE
========================================================== */

date_default_timezone_set(

    $config['timezone'] ?? 'America/Bogota'

);

/* ==========================================================
   CONFIG GLOBAL
========================================================== */

$GLOBALS['config'] = $config;


/* ==========================================================
   HELPERS
========================================================== */

require_once __DIR__ . '/../helpers/flash.php';

require_once __DIR__ . '/../helpers/redirect.php';

require_once __DIR__ . '/../helpers/format.php';

require_once __DIR__ . '/../helpers/ui.php';

require_once __DIR__ . '/../helpers/validaciones.php';