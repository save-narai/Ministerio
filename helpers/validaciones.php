<?php

/* ======================================================
   LIMPIAR TEXTO
====================================================== */

function limpiarTexto(string $texto): string
{
    return trim(
        htmlspecialchars(
            $texto,
            ENT_QUOTES,
            'UTF-8'
        )
    );
}


/* ======================================================
   VALIDAR NOMBRE
====================================================== */

function validarNombre(string $nombre): array
{
    $nombre = trim($nombre);

    $nombre = preg_replace(
        '/\s+/',
        ' ',
        $nombre
    );

    if (
        !preg_match(
            '/^[\p{L}\'\- ]+$/u',
            $nombre
        )
    ) {

        return [
            false,
            "Nombre inválido"
        ];
    }

    if (mb_strlen($nombre) < 3) {

        return [
            false,
            "Nombre demasiado corto"
        ];
    }

    $nombre = mb_convert_case(
        $nombre,
        MB_CASE_TITLE,
        "UTF-8"
    );

    return [
        true,
        $nombre
    ];
}


/* ======================================================
   VALIDAR TELÉFONO
====================================================== */

function validarTelefono(string $telefono): array
{
    $telefono = preg_replace(
        '/\D/',
        '',
        $telefono
    );

    /* Debe iniciar en 3 y tener 10 dígitos */

    if (
        !preg_match(
            '/^3\d{9}$/',
            $telefono
        )
    ) {

        return [
            false,
            "Teléfono inválido"
        ];
    }

    /* Evitar teléfonos repetidos */

    if (
        preg_match(
            '/^(\d)\1+$/',
            $telefono
        )
    ) {

        return [
            false,
            "Teléfono inválido"
        ];
    }

    return [
        true,
        $telefono
    ];
}


/* ======================================================
   VALIDAR FECHA
====================================================== */

function validarFecha(?string $fecha): bool
{
    if (
        empty($fecha) ||
        !strtotime($fecha)
    ) {

        return false;
    }

    return true;
}


/* ======================================================
   VALIDAR GÉNERO
====================================================== */

function validarGenero(?string $genero): bool
{
    $permitidos = ['M', 'F'];

    /* Vacío permitido */

    if (empty($genero)) {

        return true;
    }

    return in_array(
        $genero,
        $permitidos,
        true
    );
}


/* ======================================================
   VALIDAR ENTERO POSITIVO
====================================================== */

function validarEntero($numero): bool
{
    return (
        filter_var(
            $numero,
            FILTER_VALIDATE_INT
        ) !== false
        &&
        $numero >= 0
    );
}


/* ======================================================
   VALIDAR ID
====================================================== */

function validarId($id): bool
{
    return (
        filter_var(
            $id,
            FILTER_VALIDATE_INT
        ) !== false
        &&
        $id > 0
    );
}


/* ======================================================
   VALIDAR EDAD
====================================================== */

function validarEdad($edad): bool
{
    if (!validarEntero($edad)) {

        return false;
    }

    return (
        $edad >= 0 &&
        $edad <= 120
    );
}


/* ======================================================
   VALIDAR EMAIL
====================================================== */

function validarEmail(string $email): bool
{
    return (
        filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        ) !== false
    );
}


/* ======================================================
   VALIDAR CAMPO REQUERIDO
====================================================== */

function requerido($valor): bool
{
    return (
        isset($valor)
        &&
        trim((string)$valor) !== ''
    );
}


/* ======================================================
   VALIDAR BOOLEANO
====================================================== */

function validarBoolean($valor): bool
{
    return in_array(
        $valor,
        [0, 1, '0', '1', true, false],
        true
    );
}


/* ======================================================
   VALIDAR ARRAY NO VACÍO
====================================================== */

function validarArray($array): bool
{
    return (
        is_array($array)
        &&
        count($array) > 0
    );
}

/* ======================================================
   VALIDAR VALOR EN ARRAY
====================================================== */

function validarOpcion(
    string $valor,
    array $permitidos
): bool {

    return in_array(
        $valor,
        $permitidos,
        true
    );
}