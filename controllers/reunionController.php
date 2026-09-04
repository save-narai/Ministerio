<?php

declare(strict_types=1);

require_once __DIR__ . '/controller.php';
require_once __DIR__ . '/../services/reunionService.php';
require_once __DIR__ . '/../services/discipuladoService.php';

controllerInit();

$pdo = controllerPdo();

controllerRun(

    [

        'crear_reunion' => function () use ($pdo) {

            /* --------------------------------------------------
               FASE 7: se reemplaza la llamada directa a
               crearReunion() por crearReunionDiscipulado(),
               que reutiliza crearReunion() TAL CUAL por dentro
               y además vincula ciclo/clase cuando el tipo es
               Discipulado. Para cualquier otro tipo de
               reunión, el comportamiento es exactamente el
               mismo que antes.
            -------------------------------------------------- */

            crearReunionDiscipulado(
                $pdo,
                $_POST
            );

            return controllerSuccess(
                'Reunión creada correctamente.'
            );

        },

        'actualizar_reunion' => function () use ($pdo) {

            actualizarReunionDiscipulado(
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