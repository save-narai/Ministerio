<?php

declare(strict_types=1);

require_once __DIR__ . '/controller.php';

require_once __DIR__ . '/../services/usuarioService.php';

controllerInit();

$pdo = controllerPdo();

controllerRun(

    [

        'toggle_usuario' => function () use ($pdo) {

            $mensaje = toggleUsuario(
                $pdo,
                (int) ($_GET['id'] ?? 0)
            );

            return controllerSuccess($mensaje);

        }

    ],

    [

        'redirect' => '../views/usuarios/index.php',

        'method' => 'GET',

        'csrf' => false

    ]

);