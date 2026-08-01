<?php

declare(strict_types=1);

require_once __DIR__ . '/../services/SessionService.php';

/* ==========================================================
   OBTENER PERMISOS DEL USUARIO
========================================================== */

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
        SELECT DISTINCT p.nombre
        FROM permisos p
        INNER JOIN rol_permiso rp
            ON rp.permiso_id = p.id
        INNER JOIN usuarios u
            ON u.rol_id = rp.rol_id
        WHERE
            u.id = :usuario
            AND u.activo = 1
    ");

    $stmt->execute([
        'usuario' => usuarioId()
    ]);

    $permisos = $stmt->fetchAll(PDO::FETCH_COLUMN);

    return $permisos;
}

/* ==========================================================
   VALIDAR PERMISO
========================================================== */

function tienePermiso(string $permiso): bool
{
    if (esAdmin()) {
        return true;
    }

    return in_array(
        $permiso,
        obtenerPermisosUsuario(controllerPdo()),
        true
    );
}

/* ==========================================================
   EXIGIR PERMISO
========================================================== */

function exigirPermiso(string $permiso): void
{
    if (tienePermiso($permiso)) {
        return;
    }

    http_response_code(403);

    redirect(BASE_URL . '/views/errors/403.php');
}