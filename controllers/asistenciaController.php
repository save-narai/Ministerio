<?php

declare(strict_types=1);

require_once __DIR__ . '/controller.php';
require_once __DIR__ . '/../services/asistenciaService.php';

controllerInit();

$pdo = controllerPdo();

controllerRun(

    [

        /* =====================================================
           GUARDAR ASISTENCIA
        ===================================================== */

        'guardar_asistencia' => function () use ($pdo) {

            guardarAsistencia(
                $pdo,
                $_POST
            );

            return controllerRedirect(

                '../views/reuniones/ver.php?id=' . (int) $_POST['reunion_id'],

                'Asistencia guardada correctamente.'

            );

        }

    ],

    [

        'redirect' => '../views/reuniones/index.php'

    ]

);