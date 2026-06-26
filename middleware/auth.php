<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* =========================================================
   VERIFICAR AUTENTICACIÓN
========================================================= */

function usuarioAutenticado(): bool
{
    return !empty($_SESSION['user_id']);
}

/* =========================================================
   OBTENER USUARIO ACTUAL
========================================================= */

function usuarioId(): ?int
{
    return $_SESSION['user_id'] ?? null;
}

function usuarioNombre(): ?string
{
    return $_SESSION['nombre'] ?? null;
}

function usuarioRol(): ?string
{
    return $_SESSION['rol'] ?? null;
}

/* =========================================================
   CERRAR SESIÓN
========================================================= */

function cerrarSesion(): void
{
    $_SESSION = [];

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

/* =========================================================
   PROTEGER RUTA
========================================================= */

if (!usuarioAutenticado()) {

    header('Location: ../index.php');
    exit();
}