
<?php

require_once __DIR__ . "/../config/conexion.php";

/* ======================================================
   ACTUALIZAR ESTADOS AUTOMÁTICOS
====================================================== */

function actualizarEstadoActividad() {

    global $pdo;

    /*
    |--------------------------------------------------------------------------
    | INACTIVOS
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        UPDATE jovenes

        SET estado_actividad = 'INACTIVO'

        WHERE estado_actividad != 'ELIMINADO'

        AND ultima_actividad IS NOT NULL

        AND DATEDIFF(NOW(), ultima_actividad) >= 60
    ");

    $stmt->execute();

    /*
    |--------------------------------------------------------------------------
    | REACTIVAR ACTIVOS
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        UPDATE jovenes

        SET estado_actividad = 'ACTIVO'

        WHERE estado_actividad != 'ELIMINADO'

        AND ultima_actividad IS NOT NULL

        AND DATEDIFF(NOW(), ultima_actividad) < 60
    ");

    $stmt->execute();

    /*
    |--------------------------------------------------------------------------
    | ELIMINAR MAYORES DE 28
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        UPDATE jovenes

        SET estado_actividad = 'ELIMINADO'

        WHERE estado_actividad != 'ELIMINADO'

        AND (

            (
                fecha_nacimiento IS NOT NULL

                AND TIMESTAMPDIFF(
                    YEAR,
                    fecha_nacimiento,
                    CURDATE()
                ) >= 28
            )

            OR

            (
                fecha_nacimiento IS NULL

                AND edad_manual >= 28
            )
        )
    ");

    $stmt->execute();
}

/* ======================================================
   FALTAS CONSECUTIVAS
====================================================== */

function faltasConsecutivasConexion($joven_id) {

    global $pdo;

    $stmt = $pdo->prepare("
        SELECT a.asistio

        FROM asistencia a

        INNER JOIN reuniones r
            ON a.reunion_id = r.id

        WHERE a.joven_id = :id

        AND r.tipo = 'CONEXION'

        ORDER BY r.fecha DESC

        LIMIT 5
    ");

    $stmt->execute([
        "id" => $joven_id
    ]);

    $asistencias =
        $stmt->fetchAll(PDO::FETCH_COLUMN);

    $faltas = 0;

    foreach ($asistencias as $a) {

        if ((int)$a === 0) {

            $faltas++;

        } else {

            break;
        }
    }

    return $faltas;
}

/* ======================================================
   MESES EN EL MINISTERIO
====================================================== */

function mesesMinisterio($fechaIngreso){

    if (empty($fechaIngreso)) {
        return 0;
    }

    $inicio = new DateTime($fechaIngreso);

    $hoy = new DateTime();

    $diff = $inicio->diff($hoy);

    return ($diff->y * 12) + $diff->m;
}

/* ======================================================
   ESTADO CONEXIÓN INTELIGENTE
====================================================== */

function estadoConexionJoven($joven_id) {

    global $pdo;

    /*
    |--------------------------------------------------------------------------
    | DATOS JOVEN
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT

            estado_espiritual,
            fecha_ingreso,
            es_servidor

        FROM jovenes

        WHERE id = :id
    ");

    $stmt->execute([
        "id" => $joven_id
    ]);

    $joven = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$joven){

        return [
            "estado" => "Desconocido",
            "color" => "warning",
            "icono" => "⚪"
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | DATOS BASE
    |--------------------------------------------------------------------------
    */

    $faltas =
        faltasConsecutivasConexion($joven_id);

    $estadoEspiritual =
        strtoupper(
            $joven["estado_espiritual"] ?? ''
        );

    $meses =
        mesesMinisterio(
            $joven["fecha_ingreso"] ?? null
        );

    $esServidor =
        (int)($joven["es_servidor"] ?? 0);

    /*
    |--------------------------------------------------------------------------
    | NUEVOS / CONSOLIDACIÓN
    |--------------------------------------------------------------------------
    */

    $esNuevo =
        $meses <= 3
        ||
        in_array(
            $estadoEspiritual,
            ["NUEVO", "CONSOLIDACION"]
        );

    /*
    |--------------------------------------------------------------------------
    | MADUROS / SERVIDORES
    |--------------------------------------------------------------------------
    */

    $esMaduro =
        $meses >= 12
        ||
        $esServidor === 1
        ||
        in_array(
            $estadoEspiritual,
            ["MADURO", "LIDER"]
        );

    /*
    |--------------------------------------------------------------------------
    | ALTO RIESGO
    |--------------------------------------------------------------------------
    */

    if ($esNuevo && $faltas >= 4){

        return [
            "estado" => "Alto Riesgo",
            "color" => "danger",
            "icono" => "🔴"
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | RIESGO
    |--------------------------------------------------------------------------
    */

    if (!$esMaduro && $faltas >= 3){

        return [
            "estado" => "Riesgo",
            "color" => "warning",
            "icono" => "🟡"
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | OBSERVACIÓN
    |--------------------------------------------------------------------------
    */

    if ($esMaduro && $faltas >= 3){

        return [
            "estado" => "Observación",
            "color" => "info",
            "icono" => "🔵"
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | CONECTADO
    |--------------------------------------------------------------------------
    */

    return [
        "estado" => "Conectado",
        "color" => "ok",
        "icono" => "🟢"
    ];
}

/* ======================================================
   CONTADORES GLOBALES
====================================================== */

function resumenConexionMinisterial() {

    global $pdo;

    $stmt = $pdo->prepare("
        SELECT id

        FROM jovenes

        WHERE estado_actividad != 'ELIMINADO'
    ");

    $stmt->execute();

    $jovenes =
        $stmt->fetchAll(PDO::FETCH_ASSOC);

    $conectados = 0;

    $observacion = 0;

    $riesgo = 0;

    $alto = 0;

    foreach ($jovenes as $j) {

        $estado =
            estadoConexionJoven($j["id"]);

        switch ($estado["estado"]) {

            case "Alto Riesgo":
                $alto++;
            break;

            case "Riesgo":
                $riesgo++;
            break;

            case "Observación":
                $observacion++;
            break;

            default:
                $conectados++;
            break;
        }
    }

    return [

        "conectados" => $conectados,

        "observacion" => $observacion,

        "riesgo" => $riesgo,

        "alto" => $alto
    ];
}