<?php

declare(strict_types=1);

require_once __DIR__ . '/controller.php';
require_once __DIR__ . '/../services/seguimientoService.php';

controllerInit();

$pdo = controllerPdo();

controllerRun(

    [

        /* ==================================================
           CREAR SEGUIMIENTO
        ================================================== */

        'crear_seguimiento' => function () use ($pdo) {

            crearSeguimiento(
                $pdo,
                $_POST
            );

            return controllerRedirect(

                '../views/jovenes/ver.php?id='
                . (int)($_POST['joven_id'] ?? 0),

                'Seguimiento registrado correctamente.'

            );

        },


        /* ==================================================
           ELIMINAR SEGUIMIENTO
        ================================================== */

        'eliminar_seguimiento' => function () use ($pdo) {

            $id = (int)(
                $_POST['id'] ?? 0
            );


            /* ==========================================
               OBTENER SEGUIMIENTO
            ========================================== */

            $seguimiento =
                obtenerSeguimientoPorId(
                    $pdo,
                    $id
                );


            if (!$seguimiento) {

                throw new Exception(
                    'El seguimiento no existe.'
                );

            }


            /* ==========================================
               ELIMINAR
            ========================================== */

            eliminarSeguimiento(
                $pdo,
                $id
            );


            /* ==========================================
               VOLVER AL JOVEN
            ========================================== */

            return controllerRedirect(

                '../views/jovenes/ver.php?id='
                . (int)$seguimiento['joven_id'],

                'Seguimiento eliminado correctamente.'

            );

        }

    ],

    [

        'redirect' =>
            '../views/jovenes/index.php'

    ]

);