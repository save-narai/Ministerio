<?php

session_start();

require_once "../config/conexion.php";

try {

    /* =====================================
       CREAR ROL
    ===================================== */

    if (isset($_POST["crear_rol"])) {

        $nombre = trim($_POST["nombre"]);

        $permisos = $_POST["permisos"] ?? [];

        if (empty($nombre)) {

            throw new Exception(
                "Debe ingresar el nombre del rol."
            );
        }

        /* VERIFICAR DUPLICADO */

        $verificar = $pdo->prepare("
            SELECT id
            FROM roles
            WHERE nombre = :nombre
            LIMIT 1
        ");

        $verificar->execute([
            ":nombre" => $nombre
        ]);

        if ($verificar->fetch()) {

            throw new Exception(
                "Ya existe un rol con ese nombre."
            );
        }

        /* CREAR ROL */

        $stmt = $pdo->prepare("
            INSERT INTO roles (nombre)
            VALUES (:nombre)
        ");

        $stmt->execute([
            ":nombre" => strtoupper($nombre)
        ]);

        $rolId = $pdo->lastInsertId();

        /* GUARDAR PERMISOS */

        if (!empty($permisos)) {

            $insertPermiso = $pdo->prepare("
                INSERT INTO rol_permiso
                (rol_id, permiso_id)
                VALUES
                (:rol_id, :permiso_id)
            ");

            foreach ($permisos as $permisoId) {

                $insertPermiso->execute([
                    ":rol_id"      => $rolId,
                    ":permiso_id"  => $permisoId
                ]);
            }
        }

        $_SESSION["success"] =
            "Rol creado correctamente.";

        header(
            "Location: ../views/roles/index.php"
        );

        exit();
    }

    /* =====================================
       EDITAR ROL
    ===================================== */

    if (isset($_POST["editar_rol"])) {

        $id = (int) $_POST["id"];

        $nombre = trim($_POST["nombre"]);

        $permisos = $_POST["permisos"] ?? [];

        if ($id <= 0) {

            throw new Exception(
                "Rol inválido."
            );
        }

        /* ACTUALIZAR ROL */

        $stmt = $pdo->prepare("
            UPDATE roles
            SET nombre = :nombre
            WHERE id = :id
        ");

        $stmt->execute([
            ":nombre" => strtoupper($nombre),
            ":id" => $id
        ]);

        /* ELIMINAR PERMISOS ACTUALES */

        $delete = $pdo->prepare("
            DELETE FROM rol_permiso
            WHERE rol_id = :rol_id
        ");

        $delete->execute([
            ":rol_id" => $id
        ]);

        /* INSERTAR NUEVOS */

        if (!empty($permisos)) {

            $insertPermiso = $pdo->prepare("
                INSERT INTO rol_permiso
                (rol_id, permiso_id)
                VALUES
                (:rol_id, :permiso_id)
            ");

            foreach ($permisos as $permisoId) {

                $insertPermiso->execute([
                    ":rol_id" => $id,
                    ":permiso_id" => $permisoId
                ]);
            }
        }

        $_SESSION["success"] =
            "Rol actualizado correctamente.";

        header(
            "Location: ../views/roles/index.php"
        );

        exit();
    }

} catch (Exception $e) {

    $_SESSION["error"] =
        $e->getMessage();

    header(
        "Location: ../views/roles/index.php"
    );

    exit();
}



/* =============================
   ELIMINAR ROL
==============================*/

if (isset($_POST["eliminar_rol"])) {

    $id = (int)$_POST["id"];

    if ($id <= 0) {

        throw new Exception(
            "Rol inválido."
        );
    }

    /* EVITAR ELIMINAR ADMIN */

    $stmt = $pdo->prepare("
        SELECT nombre
        FROM roles
        WHERE id = :id
    ");

    $stmt->execute([
        "id" => $id
    ]);

    $rol = $stmt->fetch();

    if (!$rol) {

        throw new Exception(
            "Rol no encontrado."
        );
    }

    if ($rol["nombre"] === "ADMIN") {

        throw new Exception(
            "No se puede eliminar el rol ADMIN."
        );
    }

    /* ELIMINAR RELACIONES */

    $stmt = $pdo->prepare("
        DELETE FROM rol_permiso
        WHERE rol_id = :id
    ");

    $stmt->execute([
        "id" => $id
    ]);

    /* ELIMINAR ROL */

    $stmt = $pdo->prepare("
        DELETE FROM roles
        WHERE id = :id
    ");

    $stmt->execute([
        "id" => $id
    ]);

    header(
        "Location: ../views/roles/index.php"
    );

    exit();
}