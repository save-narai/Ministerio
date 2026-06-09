
<?php

/* ======================================================
   REDIRECCIONAR ERROR
====================================================== */

function errorResponse($ruta, $mensaje){

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $_SESSION["error"] = $mensaje;

    header("Location: $ruta");

    exit;
}

/* ======================================================
   REDIRECCIONAR SUCCESS
====================================================== */

function successResponse($ruta, $mensaje){

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $_SESSION["success"] = $mensaje;

    header("Location: $ruta");

    exit;
}

/* ======================================================
   RESPUESTA JSON
====================================================== */

function jsonResponse($data = [], $codigo = 200){

    http_response_code($codigo);

    header('Content-Type: application/json');

    echo json_encode($data);

    exit;
}

/* ======================================================
   VALIDAR CSRF
====================================================== */

function validarCsrf(){

    if (
        !isset($_POST["csrf_token"]) ||
        !isset($_SESSION["csrf_token"]) ||
        $_POST["csrf_token"] !== $_SESSION["csrf_token"]
    ){
        die("Token CSRF inválido");
    }
}

/* ======================================================
   VALIDAR PERMISO
====================================================== */

function validarPermiso($permiso){

    if (!tienePermiso($permiso)) {

        die("Acceso denegado.");
    }
}