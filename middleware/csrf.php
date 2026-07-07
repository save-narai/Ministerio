<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {

    session_start();

}

/* ==========================================================
   GENERAR TOKEN
========================================================== */

function generarCSRF(): string
{
    if (empty($_SESSION['csrf_token'])) {

        $_SESSION['csrf_token'] = bin2hex(
            random_bytes(32)
        );

    }

    return $_SESSION['csrf_token'];
}

/* ==========================================================
   CAMPO OCULTO
========================================================== */

function csrfField(): string
{
    return sprintf(

        '<input type="hidden" name="csrf_token" value="%s">',

        htmlspecialchars(
            generarCSRF(),
            ENT_QUOTES,
            'UTF-8'
        )

    );
}

/* ==========================================================
   VALIDAR TOKEN
========================================================== */

function validarCSRF(): void
{
    $token = $_POST['csrf_token'] ?? '';

    if (

        empty($token)

        ||

        empty($_SESSION['csrf_token'])

        ||

        !hash_equals(
            $_SESSION['csrf_token'],
            $token
        )

    ) {

        http_response_code(403);

        exit('Token CSRF inválido.');

    }

}

/* ==========================================================
   REGENERAR TOKEN
========================================================== */

function regenerarCSRF(): void
{
    $_SESSION['csrf_token'] = bin2hex(
        random_bytes(32)
    );
}