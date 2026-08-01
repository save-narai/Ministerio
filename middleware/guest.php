<?php

declare(strict_types=1);

require_once __DIR__ . "/auth.php";

/* ==========================================================
   EXIGIR INVITADO
========================================================== */

function exigirInvitado(): void
{
    if (!usuarioAutenticado()) {
        return;
    }

    redirect(BASE_URL . "/views/dashboard.php");
}