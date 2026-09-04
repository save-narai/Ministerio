<?php

declare(strict_types=1);

require_once __DIR__ . '/controller.php';
require_once __DIR__ . '/../services/discipuladoService.php';

controllerInit();

$pdo = controllerPdo();

controllerRun(

    [

        'crear_ciclo_discipulado' => function () use ($pdo) {

            controllerRequirePermission('gestionar_reuniones');

            $id = crearCicloDiscipulado(
                $pdo,
                $_POST
            );

            return controllerRedirect(
                '../views/formacion/discipulado/ver.php?ciclo_id=' . $id,
                'Ciclo de discipulado creado correctamente.'
            );

        },

        'actualizar_ciclo_discipulado' => function () use ($pdo) {

            controllerRequirePermission('gestionar_reuniones');

            editarCicloDiscipulado(
                $pdo,
                $_POST
            );

            $cicloId = (int)($_POST['id'] ?? 0);

            return controllerRedirect(
                '../views/formacion/discipulado/ver.php?ciclo_id=' . $cicloId,
                'Ciclo de discipulado actualizado correctamente.'
            );

        },

        'cambiar_estado_ciclo_discipulado' => function () use ($pdo) {

            controllerRequirePermission('gestionar_reuniones');

            cambiarEstadoCicloDiscipulado(
                $pdo,
                (int)($_POST['id'] ?? 0),
                (string)($_POST['estado'] ?? '')
            );

            return controllerSuccess(
                'Estado del ciclo actualizado correctamente.'
            );

        }

    ],

    [

        'redirect' => '../views/formacion/discipulado/index.php'

    ]

);
