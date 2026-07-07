<?php

declare(strict_types=1);

require_once __DIR__ . "/auth.php";

/* ==========================================================
   PERMITIR SOLO INVITADOS
========================================================== */

function exigirInvitado(): void
{
    if (!usuarioAutenticado()) {
        return;
    }

    header("Location: " . BASE_URL . "/views/dashboard.php");
    exit;
}