<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Reunion Service
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/actividadService.php';

/* ==========================================================
   TIPOS DE REUNIÓN
========================================================== */

const TIPOS_REUNION = [

    'REUNION_JOVENES',
    'GRUPO_CONEXION',
    'DISCIPULADO',
    'EVENTO_ESPECIAL',
    'OTRO'

];

/* ==========================================================
   CREAR REUNIÓN
========================================================== */

function crearReunion(
    PDO $pdo,
    array $datos
): int
{
    $fecha = $datos['fecha'] ?? '';

    $tipo = strtoupper(
        trim($datos['tipo'] ?? '')
    );

    $tipoPersonalizado = trim(
        $datos['tipo_personalizado'] ?? ''
    );

    if (!validarFecha($fecha)) {

        throw new Exception(
            'Fecha inválida.'
        );

    }

    if (!in_array(
        $tipo,
        TIPOS_REUNION,
        true
    )) {

        throw new Exception(
            'Tipo de reunión inválido.'
        );

    }

    switch ($tipo) {

        case 'REUNION_JOVENES':
            $tipo = 'Reunión Jóvenes';
            break;

        case 'GRUPO_CONEXION':
            $tipo = 'Grupo Conexión';
            break;

        case 'DISCIPULADO':
            $tipo = 'Discipulado';
            break;

        case 'EVENTO_ESPECIAL':
            $tipo = 'Evento Especial';
            break;

        case 'OTRO':

            if ($tipoPersonalizado === '') {

                throw new Exception(
                    'Debes ingresar el nombre del evento.'
                );

            }

            $tipo = $tipoPersonalizado;

            break;

    }

    $stmt = $pdo->prepare("

        INSERT INTO reuniones

        (

            tipo,

            fecha

        )

        VALUES

        (

            :tipo,

            :fecha

        )

    ");

    $stmt->execute([

        ':tipo'  => $tipo,
        ':fecha' => $fecha

    ]);

    return (int) $pdo->lastInsertId();
}

/* ==========================================================
   ACTUALIZAR REUNIÓN
========================================================== */

function actualizarReunion(
    PDO $pdo,
    array $datos
): void
{
    if (!tienePermiso('gestionar_reuniones')) {

        throw new Exception(
            'Acceso denegado.'
        );

    }

    $id = (int) ($datos['id'] ?? 0);

    $fecha = $datos['fecha'] ?? '';

    $tipo = strtoupper(
        trim($datos['tipo'] ?? '')
    );

    $tipoPersonalizado = trim(
        $datos['tipo_personalizado'] ?? ''
    );

    if ($id <= 0) {

        throw new Exception(
            'Reunión inválida.'
        );

    }

    if (!validarFecha($fecha)) {

        throw new Exception(
            'Fecha inválida.'
        );

    }

    if (!in_array(
        $tipo,
        TIPOS_REUNION,
        true
    )) {

        throw new Exception(
            'Tipo de reunión inválido.'
        );

    }

    switch ($tipo) {

        case 'REUNION_JOVENES':
            $tipo = 'Reunión Jóvenes';
            break;

        case 'GRUPO_CONEXION':
            $tipo = 'Grupo Conexión';
            break;

        case 'DISCIPULADO':
            $tipo = 'Discipulado';
            break;

        case 'EVENTO_ESPECIAL':
            $tipo = 'Evento Especial';
            break;

        case 'OTRO':

            if ($tipoPersonalizado === '') {

                throw new Exception(
                    'Debes ingresar el nombre del evento.'
                );

            }

            $tipo = $tipoPersonalizado;

            break;

    }

    $stmt = $pdo->prepare("

        UPDATE reuniones

        SET

            tipo = :tipo,

            fecha = :fecha

        WHERE id = :id

    ");

    $stmt->execute([

        ':tipo'  => $tipo,

        ':fecha' => $fecha,

        ':id'    => $id

    ]);
}

/* ==========================================================
   ELIMINAR REUNIÓN
========================================================== */

function eliminarReunion(
    PDO $pdo,
    int $id
): void
{
    if (!tienePermiso('gestionar_reuniones')) {

        throw new Exception(
            'Acceso denegado.'
        );

    }

    if ($id <= 0) {

        throw new Exception(
            'Reunión inválida.'
        );

    }

    $stmt = $pdo->prepare("

        DELETE FROM reuniones

        WHERE id = :id

        LIMIT 1

    ");

    $stmt->execute([

        ':id' => $id

    ]);

    if ($stmt->rowCount() === 0) {

        throw new Exception(
            'La reunión no existe.'
        );

    }
}

/* ==========================================================
   OBTENER REUNIÓN
========================================================== */

function obtenerReunionPorId(
    PDO $pdo,
    int $id
): ?array
{
    $stmt = $pdo->prepare("

        SELECT *

        FROM reuniones

        WHERE id = :id

        LIMIT 1

    ");

    $stmt->execute([

        ':id' => $id

    ]);

    $reunion = $stmt->fetch(PDO::FETCH_ASSOC);

    return $reunion ?: null;
}

/* ==========================================================
   LISTAR REUNIONES
========================================================== */

function obtenerReuniones(
    PDO $pdo
): array
{
    $stmt = $pdo->query("

        SELECT *

        FROM reuniones

        ORDER BY fecha DESC

    ");

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
