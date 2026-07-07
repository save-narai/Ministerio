<?php

declare(strict_types=1);

require_once __DIR__ . '/services/sessionService.php';

/* ==========================================================
   REDIRECCIÓN PRINCIPAL
========================================================== */

if (usuarioAutenticado()) {

    header('Location: views/dashboard.php');
    exit;

}

header('Location: views/auth/login.php');
exit;