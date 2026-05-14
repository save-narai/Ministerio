<?php

require_once __DIR__ . "/../config/conexion.php";

function obtenerJovenPorId($id) {
    global $pdo;

    $stmt = $pdo->prepare("SELECT * FROM jovenes WHERE id = ?");
    $stmt->execute([$id]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function existeJovenPorNombre($nombre, $id = null) {
    global $pdo;

    if ($id) {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM jovenes 
            WHERE nombre_completo = ? AND id != ?
        ");
        $stmt->execute([$nombre, $id]);
    } else {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM jovenes 
            WHERE nombre_completo = ?
        ");
        $stmt->execute([$nombre]);
    }

    return $stmt->fetchColumn() > 0;
}