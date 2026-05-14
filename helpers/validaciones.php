<?php

function validarTelefono($telefono) {
    return preg_match('/^3[0-9]{9}$/', $telefono);
}

function validarNombre($nombre) {

    $nombre = trim($nombre);
    $nombre = preg_replace('/\s+/', ' ', $nombre);

    if (!preg_match('/^[\p{L} ]+$/u', $nombre)) {
        return [false, "El nombre solo puede contener letras y espacios"];
    }

    if (mb_strlen($nombre) < 3) {
        return [false, "El nombre es demasiado corto"];
    }

    $nombre = mb_convert_case($nombre, MB_CASE_TITLE, "UTF-8");

    return [true, $nombre];
}