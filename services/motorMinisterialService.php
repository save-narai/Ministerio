<?php

declare(strict_types=1);

/* ==========================================================
   MOTOR MINISTERIAL V1.0
   ----------------------------------------------------------
   Este Service coordina todos los procesos del ministerio.

   IMPORTANTE

   Este archivo NO guarda datos.

   Este archivo NO consulta la BD.

   Este archivo únicamente coordina
   los diferentes Services del sistema.

========================================================== */

require_once __DIR__ . '/actividadService.php';
require_once __DIR__ . '/historialService.php';
require_once __DIR__ . '/discipuladoService.php';
require_once __DIR__ . '/indicadoresService.php';

/* ==========================================================
   TIPOS DE PROCESO
========================================================== */

const PROCESO_ASISTENCIA = 'ASISTENCIA';

const PROCESO_DISCIPULADO = 'DISCIPULADO';

const PROCESO_ESTADO = 'ESTADO';

const PROCESO_BAUTISMO = 'BAUTISMO';

const PROCESO_SERVIDOR = 'SERVIDOR';

const PROCESO_EVENTO = 'EVENTO';

/* ==========================================================
   VALIDAR PROCESO
========================================================== */

function validarProcesoMinisterial(
    string $proceso
): void {

    $permitidos = [

        PROCESO_ASISTENCIA,

        PROCESO_DISCIPULADO,

        PROCESO_ESTADO,

        PROCESO_BAUTISMO,

        PROCESO_SERVIDOR,

        PROCESO_EVENTO

    ];

    if (

        !in_array(

            strtoupper($proceso),

            $permitidos,

            true

        )

    ) {

        throw new Exception(
            'Proceso ministerial inválido.'
        );

    }

}

/* ==========================================================
   CREAR CONTEXTO
========================================================== */

function crearContextoMinisterial(
    array $datos
): array {

    return [

        'proceso' => strtoupper(

            trim(
                $datos['proceso']
            )

        ),

        'joven_id' =>

            (int)($datos['joven_id'] ?? 0),

        'reunion_id' =>

            isset($datos['reunion_id'])

                ? (int)$datos['reunion_id']

                : null,

        'tipo_reunion' => strtoupper(

            trim(
                $datos['tipo_reunion']
                ?? ''
            )

        ),

        'registro' =>

            $datos['registro']
            ?? [],

        'extras' =>

            $datos['extras']
            ?? []

    ];

}

/* ==========================================================
   VALIDAR CONTEXTO
========================================================== */

function validarContextoMinisterial(
    array $contexto
): void {

    validarProcesoMinisterial(
        $contexto['proceso']
    );

    if (

        empty(
            $contexto['joven_id']
        )

    ) {

        throw new Exception(
            'No se recibió el joven.'
        );

    }

}

/* ==========================================================
   EJECUTAR MOTOR
========================================================== */

function ejecutarMotorMinisterial(
    PDO $pdo,
    array $datos
): void {

    $contexto = crearContextoMinisterial(
        $datos
    );

    validarContextoMinisterial(
        $contexto
    );

    switch (
        $contexto['proceso']
    ) {

        case PROCESO_ASISTENCIA:

            procesarAsistenciaMinisterial(

                $pdo,

                $contexto

            );

        break;

        case PROCESO_DISCIPULADO:

            procesarDiscipuladoMinisterial(

                $pdo,

                $contexto

            );

        break;

        case PROCESO_ESTADO:

            procesarEstadoMinisterial(

                $pdo,

                $contexto

            );

        break;

        case PROCESO_BAUTISMO:

            procesarBautismoMinisterial(

                $pdo,

                $contexto

            );

        break;

        case PROCESO_SERVIDOR:

            procesarServidorMinisterial(

                $pdo,

                $contexto

            );

        break;

        default:

            throw new Exception(
                'Proceso no implementado.'
            );

    }

}


/* ==========================================================
   PIPELINE DE ASISTENCIA
========================================================== */

