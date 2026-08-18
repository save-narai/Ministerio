<?php

declare(strict_types=1);

require_once __DIR__ . '/../services/sessionService.php';

/* ==========================================================
   EXIGIR AUTENTICACIÓN
========================================================== */

function exigirAutenticacion(): void
{
    if (usuarioAutenticado()) {
        return;
    }

    redirect(BASE_URL . '/index.php');
}