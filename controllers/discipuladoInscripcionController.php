<?php

declare(strict_types=1);

require_once __DIR__ . '/controller.php';
require_once __DIR__ . '/../services/discipuladoService.php';

controllerInit();

$pdo = controllerPdo();

controllerRun(

    [

        'inscribir_joven_discipulado' => function () use ($pdo) {

            controllerRequirePermission('gestionar_reuniones');

            inscribirJovenDiscipulado(
                $pdo,
                $_POST
            );

            $cicloId = (int)($_POST['ciclo_id'] ?? 0);

            return controllerRedirect(
                '../views/formacion/discipulado/ver.php?ciclo_id=' . $cicloId,
                'Joven inscrito correctamente.'
            );

        },

        'cambiar_modalidad_inscripcion_discipulado' => function () use ($pdo) {

            controllerRequirePermission('gestionar_reuniones');

            $cicloId = (int)($_POST['ciclo_id'] ?? 0);

            $inscripcionId = (int)($_POST['id'] ?? 0);

            cambiarModalidadPrincipalInscripcionDiscipulado(
                $pdo,
                $cicloId,
                $inscripcionId,
                (string)($_POST['modalidad_principal'] ?? '')
            );

            return controllerRedirect(
                '../views/formacion/discipulado/participantes/progreso.php?ciclo_id=' . $cicloId . '&id=' . $inscripcionId,
                'Modalidad actualizada correctamente.'
            );

        },

        'cambiar_estado_inscripcion_discipulado' => function () use ($pdo) {

            controllerRequirePermission('gestionar_reuniones');

            $cicloId = (int)($_POST['ciclo_id'] ?? 0);

            $inscripcionId = (int)($_POST['id'] ?? 0);

            cambiarEstadoInscripcionDiscipulado(
                $pdo,
                $cicloId,
                $inscripcionId,
                (string)($_POST['estado'] ?? ''),
                isset($_POST['motivo']) ? (string)$_POST['motivo'] : null
            );

            return controllerRedirect(
                '../views/formacion/discipulado/participantes/progreso.php?ciclo_id=' . $cicloId . '&id=' . $inscripcionId,
                'Estado de la inscripción actualizado correctamente.'
            );

        },

        'agregar_observacion_inscripcion_discipulado' => function () use ($pdo) {

            controllerRequirePermission('gestionar_reuniones');

            $cicloId = (int)($_POST['ciclo_id'] ?? 0);

            $inscripcionId = (int)($_POST['id'] ?? 0);

            if (
                !obtenerInscripcionDiscipuladoDeCiclo(
                    $pdo,
                    $cicloId,
                    $inscripcionId
                )
            ) {

                throw new Exception(
                    'La inscripción no existe o no pertenece a este ciclo.'
                );

            }

            agregarObservacionInscripcionDiscipulado(
                $pdo,
                $inscripcionId,
                (string)($_POST['observacion'] ?? '')
            );

            return controllerRedirect(
                '../views/formacion/discipulado/participantes/progreso.php?ciclo_id=' . $cicloId . '&id=' . $inscripcionId,
                'Observación agregada correctamente.'
            );

        },

        'actualizar_repaso_inscripcion_discipulado' => function () use ($pdo) {

            controllerRequirePermission('gestionar_reuniones');

            $cicloId = (int)($_POST['ciclo_id'] ?? 0);

            $inscripcionId = (int)($_POST['id'] ?? 0);

            actualizarRepasoInscripcionDiscipulado(
                $pdo,
                $cicloId,
                $inscripcionId,
                (int)($_POST['numero_repaso'] ?? 0),
                !empty($_POST['valor'])
            );

            return controllerRedirect(
                '../views/formacion/discipulado/asistencia.php?ciclo_id=' . $cicloId,
                'Repaso actualizado correctamente.'
            );

        }

    ],

    [

        'redirect' => '../views/formacion/discipulado/index.php'

    ]

);
