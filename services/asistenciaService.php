<?php

/* =========================================================
   OBTENER TIPO DE REUNIÓN
========================================================= */

function obtenerTipoReunion(
    PDO $pdo,
    int $reunionId
): string|false {

    $stmt = $pdo->prepare("
        SELECT tipo
        FROM reuniones
        WHERE id = :id
        LIMIT 1
    ");

    $stmt->execute([
        ':id' => $reunionId
    ]);

    return $stmt->fetchColumn();
}


/* =========================================================
   DESACTIVAR DISCIPULADOS VENCIDOS
========================================================= */

function desactivarDiscipuladosVencidos(
    PDO $pdo
): void {

    $stmt = $pdo->prepare("
        UPDATE jovenes

        SET

            discipulado_activo = 0,
            es_nuevo = 0

        WHERE

            discipulado_activo = 1

            AND discipulado_fin <= CURDATE()
    ");

    $stmt->execute();
}


/* =========================================================
   OBTENER JÓVENES ACTIVOS
========================================================= */

function obtenerJovenesActivos(
    PDO $pdo
): array {

    $stmt = $pdo->query("
        SELECT id

        FROM jovenes

        WHERE estado_actividad != 'ELIMINADO'
    ");

    return $stmt->fetchAll(
        PDO::FETCH_COLUMN
    );
}


/* =========================================================
   GUARDAR REGISTRO ASISTENCIA
========================================================= */

function guardarRegistroAsistencia(
    PDO $pdo,
    array $datos
): void {

    $stmt = $pdo->prepare("
        INSERT INTO asistencia
        (
            reunion_id,
            joven_id,
            asistio,
            grupo_edad,
            participa_discipulado,
            grupo_conexion,
            primera_vez_discipulado
        )
        VALUES
        (
            :reunion,
            :joven,
            :asistio,
            :grupo,
            :discipulado,
            :conexion,
            :primera
        )

        ON DUPLICATE KEY UPDATE

            asistio = VALUES(asistio),

            grupo_edad = VALUES(grupo_edad),

            participa_discipulado =
            VALUES(participa_discipulado),

            grupo_conexion =
            VALUES(grupo_conexion),

            primera_vez_discipulado =
            VALUES(primera_vez_discipulado)
    ");

    $stmt->execute([

        'reunion' =>
            $datos['reunion_id'],

        'joven' =>
            $datos['joven_id'],

        'asistio' =>
            $datos['asistio'],

        'grupo' =>
            $datos['grupo_edad'],

        'discipulado' =>
            $datos['participa_discipulado'],

        'conexion' =>
            $datos['grupo_conexion'],

        'primera' =>
            $datos['primera_vez']
    ]);
}


/* =========================================================
   ACTUALIZAR ACTIVIDAD JOVEN
========================================================= */

function actualizarActividadJoven(
    PDO $pdo,
    int $jovenId
): void {

    $stmt = $pdo->prepare("
        UPDATE jovenes

        SET ultima_actividad = NOW()

        WHERE id = :id
    ");

    $stmt->execute([
        ':id' => $jovenId
    ]);
}


/* =========================================================
   ACTIVAR DISCIPULADO
========================================================= */

function activarDiscipulado(
    PDO $pdo,
    int $jovenId
): void {

    $inicio = date('Y-m-d');

    $fin = date(
        'Y-m-d',
        strtotime('+1 month')
    );

    $stmt = $pdo->prepare("
        UPDATE jovenes

        SET

            discipulado_activo = 1,

            discipulado_inicio = :inicio,

            discipulado_fin = :fin,

            es_nuevo = 1

        WHERE id = :id
    ");

    $stmt->execute([

        'inicio' => $inicio,

        'fin' => $fin,

        'id' => $jovenId
    ]);
}

/* =========================================================
   GUARDAR ASISTENCIA
========================================================= */

function guardarAsistencia(
    PDO $pdo,
    array $datos
): void {

    if (empty($datos['reunion_id'])) {
        throw new Exception('Reunión inválida.');
    }

    $reunionId = (int)$datos['reunion_id'];

    $tipo = obtenerTipoReunion(
        $pdo,
        $reunionId
    );

    if (!$tipo) {
        throw new Exception('La reunión no existe.');
    }

    desactivarDiscipuladosVencidos($pdo);

    $jovenes = obtenerJovenesActivos($pdo);

    foreach ($jovenes as $jovenId) {

        $registro = [

            'reunion_id' => $reunionId,

            'joven_id' => $jovenId,

            'asistio' => isset($datos['asistencia'][$jovenId]) ? 1 : 0,

            'grupo_edad' =>
                $datos['grupo_edad'][$jovenId] ?? null,

            'participa_discipulado' =>
                isset($datos['participa_discipulado'][$jovenId]) ? 1 : 0,

            'grupo_conexion' =>
                $datos['grupo_conexion'][$jovenId] ?? null,

            'primera_vez' =>
                isset($datos['primera_vez'][$jovenId]) ? 1 : 0
        ];

        guardarRegistroAsistencia(
            $pdo,
            $registro
        );

        if ($registro['asistio']) {

            actualizarActividadJoven(
                $pdo,
                $jovenId
            );

            if (
                $tipo === 'DISCIPULADO'
                && $registro['primera_vez']
            ) {

                activarDiscipulado(
                    $pdo,
                    $jovenId
                );
            }
        }
    }
}