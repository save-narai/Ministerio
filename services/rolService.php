<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| ROL SERVICE
|--------------------------------------------------------------------------
|
| Servicio encargado de toda la lógica relacionada con Roles.
|
*/



/* ==========================================================
   OBTENER ROLES
========================================================== */

function obtenerRoles(PDO $pdo): array
{
    $stmt = $pdo->query("
        SELECT
            r.id,
            r.nombre,

            (
                SELECT COUNT(*)
                FROM rol_permiso rp
                WHERE rp.rol_id = r.id
            ) AS total_permisos,

            (
                SELECT COUNT(*)
                FROM usuarios u
                WHERE u.rol_id = r.id
            ) AS total_usuarios

        FROM roles r

        ORDER BY r.nombre
    ");

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/* ==========================================================
   OBTENER ROL
========================================================== */

function obtenerRolPorId(
    PDO $pdo,
    int $id
): array|false {

    $stmt = $pdo->prepare("
        SELECT
            id,
            nombre
        FROM roles
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([$id]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/* ==========================================================
   TOTAL ROLES
========================================================== */

function obtenerTotalRoles(PDO $pdo): int
{
    return (int) $pdo
        ->query("SELECT COUNT(*) FROM roles")
        ->fetchColumn();
}

/* ==========================================================
   TOTAL PERMISOS
========================================================== */

function obtenerTotalPermisos(PDO $pdo): int
{
    return (int) $pdo
        ->query("SELECT COUNT(*) FROM permisos")
        ->fetchColumn();
}

/* ==========================================================
   TOTAL USUARIOS
========================================================== */

function obtenerTotalUsuarios(PDO $pdo): int
{
    return (int) $pdo
        ->query("SELECT COUNT(*) FROM usuarios")
        ->fetchColumn();
}

/* ==========================================================
   ROL PROTEGIDO
========================================================== */

function esRolProtegido(
    PDO $pdo,
    int $rolId
): bool {

    $stmt = $pdo->prepare("
        SELECT nombre
        FROM roles
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([$rolId]);

    $nombre = strtoupper(
        (string) $stmt->fetchColumn()
    );

    return in_array(

        $nombre,

        [

            'ADMIN',
            'ADMINISTRADOR',
            'ADMINISTRADOR PRINCIPAL'

        ],

        true

    );

}

/* ==========================================================
   CREAR ROL
========================================================== */

function crearRol(PDO $pdo): void
{
    $nombre = trim((string) ($_POST['nombre'] ?? ''));

    if ($nombre === '') {

        throw new Exception(
            'Debe ingresar el nombre del rol.'
        );

    }

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM roles
        WHERE UPPER(nombre) = UPPER(?)
    ");

    $stmt->execute([$nombre]);

    if ((int) $stmt->fetchColumn() > 0) {

        throw new Exception(
            'Ya existe un rol con ese nombre.'
        );

    }

    $pdo->beginTransaction();

    try {

        $stmt = $pdo->prepare("
            INSERT INTO roles (
                nombre
            )
            VALUES (?)
        ");

        $stmt->execute([
            strtoupper($nombre)
        ]);

        $pdo->commit();

    }

    catch (Throwable $e) {

        if ($pdo->inTransaction()) {

            $pdo->rollBack();

        }

        throw $e;

    }

}

/* ==========================================================
   EDITAR ROL
========================================================== */

function editarRol(PDO $pdo): void
{
    $id = (int) ($_POST['id'] ?? 0);

    $nombre = trim(
        (string) ($_POST['nombre'] ?? '')
    );

    if ($id <= 0) {

        throw new Exception(
            'Rol inválido.'
        );

    }

    if ($nombre === '') {

        throw new Exception(
            'Debe ingresar el nombre del rol.'
        );

    }

    if (esRolProtegido($pdo, $id)) {

        throw new Exception(
            'El rol Administrador Principal no puede modificarse.'
        );

    }

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM roles
        WHERE UPPER(nombre)=UPPER(?)
        AND id<>?
    ");

    $stmt->execute([
        $nombre,
        $id
    ]);

    if ((int) $stmt->fetchColumn() > 0) {

        throw new Exception(
            'Ya existe un rol con ese nombre.'
        );

    }

    $stmt = $pdo->prepare("
        UPDATE roles
        SET nombre = ?
        WHERE id = ?
    ");

    $stmt->execute([
        strtoupper($nombre),
        $id
    ]);

}

/* ==========================================================
   ELIMINAR ROL
========================================================== */

function eliminarRol(PDO $pdo): void
{
    $id = (int) ($_POST['id'] ?? 0);

    if ($id <= 0) {

        throw new Exception(
            'Rol inválido.'
        );

    }

    if (esRolProtegido($pdo, $id)) {

        throw new Exception(
            'No es posible eliminar el Administrador Principal.'
        );

    }

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM usuarios
        WHERE rol_id = ?
    ");

    $stmt->execute([$id]);

    if ((int) $stmt->fetchColumn() > 0) {

        throw new Exception(
            'El rol tiene usuarios asignados.'
        );

    }

    $pdo->beginTransaction();

    try {

        $stmt = $pdo->prepare("
            DELETE
            FROM rol_permiso
            WHERE rol_id = ?
        ");

        $stmt->execute([$id]);

        $stmt = $pdo->prepare("
            DELETE
            FROM roles
            WHERE id = ?
        ");

        $stmt->execute([$id]);

        $pdo->commit();

    }

    catch (Throwable $e) {

        if ($pdo->inTransaction()) {

            $pdo->rollBack();

        }

        throw $e;

    }

}

/* ==========================================================
   OBTENER PERMISOS DEL ROL
========================================================== */

function obtenerPermisosRol(
    PDO $pdo,
    int $rolId
): array {

    $stmt = $pdo->prepare("
        SELECT permiso_id
        FROM rol_permiso
        WHERE rol_id = ?
    ");

    $stmt->execute([$rolId]);

    return array_map(
        'intval',
        $stmt->fetchAll(PDO::FETCH_COLUMN)
    );

}

/* ==========================================================
   OBTENER TODOS LOS PERMISOS
========================================================== */

function obtenerTodosLosPermisos(PDO $pdo): array
{
    $stmt = $pdo->query("
        SELECT
            id,
            nombre,
            descripcion
        FROM permisos
        ORDER BY nombre ASC
    ");

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/* ==========================================================
   GUARDAR PERMISOS DEL ROL
========================================================== */

function guardarPermisosRol(PDO $pdo): void
{
    $rolId = (int) ($_POST['id'] ?? 0);

    if ($rolId <= 0) {

        throw new Exception(
            'Rol inválido.'
        );

    }

    if (esRolProtegido($pdo, $rolId)) {

        throw new Exception(
            'No es posible modificar los permisos del Administrador Principal.'
        );

    }

    $permisos = $_POST['permisos'] ?? [];

    if (!is_array($permisos)) {

        $permisos = [];

    }

    $pdo->beginTransaction();

    try {

        $stmt = $pdo->prepare("
            DELETE
            FROM rol_permiso
            WHERE rol_id = ?
        ");

        $stmt->execute([$rolId]);

        if (!empty($permisos)) {

            $stmt = $pdo->prepare("
                INSERT INTO rol_permiso (
                    rol_id,
                    permiso_id
                )
                VALUES (?, ?)
            ");

            foreach ($permisos as $permisoId) {

                $stmt->execute([
                    $rolId,
                    (int) $permisoId
                ]);

            }

        }

        $pdo->commit();

    }

    catch (Throwable $e) {

        if ($pdo->inTransaction()) {

            $pdo->rollBack();

        }

        throw $e;

    }

}

/* ==========================================================
   ROL TIENE USUARIOS
========================================================== */

function rolTieneUsuarios(
    PDO $pdo,
    int $rolId
): bool {

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM usuarios
        WHERE rol_id = ?
    ");

    $stmt->execute([$rolId]);

    return (int) $stmt->fetchColumn() > 0;

}

/* ==========================================================
   ROL TIENE PERMISO
========================================================== */

function rolTienePermiso(
    PDO $pdo,
    int $rolId,
    int $permisoId
): bool {

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM rol_permiso
        WHERE rol_id = ?
        AND permiso_id = ?
    ");

    $stmt->execute([
        $rolId,
        $permisoId
    ]);

    return (int) $stmt->fetchColumn() > 0;

}