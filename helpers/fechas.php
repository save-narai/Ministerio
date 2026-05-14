<?php

function formatearFecha($fecha) {
    return !empty($fecha) ? date("d/m/Y", strtotime($fecha)) : "-";
}

function calcularEdad($fechaNacimiento) {
    if (empty($fechaNacimiento)) return null;

    return (new DateTime($fechaNacimiento))
        ->diff(new DateTime())
        ->y;
}