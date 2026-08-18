<?php

declare(strict_types=1);

require_once __DIR__ . '/controller.php';
require_once __DIR__ . '/../services/excepcionSeguimientoService.php';

controllerInit();


/* ==========================================================
   CONSULTAR EXCEPCIÓN PARA EDICIÓN
   ----------------------------------------------------------
   Esta acción es de lectura y se consume mediante AJAX.
   No pasa por controllerRun() porque controllerRun()
   actualmente trabaja con POST + respuesta por redirect.
========================================================== */

if (
    strtoupper($_SERVER['REQUEST_METHOD'] ?? '') === 'GET'
    &&
    (
        $_GET['action'] ?? ''
    ) === 'obtener_excepcion_seguimiento'
) {

    header(
        'Content-Type: application/json; charset=utf-8'
    );

    try {

        if (
            !tienePermiso(
                'gestionar_seguimientos'
            )
        ) {

            http_response_code(403);

            echo json_encode([

                'success' =>
                    false,

                'message' =>
                    'No tienes permiso para consultar esta excepción.'

            ]);

            exit;
        }


        $pdo =
            controllerPdo();


        $id =
            (int)(
                $_GET['id']
                ?? 0
            );


        if (
            $id <= 0
        ) {

            http_response_code(400);

            echo json_encode([

                'success' =>
                    false,

                'message' =>
                    'ID de excepción inválido.'

            ]);

            exit;
        }


        $excepcion =
            obtenerExcepcionSeguimientoPorId(
                $pdo,
                $id
            );


        if (
            !$excepcion
        ) {

            http_response_code(404);

            echo json_encode([

                'success' =>
                    false,

                'message' =>
                    'La excepción no existe.'

            ]);

            exit;
        }


        echo json_encode([

            'success' =>
                true,

            'data' =>
                $excepcion

        ]);

        exit;


    } catch (Throwable $e) {

        controllerLog(
            $e
        );


        http_response_code(500);

        echo json_encode([

            'success' =>
                false,

            'message' =>
                'No se pudo consultar la excepción.'

        ]);

        exit;
    }
}


/* ==========================================================
   CONTROLLER NORMAL
========================================================== */

$pdo =
    controllerPdo();


controllerRun(

    [

        /* ==================================================
           CREAR EXCEPCIÓN DE SEGUIMIENTO
        ================================================== */

        'crear_excepcion_seguimiento' =>
            function () use ($pdo) {

                $datos =
                    $_POST;


                $datos['anio'] =
                    (int)date('Y');


                $datos['mes'] =
                    (int)date('m');


                crearExcepcionSeguimiento(
                    $pdo,
                    $datos
                );


                return controllerRedirect(

                    '../views/seguimientos/index.php',

                    'Excepción de seguimiento registrada correctamente.'

                );
            },


        /* ==================================================
           ACTUALIZAR EXCEPCIÓN DE SEGUIMIENTO
        ================================================== */

        'actualizar_excepcion_seguimiento' =>
            function () use ($pdo) {

                $id =
                    (int)(
                        $_POST['id']
                        ?? 0
                    );


                $datos =
                    $_POST;


                $datos['anio'] =
                    (int)(
                        $_POST['anio']
                        ?? date('Y')
                    );


                $datos['mes'] =
                    (int)(
                        $_POST['mes']
                        ?? date('m')
                    );


                actualizarExcepcionSeguimiento(

                    $pdo,

                    $id,

                    $datos

                );


                return controllerRedirect(

                    '../views/seguimientos/index.php',

                    'Excepción de seguimiento actualizada correctamente.'

                );
            },


        /* ==================================================
           ELIMINAR EXCEPCIÓN DE SEGUIMIENTO
        ================================================== */

        'eliminar_excepcion_seguimiento' =>
            function () use ($pdo) {

                $id =
                    (int)(
                        $_POST['id']
                        ?? 0
                    );


                eliminarExcepcionSeguimiento(

                    $pdo,

                    $id

                );


                return controllerRedirect(

                    '../views/seguimientos/index.php',

                    'Excepción eliminada correctamente.'

                );
            }

    ],

    [

        'redirect' =>
            '../views/seguimientos/index.php'

    ]

);