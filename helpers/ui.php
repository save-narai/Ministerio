<?php

/* =========================================================
   MENÚ ACTIVO
========================================================= */

function menuActivo(string $ruta): string
{
    return str_contains(
        $_SERVER['REQUEST_URI'],
        $ruta
    )
        ? 'active'
        : '';
}


/* =========================================================
   NOMBRE ROL
========================================================= */

function nombreRol(string $rol): string
{
    return match ($rol) {

        'ADMIN'      => 'Administrador',

        'LIDER'      => 'Líder',

        'SUPERVISOR' => 'Supervisor',

        default      => 'Usuario'
    };
}


/* =========================================================
   SALUDO ACTUAL
========================================================= */

function saludoActual(): string
{
    $hora = (int) date('H');

    return match (true) {

        $hora < 12 => 'Buenos días',

        $hora < 18 => 'Buenas tardes',

        default => 'Buenas noches'
    };
}