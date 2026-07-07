<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* =========================================================
   INICIAR SESIÓN DE USUARIO
========================================================= */

function iniciarSesionUsuario(array $usuario): void
{
    session_regenerate_id(true);

    $_SESSION["user_id"] = (int)$usuario["id"];
    $_SESSION["nombre"]  = $usuario["nombre"];
    $_SESSION["rol"]     = $usuario["rol_nombre"];
}

/* =========================================================
   USUARIO AUTENTICADO
========================================================= */

function usuarioAutenticado(): bool
{
    return !empty($_SESSION["user_id"]);
}

/* =========================================================
   USUARIO ACTUAL
========================================================= */

function usuarioActual(): array
{
    return [

        "id" => $_SESSION["user_id"] ?? null,

        "nombre" => $_SESSION["nombre"] ?? null,

        "rol" => $_SESSION["rol"] ?? null

    ];
}

/* =========================================================
   OBTENER ID
========================================================= */

function usuarioId(): ?int
{
    return $_SESSION["user_id"] ?? null;
}

/* =========================================================
   OBTENER NOMBRE
========================================================= */

function usuarioNombre(): ?string
{
    return $_SESSION["nombre"] ?? null;
}

/* =========================================================
   OBTENER ROL
========================================================= */

function usuarioRol(): ?string
{
    return $_SESSION["rol"] ?? null;
}

/* =========================================================
   ES ADMIN
========================================================= */

function esAdmin(): bool
{
    return usuarioRol() === "ADMIN";
}

/* =========================================================
   REGENERAR SESIÓN
========================================================= */

function regenerarSesion(): void
{
    session_regenerate_id(true);
}

/* =========================================================
   CERRAR SESIÓN
========================================================= */

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