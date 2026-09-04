<?php

declare(strict_types=1);

require_once __DIR__ . '/controller.php';
require_once __DIR__ . '/../services/discipuladoService.php';

controllerInit();

$pdo = controllerPdo();

controllerRun(

    [

        'crear_clase_discipulado' => function () use ($pdo) {

            controllerRequirePermission('gestionar_reuniones');

            crearClaseDiscipulado(
                $pdo,
                $_POST
            );

            $cicloId = (int)($_POST['ciclo_id'] ?? 0);

            return controllerRedirect(
                '../views/formacion/discipulado/ver.php?ciclo_id=' . $cicloId,
                'Clase creada correctamente.'
            );

        },

        'actualizar_clase_discipulado' => function () use ($pdo) {

            controllerRequirePermission('gestionar_reuniones');

            editarClaseDiscipulado(
                $pdo,
                $_POST
            );

            $cicloId = (int)($_POST['ciclo_id'] ?? 0);

            return controllerRedirect(
                '../views/formacion/discipulado/ver.php?ciclo_id=' . $cicloId,
                'Clase actualizada correctamente.'
            );

        },

        'cambiar_estado_clase_discipulado' => function () use ($pdo) {

            controllerRequirePermission('gestionar_reuniones');

            $cicloId = (int)($_POST['ciclo_id'] ?? 0);

            cambiarEstadoClaseDiscipulado(
                $pdo,
                $cicloId,
                (int)($_POST['id'] ?? 0),
                (string)($_POST['estado'] ?? '')
            );

            return controllerRedirect(
                '../views/formacion/discipulado/ver.php?ciclo_id=' . $cicloId,
                'Estado de la clase actualizado correctamente.'
            );

        },

        'eliminar_clase_discipulado' => function () use ($pdo) {

            controllerRequirePermission('gestionar_reuniones');

            $cicloId = (int)($_POST['ciclo_id'] ?? 0);

            eliminarClaseDiscipulado(
                $pdo,
                $cicloId,
                (int)($_POST['id'] ?? 0)
            );

            return controllerRedirect(
                '../views/formacion/discipulado/ver.php?ciclo_id=' . $cicloId,
                'Clase eliminada correctamente.'
            );

        },

        'asignar_profesor_clase_discipulado' => function () use ($pdo) {

            controllerRequirePermission('gestionar_reuniones');
            $cicloId = (int)($_POST['ciclo_id'] ?? 0);
            asignarProfesorClaseDiscipulado($pdo, $cicloId, (int)($_POST['id'] ?? 0), !empty($_POST['profesor_id']) ? (int)$_POST['profesor_id'] : null);
            return controllerRedirect('../views/formacion/discipulado/ver.php?ciclo_id=' . $cicloId, 'Profesor actualizado correctamente.');

        }

    ],

    [

        'redirect' => '../views/formacion/discipulado/index.php'

    ]

);
