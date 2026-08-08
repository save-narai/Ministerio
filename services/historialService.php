<?php

declare(strict_types=1);

/* ==========================================================
   HISTORIAL SERVICE V2.0
   ----------------------------------------------------------
   Responsable de administrar el historial ministerial
   completo de cada joven.
========================================================== */

/* ==========================================================
   TIPOS DE EVENTOS
========================================================== */

const EVENTO_ASISTENCIA = 'ASISTENCIA';

const EVENTO_FALTA = 'FALTA';

const EVENTO_DISCIPULADO = 'DISCIPULADO';

const EVENTO_CLASE = 'CLASE_DISCIPULADO';

const EVENTO_BAUTISMO = 'BAUTISMO';

const EVENTO_MINISTERIO = 'MINISTERIO';

const EVENTO_ESTADO = 'CAMBIO_ESTADO';

const EVENTO_SERVIDOR = 'SERVIDOR';

const EVENTO_OTRO = 'OTRO';

/* ==========================================================
   VALIDAR EVENTO
========================================================== */

function validarTipoEvento(
    string $tipo
): void {

    $permitidos = [

        EVENTO_ASISTENCIA,

        EVENTO_FALTA,

        EVENTO_DISCIPULADO,

        EVENTO_CLASE,

        EVENTO_BAUTISMO,

        EVENTO_MINISTERIO,

        EVENTO_ESTADO,

        EVENTO_SERVIDOR,

        EVENTO_OTRO

    ];

    if (

        !in_array(
            $tipo,
            $permitidos,
            true
        )

    ) {

        throw new Exception(
            'Tipo de evento inválido.'
        );

    }

}

/* ==========================================================
   VALIDAR DATOS
========================================================== */

function validarEventoHistorial(
    array $evento
): void {

    if (

        empty($evento['joven_id'])

    ) {

        throw new Exception(
            'Debe indicar el joven.'
        );

    }

    if (

        empty($evento['tipo_evento'])

    ) {

        throw new Exception(
            'Debe indicar el tipo.'
        );

    }

    validarTipoEvento(

        $evento['tipo_evento']

    );

}

/* ==========================================================
   NORMALIZAR EVENTO
========================================================== */

function normalizarEventoHistorial(
    array $evento
): array {

    validarEventoHistorial(
        $evento
    );

    return [

        'joven_id' =>

            (int)$evento['joven_id'],

        'reunion_id' =>

            isset($evento['reunion_id'])

                ? (int)$evento['reunion_id']

                : null,

        'tipo_evento' =>

            strtoupper(
                trim(
                    $evento['tipo_evento']
                )
            ),

        'titulo' =>

            trim(
                $evento['titulo']
                ?? ''
            ),

        'descripcion' =>

            trim(
                $evento['descripcion']
                ?? ''
            ),

        'datos_json' =>

            json_encode(

                $evento['datos_json']
                ?? [],

                JSON_UNESCAPED_UNICODE

            ),

        'usuario_id' =>

            isset($evento['usuario_id'])

                ? (int)$evento['usuario_id']

                : null,

        'fecha_evento' =>

            $evento['fecha_evento']

            ?? date('Y-m-d H:i:s')

    ];

}

/* ==========================================================
   REGISTRAR EVENTO
========================================================== */

