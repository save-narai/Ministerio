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