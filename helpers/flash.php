<?php

/* =========================================================
   SET FLASH
========================================================= */

function setFlash(
    string $tipo,
    string $mensaje
): void {

    $_SESSION[$tipo] = $mensaje;
}


/* =========================================================
   GET FLASH
========================================================= */

function getFlash(
    string $tipo
): ?string {

    if (!isset($_SESSION[$tipo])) {

        return null;
    }

    $mensaje = $_SESSION[$tipo];

    unset($_SESSION[$tipo]);

    return $mensaje;
}