function procesarAsistenciaMinisterialLegacy(
    PDO $pdo,
    array $contexto
): void {

    $registro = $contexto['registro'];

    /*
    ----------------------------------------------------------
    1.
    HISTORIAL
    ----------------------------------------------------------
    */

    if (
        ($registro['asistio'] ?? 0) === 1
    ) {

        registrarAsistenciaHistorial(

            $pdo,

            $registro

        );

    } else {

        registrarFaltaHistorial(

            $pdo,

            $registro

        );

    }

    /*
    ----------------------------------------------------------
    2.
    ACTIVIDAD
    ----------------------------------------------------------
    */

    if (
        ($registro['asistio'] ?? 0) === 1
    ) {

        actualizarUltimaActividad(

            $pdo,

            (int)$registro['joven_id']

        );

    }

    /*
    ----------------------------------------------------------
    3.
    DISCIPULADO
    ----------------------------------------------------------
    */

    procesarDiscipulado(

        $pdo,

        $contexto['tipo_reunion'],

        $registro

    );

    /*
    ----------------------------------------------------------
    4.
    INDICADORES
    ----------------------------------------------------------
    */

    actualizarIndicadoresJoven(

        $pdo,

        (int)$registro['joven_id']

    );

}

/* ==========================================================
   PIPELINE DISCIPULADO
========================================================== */

function procesarDiscipuladoMinisterial(
    PDO $pdo,
    array $contexto
): void {

    /*
    Este pipeline crecerá conforme
    el módulo de discipulado
    evolucione.

    Por ahora queda preparado.
    */

}

/* ==========================================================
   PIPELINE ESTADO
========================================================== */

function procesarEstadoMinisterial(
    PDO $pdo,
    array $contexto
): void {

    /*
    Cambio de estado espiritual.

    Cambio de servidor.

    Cambio de líder.

    etc.

    */

}

/* ==========================================================
   PIPELINE BAUTISMO
========================================================== */

function procesarBautismoMinisterial(
    PDO $pdo,
    array $contexto
): void {

    /*
    Próximamente.

    */

}

/* ==========================================================
   PIPELINE SERVIDOR
========================================================== */

function procesarServidorMinisterial(
    PDO $pdo,
    array $contexto
): void {

    /*
    Próximamente.

    */

}

/* ==========================================================
   EJECUTAR PIPELINE
========================================================== */

function ejecutarPipelineMinisterial(
    PDO $pdo,
    array $contexto
): void {

    switch ($contexto['proceso']) {

        case PROCESO_ASISTENCIA:

            procesarAsistenciaMinisterial(
                $pdo,
                $contexto
            );

        break;

        case PROCESO_DISCIPULADO:

            procesarDiscipuladoMinisterial(
                $pdo,
                $contexto
            );

        break;

        case PROCESO_ESTADO:

            procesarEstadoMinisterial(
                $pdo,
                $contexto
            );

        break;

        case PROCESO_BAUTISMO:

            procesarBautismoMinisterial(
                $pdo,
                $contexto
            );

        break;

        case PROCESO_SERVIDOR:

            procesarServidorMinisterial(
                $pdo,
                $contexto
            );

        break;

    }

}


/* ==========================================================
   EJECUTAR ACTIVIDAD
========================================================== */

function ejecutarActividadMinisterial(
    PDO $pdo,
    array $contexto
): void {

    ejecutarActividad(

        $pdo,

        $contexto

    );

}

/* ==========================================================
   EJECUTAR HISTORIAL
========================================================== */

function ejecutarHistorialMinisterial(
    PDO $pdo,
    array $contexto
): void {

    ejecutarHistorial(

        $pdo,

        $contexto

    );

}

/* ==========================================================
   EJECUTAR DISCIPULADO
========================================================== */

function ejecutarDiscipuladoMinisterial(
    PDO $pdo,
    array $contexto
): void {

    ejecutarDiscipulado(

        $pdo,

        $contexto

    );

}

/* ==========================================================
   EJECUTAR INDICADORES
========================================================== */

function ejecutarIndicadoresMinisterial(
    PDO $pdo,
    array $contexto
): void {

    ejecutarIndicadores(

        $pdo,

        $contexto

    );

}

/* ==========================================================
   PIPELINE ASISTENCIA
========================================================== */

function procesarAsistenciaMinisterial(
    PDO $pdo,
    array $contexto
): void {

    ejecutarHistorialMinisterial(

        $pdo,

        $contexto

    );

    ejecutarActividadMinisterial(

        $pdo,

        $contexto

    );

    ejecutarDiscipuladoMinisterial(

        $pdo,

        $contexto

    );

    ejecutarIndicadoresMinisterial(

        $pdo,

        $contexto

    );

}

