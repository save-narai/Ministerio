<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| HELPER RESPONSE
|--------------------------------------------------------------------------
|
| Gestiona respuestas HTTP del sistema.
|
| Responsabilidades:
| - Enviar respuestas JSON.
|
| Las redirecciones, mensajes flash y la validación CSRF se
| gestionan mediante sus Helpers específicos.
|
*/

/* ==========================================================
   RESPUESTA JSON
========================================================== */

function jsonResponse(
    array $data = [],
    int $codigo = 200
): void {

    http_response_code($codigo);

    header('Content-Type: application/json; charset=UTF-8');

    echo json_encode(
        $data,
        JSON_UNESCAPED_UNICODE
    );

    exit;

}