<?php

declare(strict_types=1);

/* ==========================================================
   SET FLASH
========================================================== */

function setFlash(
    string $tipo,
    string $mensaje
): void {

    $_SESSION[$tipo] = $mensaje;

}

/* ==========================================================
   GET FLASH
========================================================== */

function getFlash(string $tipo): ?string
{
    if (empty($_SESSION[$tipo])) {
        return null;
    }

    $mensaje = $_SESSION[$tipo];

    unset($_SESSION[$tipo]);

    return $mensaje;
}

/* ==========================================================
   HAS FLASH
========================================================== */

function hasFlash(string $tipo): bool
{
    return !empty($_SESSION[$tipo]);
}