<?php

declare(strict_types=1);

require_once __DIR__ . '/controller.php';
require_once __DIR__ . '/../services/rolService.php';

controllerInit();

$pdo = controllerPdo();

controllerRun(

    [

        'crear_rol' => function () use ($pdo) {

            crearRol($pdo);

            return controllerSuccess(
                'Rol creado correctamente.'
            );

        },

        'editar_rol' => function () use ($pdo) {

            editarRol($pdo);

            return controllerSuccess(
                'Rol actualizado correctamente.'
            );

        },

        'guardar_permisos' => function () use ($pdo) {

            guardarPermisosRol($pdo);

            return controllerSuccess(
                'Permisos actualizados correctamente.'
            );

        },

        'eliminar_rol' => function () use ($pdo) {

            eliminarRol($pdo);

            return controllerSuccess(
                'Rol eliminado correctamente.'
            );

        }

    ],

    [

        'redirect' => '../views/roles/index.php'

    ]

);