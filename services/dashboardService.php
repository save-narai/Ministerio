<?php

require_once __DIR__ . "/../config/conexion.php";

function obtenerResumenDashboard() {
    global $pdo;

    $totalJovenes = $pdo->query("SELECT COUNT(*) FROM jovenes")->fetchColumn();

    $activos = $pdo->query("
        SELECT COUNT(*) FROM jovenes 
        WHERE estado_actividad = 'ACTIVO'
    ")->fetchColumn();

    return [
        "total" => $totalJovenes,
        "activos" => $activos
    ];
}