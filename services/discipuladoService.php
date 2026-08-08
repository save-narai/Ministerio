<?php

declare(strict_types=1);

require_once __DIR__ . '/historialService.php';

/* ==========================================================
   DISCIPULADO SERVICE V2.0
   ----------------------------------------------------------
   Responsable de administrar el proceso completo
   de discipulado de un joven.
========================================================== */

/* ==========================================================
   CONSTANTES
========================================================== */

const DISCIPULADO_ACTIVO = 'ACTIVO';

const DISCIPULADO_FINALIZADO = 'FINALIZADO';

const DISCIPULADO_CANCELADO = 'CANCELADO';

const TOTAL_CLASES_DISCIPULADO = 9;

/* ==========================================================
   VALIDAR ESTADO
========================================================== */

function validarEstadoDiscipulado(
    string $estado
): void {

    $permitidos = [

        DISCIPULADO_ACTIVO,

        DISCIPULADO_FINALIZADO,

        DISCIPULADO_CANCELADO

    ];

    if (

        !in_array(

            strtoupper($estado),

            $permitidos,

            true

        )

    ) {

        throw new Exception(
            'Estado de discipulado inválido.'
        );

    }

}

/* ==========================================================
   VALIDAR CLASE
========================================================== */

function validarClaseDiscipulado(
    int $clase
): void {

    if (

        $clase < 1 ||

        $clase > TOTAL_CLASES_DISCIPULADO

    ) {

        throw new Exception(
            'Número de clase inválido.'
        );

    }

}

/* ==========================================================
   OBTENER DISCIPULADO ACTIVO
========================================================== */

