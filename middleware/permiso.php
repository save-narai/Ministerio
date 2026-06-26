<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/conexion.php';


/* =========================================================
   OBTENER TODOS LOS PERMISOS DEL USUARIO
========================================================= */

function obtenerPermisosUsuario(): array
{
    global $pdo;

    static $permisos = null;

    if ($permisos !== null) {
        return $permisos;
    }

    if (empty($_SESSION['user_id'])) {
        return [];
    }

    $stmt = $pdo->prepare("
        SELECT DISTINCT p.nombre

        FROM permisos p

        INNER JOIN rol_permiso rp
            ON p.id = rp.permiso_id

        INNER JOIN usuarios u
            ON u.rol_id = rp.rol_id

        WHERE u.id = :usuario

        AND u.activo = 1
    ");

    $stmt->execute([
        'usuario' => $_SESSION['user_id']
    ]);

    $permisos = $stmt->fetchAll(
        PDO::FETCH_COLUMN
    );

    return $permisos;
}


/* =========================================================
   VALIDAR PERMISO
========================================================= */

function tienePermiso(string $permiso): bool
{
    if (esAdmin()) {
        return true;
    }

    return in_array(
        $permiso,
        obtenerPermisosUsuario(),
        true
    );
}


/* =========================================================
   REQUERIR PERMISO
========================================================= */

function requierePermiso(string $permiso): void
{
    if (!tienePermiso($permiso)) {

        http_response_code(403);

        die('Acceso denegado.');
    }
}


/* =========================================================
   ES ADMIN
========================================================= */

function esAdmin(): bool
{
    return (
        ($_SESSION['rol'] ?? '') === 'ADMIN'
    );
}