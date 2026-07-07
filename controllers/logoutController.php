<?php

session_start();

require_once __DIR__ . "/../middleware/auth.php";
require_once __DIR__ . "/../helpers/redirect.php";

try {

    cerrarSesion();

    redirect(
        "../index.php",
        "success",
        "Has cerrado sesión correctamente."
    );

} catch (Exception $e) {

    redirect(
        "../views/dashboard.php",
        "error",
        "No fue posible cerrar la sesión."
    );

}