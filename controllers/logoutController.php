<?php

declare(strict_types=1);

require_once __DIR__ . '/controller.php';

require_once __DIR__ . '/../services/authService.php';

controllerInit();

controllerRun(

    [

        'logout' => function () {

            logoutUsuario();

            return controllerRedirect(
                '../index.php',
                'Has cerrado sesión correctamente.'
            );

        }

    ],

    [

        'redirect' => '../views/dashboard.php',

        // Si el logout se ejecuta desde un enlace GET,
        // descomenta la siguiente línea:
        // 'method' => 'GET',

        // Si no envías token CSRF en el logout,
        // descomenta también:
        // 'csrf' => false,

    ]

);