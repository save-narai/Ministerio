<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| HELPER VALIDATION
|--------------------------------------------------------------------------
|
| Funciones auxiliares para validar y normalizar datos.
|
| Responsabilidades:
| - Sanitizar texto.
| - Validar nombres, teléfonos y correos.
| - Validar fechas, edades e identificadores.
| - Validar tipos de datos comunes.
|
*/

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

function normalizarNombrePersona(string $nombre): string
{
    $nombre = preg_replace('/\s+/u', ' ', trim($nombre)) ?? '';

    return $nombre === '' ? '' : mb_convert_case(mb_strtolower($nombre, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
}

function validarNombre(string $nombre): array
{
    $nombre = normalizarNombrePersona($nombre);

    if (!preg_match('/^[\p{L}\'\- ]+$/u', $nombre)) {

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
    $telefono = preg_replace('/\D/', '', $telefono);

    if (!preg_match('/^3\d{9}$/', $telefono)) {

        return [
            false,
            "Teléfono inválido"
        ];
    }

    if (preg_match('/^(\d)\1+$/', $telefono)) {

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
    return !empty($fecha) && strtotime($fecha) !== false;
}


/* ======================================================
   VALIDAR GÉNERO
====================================================== */

function validarGenero(?string $genero): bool
{
    if (empty($genero)) {
        return true;
    }

    return in_array(
        $genero,
        ['M', 'F'],
        true
    );
}


/* ======================================================
   VALIDAR ENTERO POSITIVO
====================================================== */

function validarEntero(mixed $numero): bool
{
    return
        filter_var($numero, FILTER_VALIDATE_INT) !== false
        && $numero >= 0;
}


/* ======================================================
   VALIDAR ID
====================================================== */

function validarId(mixed $id): bool
{
    return
        filter_var($id, FILTER_VALIDATE_INT) !== false
        && $id > 0;
}


/* ======================================================
   VALIDAR EDAD
====================================================== */

function validarEdad(mixed $edad): bool
{
    if (!validarEntero($edad)) {
        return false;
    }

    return $edad >= 0 && $edad <= 120;
}


/* ======================================================
   VALIDAR EMAIL
====================================================== */

function validarEmail(string $email): bool
{
    return filter_var(
        $email,
        FILTER_VALIDATE_EMAIL
    ) !== false;
}


/* ======================================================
   VALIDAR CAMPO REQUERIDO
====================================================== */

function requerido(mixed $valor): bool
{
    return isset($valor)
        && trim((string) $valor) !== '';
}


/* ======================================================
   VALIDAR BOOLEANO
====================================================== */

function validarBoolean(mixed $valor): bool
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

function validarArray(mixed $array): bool
{
    return is_array($array)
        && count($array) > 0;
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