function obtenerDiscipuladoActivo(

    PDO $pdo,

    int $jovenId

): ?array {

    $stmt = $pdo->prepare("

        SELECT *

        FROM discipulados

        WHERE joven_id = :joven

        AND estado = :estado

        LIMIT 1

    ");

    $stmt->execute([

        'joven' => $jovenId,

        'estado' => DISCIPULADO_ACTIVO

    ]);

    $discipulado = $stmt->fetch(

        PDO::FETCH_ASSOC

    );

    return $discipulado ?: null;

}

/* ==========================================================
   EXISTE DISCIPULADO ACTIVO
========================================================== */

function existeDiscipuladoActivo(

    PDO $pdo,

    int $jovenId

): bool {

    return obtenerDiscipuladoActivo(

        $pdo,

        $jovenId

    ) !== null;

}

/* ==========================================================
   CREAR DISCIPULADO
========================================================== */

function crearDiscipulado(

    PDO $pdo,

    int $jovenId,

    ?int $reunionId = null

): int {

    if (

        existeDiscipuladoActivo(

            $pdo,

            $jovenId

        )

    ) {

        throw new Exception(

            'El joven ya tiene un discipulado activo.'

        );

    }

    $stmt = $pdo->prepare("

        INSERT INTO discipulados
        (

            joven_id,

            estado,

            fecha_inicio,

            total_clases,

            clases_completadas,

            clase_actual

        )

        VALUES
        (

            :joven,

            :estado,

            CURDATE(),

            :total,

            0,

            1

        )

    ");

    $stmt->execute([

        'joven' => $jovenId,

        'estado' => DISCIPULADO_ACTIVO,

        'total' => TOTAL_CLASES_DISCIPULADO

    ]);

    $id = (int)$pdo->lastInsertId();

    registrarEventoHistorial(

        $pdo,

        [

            'joven_id' => $jovenId,

            'reunion_id' => $reunionId,

            'tipo_evento' => EVENTO_DISCIPULADO,

            'titulo' => 'Inicio de discipulado',

            'descripcion' => 'El joven inició el proceso de discipulado.',

            'datos_json' => [

                'discipulado_id' => $id

            ]

        ]

    );

    return $id;

}

/* ==========================================================
   OBTENER DISCIPULADO
========================================================== */

function obtenerDiscipulado(
    PDO $pdo,
    int $discipuladoId
): ?array {

    $stmt = $pdo->prepare("

        SELECT *

        FROM discipulados

        WHERE id = :id

        LIMIT 1

    ");

    $stmt->execute([

        'id' => $discipuladoId

    ]);

    $discipulado = $stmt->fetch(
        PDO::FETCH_ASSOC
    );

    return $discipulado ?: null;

}

/* ==========================================================
   CONTAR CLASES COMPLETADAS
========================================================== */

function contarClasesCompletadas(
    PDO $pdo,
    int $discipuladoId
): int {

    $stmt = $pdo->prepare("

        SELECT COUNT(*)

        FROM discipulado_clases

        WHERE discipulado_id = :id

        AND completada = 1

    ");

    $stmt->execute([

        'id' => $discipuladoId

    ]);

    return (int)$stmt->fetchColumn();

}

/* ==========================================================
   CALCULAR PORCENTAJE
========================================================== */

function calcularPorcentajeDiscipulado(
    PDO $pdo,
    int $discipuladoId
): float {

    $completadas = contarClasesCompletadas(

        $pdo,

        $discipuladoId

    );

    return round(

        ($completadas * 100)

        / TOTAL_CLASES_DISCIPULADO,

        2

    );

}

/* ==========================================================
   OBTENER SIGUIENTE CLASE
========================================================== */

function obtenerSiguienteClase(
    PDO $pdo,
    int $discipuladoId
): int {

    return contarClasesCompletadas(

        $pdo,

        $discipuladoId

    ) + 1;

}

/* ==========================================================
   CLASE YA REGISTRADA
========================================================== */

function existeClaseRegistrada(
    PDO $pdo,
    int $discipuladoId,
    int $numeroClase
): bool {

    validarClaseDiscipulado(
        $numeroClase
    );

    $stmt = $pdo->prepare("

        SELECT COUNT(*)

        FROM discipulado_clases

        WHERE discipulado_id = :discipulado

        AND numero_clase = :clase

    ");

    $stmt->execute([

        'discipulado' => $discipuladoId,

        'clase' => $numeroClase

    ]);

    return (int)$stmt->fetchColumn() > 0;

}

/* ==========================================================
   REGISTRAR CLASE
========================================================== */

function registrarClaseDiscipulado(
    PDO $pdo,
    int $discipuladoId,
    int $numeroClase,
    ?int $reunionId = null,
    ?string $observaciones = null
): void {

    validarClaseDiscipulado(
        $numeroClase
    );

    if (

        existeClaseRegistrada(

            $pdo,

            $discipuladoId,

            $numeroClase

        )

    ) {

        throw new Exception(

            'La clase ya fue registrada.'

        );

    }

    $stmt = $pdo->prepare("

        INSERT INTO discipulado_clases
        (

            discipulado_id,

            numero_clase,

            fecha,

            completada,

            reunion_id,

            observaciones

        )

        VALUES
        (

            :discipulado,

            :clase,

            CURDATE(),

            1,

            :reunion,

            :observaciones

        )

    ");

    $stmt->execute([

        'discipulado' => $discipuladoId,

        'clase' => $numeroClase,

        'reunion' => $reunionId,

        'observaciones' => $observaciones

    ]);

}

/* ==========================================================
   OBTENER PROGRESO
========================================================== */

function obtenerProgresoDiscipulado(
    PDO $pdo,
    int $discipuladoId
): array {

    $discipulado = obtenerDiscipulado(
        $pdo,
        $discipuladoId
    );

    if (!$discipulado) {

        throw new Exception(
            'El discipulado no existe.'
        );

    }

    $completadas = contarClasesCompletadas(
        $pdo,
        $discipuladoId
    );

    $porcentaje = calcularPorcentajeDiscipulado(
        $pdo,
        $discipuladoId
    );

    return [

        'total' => TOTAL_CLASES_DISCIPULADO,

        'completadas' => $completadas,

        'pendientes' =>
            TOTAL_CLASES_DISCIPULADO - $completadas,

        'porcentaje' => $porcentaje,

        'clase_actual' =>
            min(
                obtenerSiguienteClase(
                    $pdo,
                    $discipuladoId
                ),
                TOTAL_CLASES_DISCIPULADO
            )

    ];

}

/* ==========================================================
   FINALIZAR DISCIPULADO
========================================================== */

function finalizarDiscipulado(
    PDO $pdo,
    int $discipuladoId
): void {

    $discipulado = obtenerDiscipulado(
        $pdo,
        $discipuladoId
    );

    if (!$discipulado) {

        throw new Exception(
            'No existe el discipulado.'
        );

    }

    $stmt = $pdo->prepare("

        UPDATE discipulados

        SET

            estado = :estado,

            fecha_fin = CURDATE()

        WHERE id = :id

    ");

    $stmt->execute([

        'estado' => DISCIPULADO_FINALIZADO,

        'id' => $discipuladoId

    ]);

    registrarEventoHistorial(

        $pdo,

        [

            'joven_id' =>
                $discipulado['joven_id'],

            'tipo_evento' =>
                EVENTO_DISCIPULADO,

            'titulo' =>
                'Discipulado finalizado',

            'descripcion' =>
                'El joven completó todas las clases.',

            'datos_json' => [

                'discipulado_id' =>
                    $discipuladoId

            ]

        ]

    );

}

/* ==========================================================
   ACTUALIZAR PROGRESO
========================================================== */

function actualizarProgresoDiscipulado(
    PDO $pdo,
    int $discipuladoId
): void {

    $progreso = obtenerProgresoDiscipulado(
        $pdo,
        $discipuladoId
    );

    if (

        $progreso['completadas']
        >=
        TOTAL_CLASES_DISCIPULADO

    ) {

        finalizarDiscipulado(
            $pdo,
            $discipuladoId
        );

    }

}

/* ==========================================================
   COMPLETAR CLASE
========================================================== */

function completarClase(
    PDO $pdo,
    int $discipuladoId,
    int $numeroClase,
    ?int $reunionId = null,
    ?string $observaciones = null
): void {

    registrarClaseDiscipulado(

        $pdo,

        $discipuladoId,

        $numeroClase,

        $reunionId,

        $observaciones

    );

    $discipulado = obtenerDiscipulado(
        $pdo,
        $discipuladoId
    );

    registrarEventoHistorial(

        $pdo,

        [

            'joven_id' =>
                $discipulado['joven_id'],

            'reunion_id' =>
                $reunionId,

            'tipo_evento' =>
                EVENTO_CLASE,

            'titulo' =>
                "Clase {$numeroClase} completada",

            'descripcion' =>
                "Se registró la clase {$numeroClase}.",

            'datos_json' => [

                'discipulado_id' =>
                    $discipuladoId,

                'clase' =>
                    $numeroClase

            ]

        ]

    );

    actualizarProgresoDiscipulado(

        $pdo,

        $discipuladoId

    );

}

/* ==========================================================
   CANCELAR DISCIPULADO
========================================================== */

function cancelarDiscipulado(
    PDO $pdo,
    int $discipuladoId,
    ?string $motivo = null
): void {

    $discipulado = obtenerDiscipulado(
        $pdo,
        $discipuladoId
    );

    if (!$discipulado) {

        throw new Exception(
            'El discipulado no existe.'
        );

    }

    $stmt = $pdo->prepare("

        UPDATE discipulados

        SET

            estado = :estado,

            fecha_fin = CURDATE(),

            observaciones = :motivo

        WHERE id = :id

    ");

    $stmt->execute([

        'estado' => DISCIPULADO_CANCELADO,

        'motivo' => $motivo,

        'id' => $discipuladoId

    ]);

    registrarEventoHistorial(

        $pdo,

        [

            'joven_id' =>
                $discipulado['joven_id'],

            'tipo_evento' =>
                EVENTO_DISCIPULADO,

            'titulo' =>
                'Discipulado cancelado',

            'descripcion' =>
                $motivo
                    ?? 'El proceso fue cancelado.',

            'datos_json' => [

                'discipulado_id' =>
                    $discipuladoId

            ]

        ]

    );

}

/* ==========================================================
   OBTENER RESUMEN
========================================================== */

function obtenerResumenDiscipulado(
    PDO $pdo,
    int $jovenId
): array {

    $discipulado = obtenerDiscipuladoActivo(
        $pdo,
        $jovenId
    );

    if (!$discipulado) {

        return [

            'activo' => false,

            'discipulado' => null,

            'progreso' => null

        ];

    }

    return [

        'activo' => true,

        'discipulado' => $discipulado,

        'progreso' => obtenerProgresoDiscipulado(

            $pdo,

            (int)$discipulado['id']

        )

    ];

}

/* ==========================================================
   DISCIPULADO COMPLETADO
========================================================== */

function discipuladoCompletado(
    PDO $pdo,
    int $discipuladoId
): bool {

    return contarClasesCompletadas(

        $pdo,

        $discipuladoId

    ) >= TOTAL_CLASES_DISCIPULADO;

}

/* ==========================================================
   PROCESAR DISCIPULADO
========================================================== */

function procesarDiscipulado(
    PDO $pdo,
    string $tipoReunion,
    array $registro
): void {

    /*
    ----------------------------------------------------------

    Esta función será llamada por asistenciaService.

    Su responsabilidad NO es guardar asistencia.

    Su única responsabilidad será decidir
    si debe iniciar un discipulado,
    registrar una clase,
    finalizarlo
    o simplemente no hacer nada.

    ----------------------------------------------------------
    */

}

/* ==========================================================
   FIN DEL SERVICE
========================================================== */