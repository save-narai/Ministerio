<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| HELPER FLASH
|--------------------------------------------------------------------------
|
| Gestiona los mensajes flash almacenados en sesión.
|
| Responsabilidades:
| - Guardar mensajes temporales.
| - Recuperarlos.
| - Eliminarlos tras su lectura.
| - Verificar su existencia.
|
*/

/* ==========================================================
   GUARDAR MENSAJE
========================================================== */

function setFlash(
    string $tipo,
    string $mensaje
): void {

    $_SESSION[$tipo] = $mensaje;

}

/* ==========================================================
   OBTENER MENSAJE
========================================================== */

function getFlash(
    string $tipo
): ?string {

    if (!isset($_SESSION[$tipo])) {

        return null;

    }

    $mensaje = (string) $_SESSION[$tipo];

    unset($_SESSION[$tipo]);

    return $mensaje;

}

/* ==========================================================
   VERIFICAR MENSAJE
========================================================== */

function hasFlash(
    string $tipo
): bool {

    return isset($_SESSION[$tipo]);

}