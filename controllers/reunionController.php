<?php

declare(strict_types=1);

require_once __DIR__ . '/controller.php';
require_once __DIR__ . '/../services/reunionService.php';

controllerInit();

$pdo = controllerPdo();

controllerRun(

    [

        'crear_reunion' => function () use ($pdo) {

            crearReunion(
                $pdo,
                $_POST
            );

            return controllerSuccess(
                'Reunión creada correctamente.'
            );

        },

        'actualizar_reunion' => function () use ($pdo) {

            actualizarReunion(
                $pdo,
                $_POST
            );

            return controllerSuccess(
                'Reunión actualizada correctamente.'
            );

        },

        'eliminar_reunion' => function () use ($pdo) {

            eliminarReunion(
                $pdo,
                (int) ($_POST['id'] ?? 0)
            );

            return controllerSuccess(
                'Reunión eliminada correctamente.'
            );

        }

    ],

    [

        'redirect' => '../views/reuniones/index.php'

    ]

);