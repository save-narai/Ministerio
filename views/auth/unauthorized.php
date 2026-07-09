<?php

declare(strict_types=1);

http_response_code(403);

?>
<!DOCTYPE html>

<html lang="es">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>

Acceso denegado

</title>

<link
    rel="stylesheet"
    href="<?= BASE_URL ?>/assets/css/modules/auth/login.css"
>

</head>

<body class="auth auth-verify">
    
<div class="auth-message">

    <h1>

        Acceso denegado

    </h1>

    <p>

        No tienes permisos para acceder a esta página.

    </p>

    <a
        href="<?= BASE_URL ?>/views/dashboard.php"
        class="btn btn-primary"
    >

        Ir al Dashboard

    </a>

</div>

</body>

</html>
