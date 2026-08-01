<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| HELPER CSRF
|--------------------------------------------------------------------------
|
| Gestiona la protección CSRF del sistema.
|
| Responsabilidades:
| - Generar el token.
| - Generar el campo oculto del formulario.
| - Validar el token recibido.
| - Regenerar el token tras una validación correcta.
|
*/

const CSRF_TOKEN_KEY = 'csrf_token';

/* ==========================================================
   GENERAR TOKEN
========================================================== */

function generarCsrf(): string
{
    if (empty($_SESSION[CSRF_TOKEN_KEY])) {

        $_SESSION[CSRF_TOKEN_KEY] = bin2hex(
            random_bytes(32)
        );
    }

    return $_SESSION[CSRF_TOKEN_KEY];
}

/* ==========================================================
   CAMPO OCULTO
========================================================== */

function csrfField(): string
{
    return sprintf(
        '<input type="hidden" name="csrf_token" value="%s">',
        htmlspecialchars(
            generarCsrf(),
            ENT_QUOTES,
            'UTF-8'
        )
    );
}

/* ==========================================================
   VALIDAR TOKEN
========================================================== */

function validarCsrf(): void
{
    $token = $_POST['csrf_token'] ?? '';

    if (
        empty($_SESSION[CSRF_TOKEN_KEY]) ||
        empty($token) ||
        !hash_equals(
            $_SESSION[CSRF_TOKEN_KEY],
            (string) $token
        )
    ) {

        throw new Exception(
            'Token CSRF inválido.'
        );
    }

    regenerarCsrf();
}

/* ==========================================================
   REGENERAR TOKEN
========================================================== */

function regenerarCsrf(): void
{
    $_SESSION[CSRF_TOKEN_KEY] = bin2hex(
        random_bytes(32)
    );
}