function registrarEventoHistorial(
    PDO $pdo,
    array $evento
): int {

    $evento = normalizarEventoHistorial(
        $evento
    );

    $stmt = $pdo->prepare("

        INSERT INTO historial_joven
        (

            joven_id,
            reunion_id,
            tipo_evento,
            titulo,
            descripcion,
            datos_json,
            usuario_id,
            fecha_evento

        )

        VALUES
        (

            :joven_id,
            :reunion_id,
            :tipo_evento,
            :titulo,
            :descripcion,
            :datos_json,
            :usuario_id,
            :fecha_evento

        )

    ");

    $stmt->execute([

        'joven_id'      => $evento['joven_id'],
        'reunion_id'    => $evento['reunion_id'],
        'tipo_evento'   => $evento['tipo_evento'],
        'titulo'        => $evento['titulo'],
        'descripcion'   => $evento['descripcion'],
        'datos_json'    => $evento['datos_json'],
        'usuario_id'    => $evento['usuario_id'],
        'fecha_evento'  => $evento['fecha_evento']

    ]);

    return (int)$pdo->lastInsertId();

}

/* ==========================================================
   REGISTRAR ASISTENCIA
========================================================== */

function registrarAsistenciaHistorial(
    PDO $pdo,
    array $registro
): void {

    registrarEventoHistorial(

        $pdo,

        [

            'joven_id'      => $registro['joven_id'],

            'reunion_id'    => $registro['reunion_id'],

            'tipo_evento'   => EVENTO_ASISTENCIA,

            'titulo'        => 'Asistencia registrada',

            'descripcion'   =>
                'El joven asistió a la reunión.',

            'datos_json'    => $registro

        ]

    );

}

/* ==========================================================
   REGISTRAR FALTA
========================================================== */

function registrarFaltaHistorial(
    PDO $pdo,
    array $registro
): void {

    registrarEventoHistorial(

        $pdo,

        [

            'joven_id'      => $registro['joven_id'],

            'reunion_id'    => $registro['reunion_id'],

            'tipo_evento'   => EVENTO_FALTA,

            'titulo'        => 'Inasistencia',

            'descripcion'   =>
                'El joven no asistió a la reunión.',

            'datos_json'    => $registro

        ]

    );

}

/* ==========================================================
   REGISTRAR DISCIPULADO
========================================================== */

function registrarInicioDiscipulado(
    PDO $pdo,
    array $registro
): void {

    registrarEventoHistorial(

        $pdo,

        [

            'joven_id'      => $registro['joven_id'],

            'reunion_id'    => $registro['reunion_id'],

            'tipo_evento'   => EVENTO_DISCIPULADO,

            'titulo'        => 'Inicio de discipulado',

            'descripcion'   =>
                'El joven inició el proceso de discipulado.',

            'datos_json'    => $registro

        ]

    );

}

/* ==========================================================
   REGISTRAR CAMBIO DE ESTADO
========================================================== */

function registrarCambioEstado(
    PDO $pdo,
    int $jovenId,
    string $estadoAnterior,
    string $estadoNuevo
): void {

    registrarEventoHistorial(

        $pdo,

        [

            'joven_id'      => $jovenId,

            'tipo_evento'   => EVENTO_ESTADO,

            'titulo'        => 'Cambio de estado',

            'descripcion'   =>
                "Estado actualizado de {$estadoAnterior} a {$estadoNuevo}.",

            'datos_json'    => [

                'anterior' => $estadoAnterior,

                'nuevo'    => $estadoNuevo

            ]

        ]

    );

}

/* ==========================================================
   OBTENER HISTORIAL DEL JOVEN
========================================================== */

function obtenerHistorialJoven(
    PDO $pdo,
    int $jovenId,
    int $limite = 100
): array {

    $stmt = $pdo->prepare("

        SELECT

            h.*,

            r.nombre AS reunion,

            r.tipo AS tipo_reunion

        FROM historial_joven h

        LEFT JOIN reuniones r
            ON h.reunion_id = r.id

        WHERE h.joven_id = :joven

        ORDER BY
            h.fecha_evento DESC,
            h.id DESC

        LIMIT :limite

    ");

    $stmt->bindValue(
        ':joven',
        $jovenId,
        PDO::PARAM_INT
    );

    $stmt->bindValue(
        ':limite',
        $limite,
        PDO::PARAM_INT
    );

    $stmt->execute();

    return $stmt->fetchAll(
        PDO::FETCH_ASSOC
    );

}

/* ==========================================================
   OBTENER ÚLTIMO EVENTO
========================================================== */

function obtenerUltimoEvento(
    PDO $pdo,
    int $jovenId
): ?array {

    $stmt = $pdo->prepare("

        SELECT *

        FROM historial_joven

        WHERE joven_id = :id

        ORDER BY
            fecha_evento DESC,
            id DESC

        LIMIT 1

    ");

    $stmt->execute([

        'id' => $jovenId

    ]);

    $evento = $stmt->fetch(
        PDO::FETCH_ASSOC
    );

    return $evento ?: null;

}

/* ==========================================================
   OBTENER EVENTOS POR TIPO
========================================================== */

function obtenerEventosPorTipo(
    PDO $pdo,
    int $jovenId,
    string $tipo
): array {

    validarTipoEvento(
        strtoupper($tipo)
    );

    $stmt = $pdo->prepare("

        SELECT *

        FROM historial_joven

        WHERE joven_id = :joven

        AND tipo_evento = :tipo

        ORDER BY
            fecha_evento DESC,
            id DESC

    ");

    $stmt->execute([

        'joven' => $jovenId,

        'tipo' => strtoupper($tipo)

    ]);

    return $stmt->fetchAll(
        PDO::FETCH_ASSOC
    );

}

/* ==========================================================
   CONTAR EVENTOS
========================================================== */

function contarEventos(
    PDO $pdo,
    int $jovenId,
    ?string $tipo = null
): int {

    if ($tipo === null) {

        $stmt = $pdo->prepare("

            SELECT COUNT(*)

            FROM historial_joven

            WHERE joven_id = :id

        ");

        $stmt->execute([

            'id' => $jovenId

        ]);

    } else {

        validarTipoEvento(
            strtoupper($tipo)
        );

        $stmt = $pdo->prepare("

            SELECT COUNT(*)

            FROM historial_joven

            WHERE joven_id = :id

            AND tipo_evento = :tipo

        ");

        $stmt->execute([

            'id' => $jovenId,

            'tipo' => strtoupper($tipo)

        ]);

    }

    return (int)$stmt->fetchColumn();

}

/* ==========================================================
   EXISTE EVENTO
========================================================== */

function existeEvento(
    PDO $pdo,
    int $jovenId,
    string $tipo
): bool {

    return contarEventos(

        $pdo,

        $jovenId,

        $tipo

    ) > 0;

}


/* ==========================================================
   OBTENER RESUMEN DEL HISTORIAL
========================================================== */

function obtenerResumenHistorial(
    PDO $pdo,
    int $jovenId
): array {

    return [

        'total_eventos' => contarEventos(
            $pdo,
            $jovenId
        ),

        'asistencias' => contarEventos(
            $pdo,
            $jovenId,
            EVENTO_ASISTENCIA
        ),

        'faltas' => contarEventos(
            $pdo,
            $jovenId,
            EVENTO_FALTA
        ),

        'discipulados' => contarEventos(
            $pdo,
            $jovenId,
            EVENTO_DISCIPULADO
        ),

        'clases' => contarEventos(
            $pdo,
            $jovenId,
            EVENTO_CLASE
        ),

        'bautismos' => contarEventos(
            $pdo,
            $jovenId,
            EVENTO_BAUTISMO
        ),

        'ministerios' => contarEventos(
            $pdo,
            $jovenId,
            EVENTO_MINISTERIO
        ),

        'cambios_estado' => contarEventos(
            $pdo,
            $jovenId,
            EVENTO_ESTADO
        ),

        'servidor' => existeEvento(
            $pdo,
            $jovenId,
            EVENTO_SERVIDOR
        ),

        'ultimo_evento' => obtenerUltimoEvento(
            $pdo,
            $jovenId
        )

    ];

}

/* ==========================================================
   ELIMINAR EVENTO
========================================================== */

function eliminarEventoHistorial(
    PDO $pdo,
    int $eventoId
): void {

    $stmt = $pdo->prepare("

        DELETE

        FROM historial_joven

        WHERE id = :id

    ");

    $stmt->execute([

        'id' => $eventoId

    ]);

}

/* ==========================================================
   ELIMINAR HISTORIAL COMPLETO
========================================================== */

function eliminarHistorialJoven(
    PDO $pdo,
    int $jovenId
): void {

    $stmt = $pdo->prepare("

        DELETE

        FROM historial_joven

        WHERE joven_id = :id

    ");

    $stmt->execute([

        'id' => $jovenId

    ]);

}

/* ==========================================================
   EXPORTAR HISTORIAL
========================================================== */

function exportarHistorial(
    PDO $pdo,
    int $jovenId
): array {

    return obtenerHistorialJoven(

        $pdo,

        $jovenId,

        5000

    );

}

/* ==========================================================
   FIN DEL SERVICE
========================================================== */