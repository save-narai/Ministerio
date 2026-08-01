<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Session Service
|--------------------------------------------------------------------------
|
| Servicio centralizado para la gestión de la sesión del usuario.
|
| Ningún Controller, Helper, Middleware o View debe acceder
| directamente a $_SESSION.
|
*/

if (session_status() !== PHP_SESSION_ACTIVE) {

    session_start();

}

/* ==========================================================
   CLAVE DE SESIÓN
========================================================== */

const SESSION_USER = 'usuario';

/* ==========================================================
   INICIAR SESIÓN
========================================================== */

function iniciarSesionUsuario(array $usuario): void
{
    session_regenerate_id(true);

    $_SESSION[SESSION_USER] = [

        'id' => (int) $usuario['id'],

        'nombre' => (string) $usuario['nombre'],

        'rol' => (string) $usuario['rol_nombre'],

        'rol_id' => (int) ($usuario['rol_id'] ?? 0)

    ];
}

/* ==========================================================
   USUARIO AUTENTICADO
========================================================== */

function usuarioAutenticado(): bool
{
    return isset($_SESSION[SESSION_USER]);
}

/* ==========================================================
   USUARIO ACTUAL
========================================================== */

function usuarioActual(): ?array
{
    return $_SESSION[SESSION_USER] ?? null;
}

/* ==========================================================
   ID
========================================================== */

function usuarioId(): ?int
{
    return usuarioActual()['id'] ?? null;
}

/* ==========================================================
   NOMBRE
========================================================== */

function usuarioNombre(): ?string
{
    return usuarioActual()['nombre'] ?? null;
}

/* ==========================================================
   ROL
========================================================== */

function usuarioRol(): ?string
{
    return usuarioActual()['rol'] ?? null;
}

/* ==========================================================
   ROL ID
========================================================== */

function usuarioRolId(): ?int
{
    return usuarioActual()['rol_id'] ?? null;
}

/* ==========================================================
   ADMIN
========================================================== */

function esAdmin(): bool
{
    return usuarioRol() === 'ADMIN';
}

/* ==========================================================
   REGENERAR SESIÓN
========================================================== */

function regenerarSesion(): void
{
    session_regenerate_id(true);
}

/* ==========================================================
   CERRAR SESIÓN
========================================================== */

function cerrarSesion(): void
{
    $_SESSION = [];

    session_unset();

    if (ini_get('session.use_cookies')) {

        $params = session_get_cookie_params();

        setcookie(

            session_name(),

            '',

            time() - 42000,

            $params['path'],

            $params['domain'],

            $params['secure'],

            $params['httponly']

        );

    }

    session_destroy();
}