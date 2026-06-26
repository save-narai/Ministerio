<?php

/* =========================================================
   REDIRECT
========================================================= */

function redirect(
    string $ruta,
    string $tipo = '',
    string $mensaje = ''
): void {

    if (!empty($tipo) && !empty($mensaje)) {

        $_SESSION[$tipo] = $mensaje;
    }

    header("Location: {$ruta}");

    exit();
}