<?php
require_once __DIR__ . "/../config/conexion.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function tienePermiso($permiso) {

    if (empty($_SESSION["user_id"])) {
        return false;
    }

    global $pdo;

    $stmt = $pdo->prepare("
        SELECT 1
        FROM usuarios u
        JOIN rol_permiso rp ON u.rol_id = rp.rol_id
        JOIN permisos p ON rp.permiso_id = p.id
        WHERE u.id = :user_id
        AND p.nombre = :permiso
        LIMIT 1
    ");

    $stmt->execute([
        "user_id" => $_SESSION["user_id"],
        "permiso" => $permiso
    ]);

    return $stmt->fetch() !== false;
}
