<?php
require_once __DIR__ . "/../config/conexion.php";

function actualizarEstadoActividad() {
    global $pdo;

    $stmt = $pdo->prepare("
        UPDATE jovenes
        SET estado_actividad = 'INACTIVO'
        WHERE ultima_actividad IS NOT NULL
        AND DATEDIFF(NOW(), ultima_actividad) >= 60
    ");

    $stmt->execute();
}

function faltasConsecutivasConexion($joven_id) {

    global $pdo;

    $stmt = $pdo->prepare("
        SELECT a.asistio
        FROM asistencia a
        INNER JOIN reuniones r ON a.reunion_id = r.id
        WHERE a.joven_id = :id
        AND r.tipo = 'CONEXION'
        ORDER BY r.fecha DESC
        LIMIT 5
    ");

    $stmt->execute(["id" => $joven_id]);
    $asistencias = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $faltas = 0;

    foreach ($asistencias as $a) {
        if ($a == 0) {
            $faltas++;
        } else {
            break;
        }
    }

    return $faltas;
}
