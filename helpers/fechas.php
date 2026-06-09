<?php
function formatearFecha($fecha)
{
    return !empty($fecha)
        ? date("d/m/Y", strtotime($fecha))
        : "-";
}

/**
 * Edad inteligente:
 * - Si hay fecha → calcula real
 * - Si no → usa edad manual
 */
function obtenerEdad($fechaNacimiento = null, $edadManual = null)
{
    if (!empty($fechaNacimiento)) {
        $edad = (new DateTime($fechaNacimiento))
            ->diff(new DateTime())
            ->y;

        return [
            "edad" => $edad,
            "tipo" => "real"
        ];
    }

    return [
        "edad" => $edadManual,
        "tipo" => "estimada"
    ];
}