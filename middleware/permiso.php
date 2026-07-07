<?php

require_once __DIR__ . "/../services/SessionService.php";
require_once __DIR__ . "/../config/conexion.php";

/* =========================================================
   OBTENER PERMISOS DEL USUARIO
========================================================= */

function obtenerPermisosUsuario(PDO $pdo): array
{
    static $permisos = null;

    if ($permisos !== null) {
        return $permisos;
    }

    if (!usuarioAutenticado()) {
        return [];
    }

    $stmt = $pdo->prepare("
        SELECT DISTINCT
            p.nombre
        FROM permisos p
        INNER JOIN rol_permiso rp
            ON p.id = rp.permiso_id
        INNER JOIN usuarios u
            ON u.rol_id = rp.rol_id
        WHERE
            u.id = :usuario
            AND u.activo = 1
    ");

    $stmt->execute([
        "usuario" => usuarioId()
    ]);

    $permisos = $stmt->fetchAll(PDO::FETCH_COLUMN);

    return $permisos;
}

/* =========================================================
   VALIDAR PERMISO
========================================================= */

function tienePermiso(string $permiso): bool
{
    global $pdo;

    if (esAdmin()) {
        return true;
    }

    return in_array(
        $permiso,
        obtenerPermisosUsuario($pdo),
        true
    );
}

/* =========================================================
   EXIGIR PERMISO
========================================================= */

function exigirPermiso(string $permiso): void
{
    if (!tienePermiso($permiso)) {

        http_response_code(403);

        exit("Acceso denegado.");

    }
}