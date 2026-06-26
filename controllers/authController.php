<?php

session_start();

require_once "../config/conexion.php";

require_once "../helpers/redirect.php";
require_once "../helpers/validaciones.php";

try {

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {

        redirect(
            "../index.php",
            "error",
            "Acceso inválido."
        );
    }

    autenticarUsuario($pdo);

} catch (PDOException $e) {

    error_log($e->getMessage());

    redirect(
        "../index.php",
        "error",
        "Error interno del sistema."
    );

} catch (Exception $e) {

    redirect(
        "../index.php",
        "error",
        $e->getMessage()
    );
}


/* =========================================================
   AUTENTICAR USUARIO
========================================================= */

function autenticarUsuario(PDO $pdo): void
{
    $usuario = limpiarTexto(
        $_POST["usuario"] ?? ''
    );

    $password = trim(
        $_POST["password"] ?? ''
    );

    if (
        empty($usuario)
        || empty($password)
    ) {

        throw new Exception(
            "Debe ingresar usuario y contraseña."
        );
    }

    $stmt = $pdo->prepare("
        SELECT

            u.id,
            u.nombre,
            u.password,
            u.rol_id,
            r.nombre AS rol_nombre

        FROM usuarios u

        INNER JOIN roles r
            ON u.rol_id = r.id

        WHERE

            u.usuario = :usuario

            AND u.activo = 1

        LIMIT 1
    ");

    $stmt->execute([
        ":usuario" => $usuario
    ]);

    $user = $stmt->fetch(
        PDO::FETCH_ASSOC
    );

    if (
        !$user
        || !password_verify(
            $password,
            $user["password"]
        )
    ) {

        throw new Exception(
            "Usuario o contraseña incorrectos."
        );
    }

    session_regenerate_id(true);

    $_SESSION["user_id"] =
        $user["id"];

    $_SESSION["nombre"] =
        $user["nombre"];

    $_SESSION["rol"] =
        $user["rol_nombre"];

    redirect(
        "../views/dashboard.php",
        "success",
        "Bienvenido {$user['nombre']}"
    );
}