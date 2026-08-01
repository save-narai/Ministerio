<?php

declare(strict_types=1);

require_once __DIR__ . '/controller.php';
require_once __DIR__ . '/../services/jovenService.php';

controllerInit();

$pdo = controllerPdo();

controllerRun(

    [

        /* ==================================================
           CREAR JOVEN
        =================================================== */

        'crear_joven' => function () use ($pdo) {

            crearJoven(
                $pdo,
                $_POST
            );

            return controllerSuccess(
                'Joven creado correctamente.'
            );

        },

        /* ==================================================
           EDITAR JOVEN
        =================================================== */

        'editar_joven' => function () use ($pdo) {

            editarJoven(
                $pdo,
                (int) $_POST['id'],
                $_POST
            );

            return controllerSuccess(
                'Joven actualizado correctamente.'
            );

        },

        /* ==================================================
           ELIMINAR JOVEN
        =================================================== */

        'eliminar_joven' => function () use ($pdo) {

            eliminarJoven(
                $pdo,
                (int) $_POST['id']
            );

            return controllerSuccess(
                'Joven eliminado correctamente.'
            );

        },

        /* ==================================================
           RECUPERAR JOVEN
        =================================================== */

        'recuperar_joven' => function () use ($pdo) {

            recuperarJoven(
                $pdo,
                (int) $_POST['id']
            );

            return controllerRedirect(
                '../views/jovenes/index.php?filtro=eliminados',
                'Joven recuperado correctamente.'
            );

        },

        /* ==================================================
           ELIMINAR DEFINITIVAMENTE
        =================================================== */

        'eliminar_definitivo' => function () use ($pdo) {

            eliminarDefinitivo(
                $pdo,
                (int) $_POST['id']
            );

            return controllerRedirect(
                '../views/jovenes/index.php?filtro=eliminados',
                'Joven eliminado definitivamente.'
            );

        }

    ],

    [

        'redirect' => '../views/jovenes/index.php'

    ]

);