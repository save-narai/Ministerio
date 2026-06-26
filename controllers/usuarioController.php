<?php

session_start();

require_once '../config/conexion.php';
require_once '../helpers/redirect.php';
require_once '../helpers/validaciones.php';

try {

    /* =============================
       CREAR USUARIO
    ============================== */

    if (isset($_POST['crear_usuario'])) {

        crearUsuario($pdo);
    }

    /* =============================
       EDITAR USUARIO
    ============================== */

    if (isset($_POST['editar_usuario'])) {

        editarUsuario($pdo);
    }

    /* =============================
       CAMBIAR CONTRASEÑA
    ============================== */

    if (isset($_POST['cambiar_password'])) {

        cambiarPassword($pdo);
    }

} catch (Exception $e) {

    redirect(
        '../views/usuarios/index.php',
        'error',
        $e->getMessage()
    );
}


/* =========================================================
   CREAR USUARIO
========================================================= */

function crearUsuario(PDO $pdo): void
{
    $nombre = trim($_POST['nombre']);
    $usuario = trim($_POST['usuario']);
    $password = trim($_POST['password']);
    $rol_id = (int) $_POST['rol_id'];

    if (
        empty($nombre) ||
        empty($usuario) ||
        empty($password) ||
        $rol_id <= 0
    ) {

        throw new Exception(
            'Todos los campos son obligatorios.'
        );
    }

    /* VERIFICAR DUPLICADO */

    $stmt = $pdo->prepare("
        SELECT id
        FROM usuarios
        WHERE usuario = :usuario
        LIMIT 1
    ");

    $stmt->execute([
        ':usuario' => $usuario
    ]);

    if ($stmt->fetch()) {

        throw new Exception(
            'El nombre de usuario ya existe.'
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
        ':nombre' => $nombre,
        ':usuario' => $usuario,
        ':password' => $passwordHash,
        ':rol_id' => $rol_id
    ]);

    redirect(
        '../views/usuarios/index.php',
        'success',
        'Usuario creado correctamente.'
    );
}


/* =========================================================
   EDITAR USUARIO
========================================================= */

function editarUsuario(PDO $pdo): void
{
    $id = (int) $_POST['id'];

    $nombre = trim($_POST['nombre']);

    $usuario = trim($_POST['usuario']);

    $password = trim($_POST['password'] ?? '');

    $rol_id = (int) $_POST['rol_id'];

    if (
        empty($nombre) ||
        empty($usuario) ||
        $rol_id <= 0
    ) {

        throw new Exception(
            'Todos los campos son obligatorios.'
        );
    }

    if ($id <= 0) {

        throw new Exception(
            'ID de usuario inválido.'
        );
    }

    /* VERIFICAR DUPLICADO */

    $stmt = $pdo->prepare("
        SELECT id
        FROM usuarios
        WHERE usuario = :usuario
        AND id != :id
        LIMIT 1
    ");

    $stmt->execute([
        ':usuario' => $usuario,
        ':id' => $id
    ]);

    if ($stmt->fetch()) {

        throw new Exception(
            'El nombre de usuario ya existe.'
        );
    }

    /* ACTUALIZAR */

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
            ':nombre' => $nombre,
            ':usuario' => $usuario,
            ':password' => $passwordHash,
            ':rol_id' => $rol_id,
            ':id' => $id
        ]);

    } else {

        $stmt = $pdo->prepare("
            UPDATE usuarios
            SET nombre = :nombre,
                usuario = :usuario,
                rol_id = :rol_id
            WHERE id = :id
        ");

        $stmt->execute([
            ':nombre' => $nombre,
            ':usuario' => $usuario,
            ':rol_id' => $rol_id,
            ':id' => $id
        ]);
    }

    redirect(
        '../views/usuarios/index.php',
        'success',
        'Usuario actualizado correctamente.'
    );
}


/* =========================================================
   CAMBIAR CONTRASEÑA
========================================================= */

function cambiarPassword(PDO $pdo): void
{
    $id = (int) $_POST['id'];

    $password = trim($_POST['password']);

    $confirmar = trim(
        $_POST['confirmar_password']
    );

    if ($id <= 0) {

        throw new Exception(
            'Usuario inválido.'
        );
    }

    if (empty($password)) {

        throw new Exception(
            'La contraseña es obligatoria.'
        );
    }

    if ($password !== $confirmar) {

        throw new Exception(
            'Las contraseñas no coinciden.'
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
        ':password' => $passwordHash,
        ':id' => $id
    ]);

    redirect(
        '../views/usuarios/index.php',
        'success',
        'Contraseña actualizada correctamente.'
    );
}