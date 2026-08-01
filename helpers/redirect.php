<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| HELPER REDIRECT
|--------------------------------------------------------------------------
|
| Gestiona las redirecciones del sistema.
|
| Si se proporciona un tipo y un mensaje, estos se almacenan
| en sesión para mostrarse como notificación en la siguiente
| petición.
|
*/

/* ==========================================================
   REDIRECCIONAR
========================================================== */

function redirect(
    string $ruta,
    string $tipo = '',
    string $mensaje = ''
): void {

    if ($tipo !== '' && $mensaje !== '') {

        $_SESSION[$tipo] = $mensaje;

    }

    header("Location: {$ruta}");

    exit;

}