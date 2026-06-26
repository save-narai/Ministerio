<?php

session_start();

require_once "../config/conexion.php";

require_once "../middleware/auth.php";
require_once "../middleware/permiso.php";

require_once "../helpers/redirect.php";
require_once "../helpers/validaciones.php";

try {

    if (!tienePermiso('gestionar_usuarios')) {

        throw new Exception(
            "Acceso denegado."
        );
    }

    $id = (int) ($_GET["id"] ?? 0);

    if (!validarId($id)) {

        throw new Exception(
            "Usuario inválido."
        );
    }

    /* =====================================
       OBTENER USUARIO
    ===================================== */

    $stmt = $pdo->prepare("
        SELECT activo
        FROM usuarios
        WHERE id = :id
        LIMIT 1
    ");

    $stmt->execute([
        ":id" => $id
    ]);

    $usuario = $stmt->fetch(
        PDO::FETCH_ASSOC
    );

    if (!$usuario) {

        throw new Exception(
            "Usuario no encontrado."
        );
    }

    /* =====================================
       CAMBIAR ESTADO
    ===================================== */

    $nuevoEstado =
        $usuario["activo"] ? 0 : 1;

    $stmt = $pdo->prepare("
        UPDATE usuarios

        SET activo = :estado

        WHERE id = :id
    ");

    $stmt->execute([

        ":estado" => $nuevoEstado,

        ":id" => $id
    ]);

    $mensaje = $nuevoEstado
        ? "Usuario activado correctamente."
        : "Usuario desactivado correctamente.";

    redirect(
        "../views/usuarios/index.php",
        "success",
        $mensaje
    );

} catch (Exception $e) {

    redirect(
        "../views/usuarios/index.php",
        "error",
        $e->getMessage()
    );
}