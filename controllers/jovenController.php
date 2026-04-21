<?php
session_start();

require_once "../middleware/auth.php";
require_once "../middleware/permiso.php";
require_once "../config/conexion.php";

if (empty($_SESSION["user_id"])) {
    header("Location: ../index.php");
    exit;
}

try {

    /* ============================
       CREAR JOVEN
    ============================ */
    if (isset($_POST["crear_joven"])) {

        if (!tienePermiso('gestionar_jovenes')) {
            die("Acceso denegado.");
        }

        $nombre = trim($_POST["nombre_completo"] ?? '');
        $fecha_nacimiento = $_POST["fecha_nacimiento"] ?? null;
        $fecha_ingreso = $_POST["fecha_ingreso"] ?? null;
        $telefono = trim($_POST["telefono"] ?? '');
        $es_servidor = isset($_POST["es_servidor"]) ? (int) $_POST["es_servidor"] : 0;

        if (empty($nombre) || empty($fecha_nacimiento) || empty($fecha_ingreso)) {
            die("Nombre, fecha de nacimiento y fecha de ingreso son obligatorios.");
        }

        $stmt = $pdo->prepare("
            INSERT INTO jovenes
            (nombre_completo, fecha_nacimiento, fecha_ingreso, telefono, es_servidor, estado_actividad)
            VALUES
            (:nombre, :fecha_nacimiento, :fecha_ingreso, :telefono, :servidor, 'ACTIVO')
        ");

        $stmt->execute([
            "nombre" => $nombre,
            "fecha_nacimiento" => $fecha_nacimiento,
            "fecha_ingreso" => $fecha_ingreso,
            "telefono" => $telefono ?: null,
            "servidor" => $es_servidor
        ]);

        header("Location: ../views/jovenes/index.php");
        exit();
    }

    /* ============================
       EDITAR JOVEN
    ============================ */
    if (isset($_POST["editar_joven"])) {

        if (!tienePermiso('gestionar_jovenes')) {
            die("Acceso denegado.");
        }

        $id = (int) ($_POST["id"] ?? 0);

        if ($id <= 0) {
            die("ID inválido.");
        }

        $stmt = $pdo->prepare("
            UPDATE jovenes
            SET nombre_completo = :nombre,
                telefono = :telefono,
                fecha_nacimiento = :fecha_nacimiento,
                fecha_ingreso = :fecha_ingreso,
                genero = :genero,
                estado_espiritual = :estado,
                observaciones = :observaciones
            WHERE id = :id
        ");

        $stmt->execute([
            "nombre" => trim($_POST["nombre_completo"]),
            "telefono" => trim($_POST["telefono"]) ?: null,
            "fecha_nacimiento" => $_POST["fecha_nacimiento"] ?: null,
            "fecha_ingreso" => $_POST["fecha_ingreso"] ?: null,
            "genero" => $_POST["genero"] ?: null,
            "estado" => $_POST["estado_espiritual"] ?: null,
            "observaciones" => trim($_POST["observaciones"]) ?: null,
            "id" => $id
        ]);

        header("Location: ../views/jovenes/index.php");
        exit();
    }

    /* ============================
       ELIMINAR JOVEN
    ============================ */
    if (isset($_POST["eliminar_joven"])) {

        if (!tienePermiso('eliminar_jovenes')) {
            die("Acceso denegado.");
        }

        $id = (int) ($_POST["id"] ?? 0);

        if ($id <= 0) {
            die("ID inválido.");
        }

        $stmt = $pdo->prepare("DELETE FROM jovenes WHERE id = :id");
        $stmt->execute(["id" => $id]);

        header("Location: ../views/jovenes/index.php");
        exit();
    }

} catch (PDOException $e) {

    error_log($e->getMessage());
    die("Ocurrió un error en la base de datos.");
}