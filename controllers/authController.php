<?php

session_start();

require_once __DIR__ . "/../config/conexion.php";

require_once __DIR__ . "/../helpers/redirect.php";
require_once __DIR__ . "/../helpers/validaciones.php";

require_once __DIR__ . "/../middleware/csrf.php";

require_once __DIR__ . "/../services/AuthService.php";

try {

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {

        throw new Exception(
            "Acceso inválido."
        );

    }

    validarCSRF();

    $usuario = limpiarTexto(
        $_POST["usuario"] ?? ""
    );

    $password = trim(
        $_POST["password"] ?? ""
    );

    if (
        empty($usuario)
        || empty($password)
    ) {

        throw new Exception(
            "Debe ingresar usuario y contraseña."
        );

    }

    $usuarioSistema = loginUsuario(
        $pdo,
        $usuario,
        $password
    );

    redirect(

        "../views/dashboard.php",

        "success",

        "Bienvenido {$usuarioSistema['nombre']}"

    );

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