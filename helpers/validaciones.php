
<?php

/* ======================================================
   LIMPIAR TEXTO
====================================================== */

function limpiarTexto($texto){

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

function validarNombre($nombre){

    $nombre = trim($nombre);

    $nombre = preg_replace('/\s+/', ' ', $nombre);

    if (
        !preg_match(
            '/^[\p{L}\'\- ]+$/u',
            $nombre
        )
    ){
        return [
            false,
            "Nombre inválido"
        ];
    }

    if (mb_strlen($nombre) < 3){

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

function validarTelefono($telefono){

    $telefono = preg_replace(
        '/\D/',
        '',
        $telefono
    );

    /*
    |--------------------------------------------------------------------------
    | Debe iniciar en 3 y tener 10 dígitos
    */

    if (
        !preg_match(
            '/^3\d{9}$/',
            $telefono
        )
    ){
        return [
            false,
            "Teléfono inválido"
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Bloquear repetidos
    */

    if (
        preg_match(
            '/^(\d)\1+$/',
            $telefono
        )
    ){
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

function validarFecha($fecha){

    if (
        empty($fecha)
        ||
        !strtotime($fecha)
    ){
        return false;
    }

    return true;
}

/* ======================================================
   VALIDAR GÉNERO
====================================================== */

function validarGenero($genero){

    $permitidos = ['M', 'F'];

    /*
    |--------------------------------------------------------------------------
    | Vacío permitido
    */

    if (empty($genero)){

        return true;
    }

    return in_array(
        $genero,
        $permitidos
    );
}

/* ======================================================
   VALIDAR ENTERO POSITIVO
====================================================== */

function validarEntero($numero){

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
   VALIDAR EDAD
====================================================== */

function validarEdad($edad){

    if (
        !validarEntero($edad)
    ){
        return false;
    }

    return (
        $edad >= 0
        &&
        $edad <= 120
    );
}

/* ======================================================
   VALIDAR EMAIL
====================================================== */

function validarEmail($email){

    return filter_var(
        $email,
        FILTER_VALIDATE_EMAIL
    );
}

/* ======================================================
   VALIDAR STRING REQUERIDO
====================================================== */

function requerido($valor){

    return (
        isset($valor)
        &&
        trim($valor) !== ''
    );
}

/* ======================================================
   VALIDAR BOOLEANO
====================================================== */

function validarBoolean($valor){

    return in_array(
        $valor,
        [0, 1, '0', '1', true, false],
        true
    );
}