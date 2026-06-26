<?php

session_start();

require_once '../config/conexion.php';
require_once '../helpers/redirect.php';

try {

    /* =====================================
       CREAR ROL
    ===================================== */

    if (isset($_POST['crear_rol'])) {
        crearRol($pdo);
    }

    /* =====================================
       EDITAR ROL
    ===================================== */

    if (isset($_POST['editar_rol'])) {
        editarRol($pdo);
    }

    /* =====================================
       GUARDAR PERMISOS
    ===================================== */

    if (isset($_POST['guardar_permisos'])) {
        editarRol($pdo);
    }

    /* =====================================
       ELIMINAR ROL
    ===================================== */

    if (isset($_POST['eliminar_rol'])) {
        eliminarRol($pdo);
    }

} catch (Exception $e) {

    redirect(
        '../views/roles/index.php',
        'error',
        $e->getMessage()
    );
}


/* =========================================================
   CREAR ROL
========================================================= */

function crearRol(PDO $pdo): void
{
    $nombre = trim($_POST['nombre'] ?? '');

    $permisos = $_POST['permisos'] ?? [];

    if (empty($nombre)) {

        throw new Exception(
            'Debe ingresar el nombre del rol.'
        );
    }

    $nombre = strtoupper($nombre);

    $stmt = $pdo->prepare("
        SELECT id
        FROM roles
        WHERE nombre = :nombre
        LIMIT 1
    ");

    $stmt->execute([
        ':nombre' => $nombre
    ]);

    if ($stmt->fetch()) {

        throw new Exception(
            'Ya existe un rol con ese nombre.'
        );
    }

    $stmt = $pdo->prepare("
        INSERT INTO roles(nombre)
        VALUES(:nombre)
    ");

    $stmt->execute([
        ':nombre' => $nombre
    ]);

    $rolId = (int) $pdo->lastInsertId();

    guardarPermisos(
        $pdo,
        $rolId,
        $permisos
    );

    redirect(
        '../views/roles/index.php',
        'success',
        'Rol creado correctamente.'
    );
}


/* =========================================================
   EDITAR ROL
========================================================= */

function editarRol(PDO $pdo): void
{
    $id = (int) (
        $_POST['id']
        ?? $_POST['rol_id']
        ?? 0
    );

    $nombre = trim($_POST['nombre'] ?? '');

    $permisos = $_POST['permisos'] ?? [];

    if ($id <= 0) {

        throw new Exception(
            'Rol inválido.'
        );
    }

    /* VALIDAR DUPLICADO */

    if (!empty($nombre)) {

        $nombre = strtoupper($nombre);

        $stmt = $pdo->prepare("
            SELECT id
            FROM roles
            WHERE nombre = :nombre
            AND id != :id
            LIMIT 1
        ");

        $stmt->execute([
            ':nombre' => $nombre,
            ':id'      => $id
        ]);

        if ($stmt->fetch()) {

            throw new Exception(
                'Ya existe un rol con ese nombre.'
            );
        }

        $stmt = $pdo->prepare("
            UPDATE roles
            SET nombre = :nombre
            WHERE id = :id
        ");

        $stmt->execute([
            ':nombre' => $nombre,
            ':id'     => $id
        ]);
    }

    /* ELIMINAR PERMISOS ACTUALES */

    $stmt = $pdo->prepare("
        DELETE FROM rol_permiso
        WHERE rol_id = :rol_id
    ");

    $stmt->execute([
        ':rol_id' => $id
    ]);

    /* INSERTAR NUEVOS PERMISOS */

    guardarPermisos(
        $pdo,
        $id,
        $permisos
    );

    redirect(
        '../views/roles/index.php',
        'success',
        'Rol actualizado correctamente.'
    );
}


/* =========================================================
   ELIMINAR ROL
========================================================= */

function eliminarRol(PDO $pdo): void
{
    $id = (int) ($_POST['id'] ?? 0);

    if ($id <= 0) {

        throw new Exception(
            'Rol inválido.'
        );
    }

    $stmt = $pdo->prepare("
        SELECT nombre
        FROM roles
        WHERE id = :id
        LIMIT 1
    ");

    $stmt->execute([
        ':id' => $id
    ]);

    $rol = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$rol) {

        throw new Exception(
            'Rol no encontrado.'
        );
    }

    if ($rol['nombre'] === 'ADMIN') {

        throw new Exception(
            'No se puede eliminar el rol ADMIN.'
        );
    }

    $stmt = $pdo->prepare("
        DELETE FROM rol_permiso
        WHERE rol_id = :id
    ");

    $stmt->execute([
        ':id' => $id
    ]);

    $stmt = $pdo->prepare("
        DELETE FROM roles
        WHERE id = :id
    ");

    $stmt->execute([
        ':id' => $id
    ]);

    redirect(
        '../views/roles/index.php',
        'success',
        'Rol eliminado correctamente.'
    );
}


/* =========================================================
   GUARDAR PERMISOS
========================================================= */

function guardarPermisos(
    PDO $pdo,
    int $rolId,
    array $permisos
): void {

    if (empty($permisos)) {
        return;
    }

    $stmt = $pdo->prepare("
        INSERT INTO rol_permiso (
            rol_id,
            permiso_id
        )
        VALUES (
            :rol_id,
            :permiso_id
        )
    ");

    foreach ($permisos as $permisoId) {

        $stmt->execute([
            ':rol_id'     => $rolId,
            ':permiso_id' => (int) $permisoId
        ]);
    }
}