<?php
session_start();
require_once "../config/conexion.php";

try {

    /* =============================
       TOGGLE USUARIO (POST)
    ==============================*/
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["toggle"])) {

        $id = (int) $_POST["toggle"];

        if ($id <= 0) {
            throw new Exception("ID inválido.");
        }

        $stmt = $pdo->prepare("
            UPDATE usuarios
            SET activo = IF(activo = 1, 0, 1)
            WHERE id = :id
        ");

        $stmt->execute(["id" => $id]);

        header("Location: ../views/usuarios/index.php");
        exit();
    }



/* =============================
   CREAR USUARIO
==============================*/
if (isset($_POST["crear_usuario"])) {

    $nombre = trim($_POST["nombre"]);
    $usuario = trim($_POST["usuario"]);
    $password = $_POST["password"];
    $rol_id = (int) $_POST["rol_id"];

    if (empty($nombre) || empty($usuario) || empty($password) || $rol_id <= 0) {
        throw new Exception("Todos los campos son obligatorios.");
    }

    // Verificar duplicado
    $verificar = $pdo->prepare("
        SELECT id FROM usuarios
        WHERE usuario = :usuario
    ");

    $verificar->execute([
        "usuario" => $usuario
    ]);

    if ($verificar->rowCount() > 0) {
        throw new Exception("El nombre de usuario ya existe.");
    }

    // Encriptar contraseña
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        INSERT INTO usuarios (nombre, usuario, password, rol_id, activo)
        VALUES (:nombre, :usuario, :password, :rol_id, 1)
    ");

    $stmt->execute([
        "nombre" => $nombre,
        "usuario" => $usuario,
        "password" => $passwordHash,
        "rol_id" => $rol_id
    ]);

    header("Location: ../views/usuarios/index.php");
    exit();
}


    /* =============================
       EDITAR USUARIO
    ==============================*/
    if (isset($_POST["editar_usuario"])) {

        $id = (int) $_POST["id"];
        $nombre = trim($_POST["nombre"]);
        $usuario = trim($_POST["usuario"]);
        $rol_id = (int) $_POST["rol_id"];

        // Verificar duplicado
        $verificar = $pdo->prepare("
            SELECT id FROM usuarios
            WHERE usuario = :usuario AND id != :id
        ");

        $verificar->execute([
            "usuario" => $usuario,
            "id" => $id
        ]);

        if ($verificar->rowCount() > 0) {
            throw new Exception("El nombre de usuario ya existe.");
        }

        $stmt = $pdo->prepare("
            UPDATE usuarios
            SET nombre = :nombre,
                usuario = :usuario,
                rol_id = :rol_id
            WHERE id = :id
        ");

        $stmt->execute([
            "nombre" => $nombre,
            "usuario" => $usuario,
            "rol_id" => $rol_id,
            "id" => $id
        ]);

        header("Location: ../views/usuarios/index.php");
        exit();
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
