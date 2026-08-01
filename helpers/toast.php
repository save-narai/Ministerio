<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| HELPER TOAST
|--------------------------------------------------------------------------
|
| Gestiona las notificaciones tipo Toast.
|
| Responsabilidades:
| - Almacenar temporalmente un mensaje en sesión.
| - Imprimir el JavaScript necesario para mostrar el Toast.
|
*/

/* ==========================================================
   GUARDAR TOAST
========================================================== */

function setToast(string $mensaje): void
{
    $_SESSION['error'] = $mensaje;
}

/* ==========================================================
   MOSTRAR TOAST
========================================================== */

function showToastJS(): void
{
    if (empty($_SESSION['error'])) {
        return;
    }

    $mensaje = addslashes((string) $_SESSION['error']);

    unset($_SESSION['error']);

    echo <<<HTML
<script>
document.addEventListener('DOMContentLoaded', () => {
    showToast('{$mensaje}');
});
</script>
HTML;
}