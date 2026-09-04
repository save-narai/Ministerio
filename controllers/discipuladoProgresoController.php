<?php

declare(strict_types=1);

require_once __DIR__ . '/controller.php';
require_once __DIR__ . '/../services/discipuladoService.php';

controllerInit();

$pdo = controllerPdo();

controllerRun(

    [

        'completar_clase_progreso_discipulado' => function () use ($pdo) {

            controllerRequirePermission('gestionar_reuniones');

            $cicloId = (int)($_POST['ciclo_id'] ?? 0);

            $inscripcionId = (int)($_POST['inscripcion_id'] ?? 0);

            $claseId = (int)($_POST['clase_id'] ?? 0);

            completarClaseProgresoDiscipulado(
                $pdo,
                $cicloId,
                $inscripcionId,
                $claseId,
                $_POST
            );

            return controllerRedirect(
                '../views/formacion/discipulado/participantes/progreso.php?ciclo_id=' . $cicloId . '&id=' . $inscripcionId,
                'Clase registrada correctamente.'
            );

        },

        'revertir_progreso_clase_discipulado' => function () use ($pdo) {

            controllerRequirePermission('gestionar_reuniones');

            $cicloId = (int)($_POST['ciclo_id'] ?? 0);

            $inscripcionId = (int)($_POST['inscripcion_id'] ?? 0);

            $claseId = (int)($_POST['clase_id'] ?? 0);

            revertirProgresoClaseDiscipulado(
                $pdo,
                $cicloId,
                $inscripcionId,
                $claseId
            );

            return controllerRedirect(
                '../views/formacion/discipulado/participantes/progreso.php?ciclo_id=' . $cicloId . '&id=' . $inscripcionId,
                'Clase revertida a pendiente.'
            );

        },

        'actualizar_checklist_ciclo_discipulado' => function () use ($pdo) {

            controllerRequirePermission('gestionar_reuniones');
            $cicloId = (int)($_POST['ciclo_id'] ?? 0);
            $inscripcionId = (int)($_POST['inscripcion_id'] ?? 0);
            $claseId = (int)($_POST['clase_id'] ?? 0);
            $estado = strtoupper((string)($_POST['estado'] ?? 'PENDIENTE'));
            $actual = obtenerProgresoClaseInscripcion($pdo, $inscripcionId, $claseId);

            if ($actual) {
                revertirProgresoClaseDiscipulado($pdo, $cicloId, $inscripcionId, $claseId);
            }
            if ($estado !== 'PENDIENTE') {
                completarClaseProgresoDiscipulado($pdo, $cicloId, $inscripcionId, $claseId, [
                    'modalidad' => $estado === 'VIRTUAL' ? 'VIRTUAL' : 'PRESENCIAL',
                    'fecha' => date('Y-m-d'),
                    'es_recuperacion' => $estado === 'RECUPERAR' ? '1' : '',
                ]);
            }
            return controllerRedirect('../views/formacion/discipulado/ver.php?ciclo_id=' . $cicloId . '#seguimiento', 'Checklist actualizado correctamente.');

        }

    ],

    [

        'redirect' => '../views/formacion/discipulado/index.php'

    ]

);
