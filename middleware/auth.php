<?php

require_once __DIR__ . "/../services/SessionService.php";

/* =========================================================
   EXIGIR AUTENTICACIÓN
========================================================= */

function exigirAutenticacion(): void
{
    if (!usuarioAutenticado()) {

        header("Location: ../index.php");

        exit;

    }
}