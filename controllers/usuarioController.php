<?php

session_start();

require_once '../config/conexion.php';

try {



/* =============================
   CREAR USUARIO
==============================*/

if (isset($_POST["crear_usuario"])) {

    $nombre = trim($_POST["nombre"]);
    $usuario = trim($_POST["usuario"]);
    $password = trim($_POST["password"]);
    $rol_id = (int) $_POST["rol_id"];

    if (
        empty($nombre) ||
        empty($usuario) ||
        empty($password) ||
        $rol_id <= 0
    ) {

        throw new Exception(
            "Todos los campos son obligatorios."
        );
    }

    /* VERIFICAR DUPLICADO */

    $verificar = $pdo->prepare("
        SELECT id
        FROM usuarios
        WHERE usuario = :usuario
        LIMIT 1
    ");

    $verificar->execute([
        ":usuario" => $usuario
    ]);

    if ($verificar->fetch()) {

        throw new Exception(
            "El nombre de usuario ya existe."
        );
    }

    /* ENCRIPTAR CONTRASEÑA */

    $passwordHash = password_hash(
        $password,
        PASSWORD_DEFAULT
    );

    /* INSERTAR */

    $stmt = $pdo->prepare("
        INSERT INTO usuarios (
            nombre,
            usuario,
            password,
            rol_id,
            activo
        )
        VALUES (
            :nombre,
            :usuario,
            :password,
            :rol_id,
            1
        )
    ");

    $stmt->execute([
        ":nombre" => $nombre,
        ":usuario" => $usuario,
        ":password" => $passwordHash,
        ":rol_id" => $rol_id
    ]);

    $_SESSION["success"] =
        "Usuario creado correctamente.";

    header(
        "Location: ../views/usuarios/index.php"
    );

    exit();
}

    /* =============================
       EDITAR USUARIO
    ==============================*/
    if (isset($_POST["editar_usuario"])) {

        $id = (int) $_POST["id"];
        $nombre = trim($_POST["nombre"]);
        $usuario = trim($_POST["usuario"]);
        $password = trim($_POST["password"] ?? "");
        $rol_id = (int) $_POST["rol_id"];

        /* VALIDAR CAMPOS OBLIGATORIOS */
        if (
            empty($nombre) ||
            empty($usuario) ||
            $rol_id <= 0
        ) {

            throw new Exception(
                "Todos los campos son obligatorios."
            );
        }

        /* VALIDAR ID */
        if ($id <= 0) {

            throw new Exception(
                "ID de usuario inválido."
            );
        }

        /* VERIFICAR USUARIO DUPLICADO */
        $verificar = $pdo->prepare("
            SELECT id
            FROM usuarios
            WHERE usuario = :usuario
            AND id != :id
            LIMIT 1
        ");

        $verificar->execute([
            ":usuario" => $usuario,
            ":id" => $id
        ]);

        if ($verificar->fetch()) {

            throw new Exception(
                "El nombre de usuario ya existe."
            );
        }

        /* ACTUALIZAR CON CONTRASEÑA */
        if (!empty($password)) {

            $passwordHash = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $stmt = $pdo->prepare("
                UPDATE usuarios
                SET nombre = :nombre,
                    usuario = :usuario,
                    password = :password,
                    rol_id = :rol_id
                WHERE id = :id
            ");

            $stmt->execute([
                ":nombre" => $nombre,
                ":usuario" => $usuario,
                ":password" => $passwordHash,
                ":rol_id" => $rol_id,
                ":id" => $id
            ]);

        } else {

            /* ACTUALIZAR SIN CONTRASEÑA */
            $stmt = $pdo->prepare("
                UPDATE usuarios
                SET nombre = :nombre,
                    usuario = :usuario,
                    rol_id = :rol_id
                WHERE id = :id
            ");

            $stmt->execute([
                ":nombre" => $nombre,
                ":usuario" => $usuario,
                ":rol_id" => $rol_id,
                ":id" => $id
            ]);
        }

        $_SESSION["success"] =
            "Usuario actualizado correctamente.";

        header(
            "Location: ../views/usuarios/index.php"
        );

        exit();
    }

} catch (Exception $e) {

    $_SESSION["error"] = $e->getMessage();

    header(
        "Location: ../views/usuarios/index.php"
    );

    exit();
}


/* =============================
   CAMBIAR CONTRASEÑA
==============================*/

if (isset($_POST["cambiar_password"])) {

    $id = (int)$_POST["id"];

    $password = trim($_POST["password"]);
    $confirmar = trim($_POST["confirmar_password"]);

    if ($password !== $confirmar) {

        throw new Exception(
            "Las contraseñas no coinciden."
        );
    }

    $passwordHash = password_hash(
        $password,
        PASSWORD_DEFAULT
    );

    $stmt = $pdo->prepare("
        UPDATE usuarios
        SET password = :password
        WHERE id = :id
    ");

    $stmt->execute([
        "password" => $passwordHash,
        "id" => $id
    ]);

    header(
        "Location: ../views/usuarios/index.php"
    );

    exit();
}