<?php

declare(strict_types=1);

require_once __DIR__ . '/controller.php';
require_once __DIR__ . '/../services/seguimientoService.php';


/* ==========================================================
   INICIALIZAR
========================================================== */

controllerInit();

$pdo = controllerPdo();


/* ==========================================================
   CONTROLADOR DE SEGUIMIENTOS
========================================================== */

controllerRun(

    [

        /* ==================================================
           CREAR SEGUIMIENTO
        ================================================== */

        'crear_seguimiento' => function () use ($pdo) {

            $jovenId =
                (int)(
                    $_POST['joven_id']
                    ?? 0
                );


            if ($jovenId <= 0) {

                throw new Exception(
                    'El joven seleccionado no es válido.'
                );
            }


            /*
             * Toda la lógica real queda en:
             *
             * services/seguimientoService.php
             *
             * Allí se:
             *
             * - valida el seguimiento.
             * - determina el usuario.
             * - busca la asignación activa.
             * - registra el seguimiento.
             * - actualiza el joven.
             * - completa la asignación si FINALIZADO.
             * - genera la notificación correspondiente.
             */

            crearSeguimiento(
                $pdo,
                $_POST
            );


            return controllerRedirect(

                '../views/jovenes/ver.php?id='
                . $jovenId,

                'Seguimiento registrado correctamente.'
            );
        },


        /* ==================================================
           ELIMINAR SEGUIMIENTO
        ================================================== */

        'eliminar_seguimiento' => function () use ($pdo) {

            $id =
                (int)(
                    $_POST['id']
                    ?? 0
                );


            if ($id <= 0) {

                throw new Exception(
                    'El seguimiento seleccionado no es válido.'
                );
            }


            /*
             * Recuperamos primero el seguimiento
             * para saber a qué joven regresar.
             */

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


            eliminarSeguimiento(
                $pdo,
                $id
            );


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