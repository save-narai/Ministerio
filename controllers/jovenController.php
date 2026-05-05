<?php
session_start();

require_once "../middleware/auth.php";
require_once "../middleware/permiso.php";
require_once "../config/conexion.php";

/* ============================
   🔧 FUNCIÓN REUTILIZABLE
============================ */
function limpiarNombre($nombre) {

    $nombre = trim($nombre);
    $nombre = preg_replace('/\s+/', ' ', $nombre);

    if (!preg_match('/^[\p{L} ]+$/u', $nombre)) {
        return [false, "El nombre solo puede contener letras y espacios"];
    }

    if (mb_strlen($nombre) < 3) {
        return [false, "El nombre es demasiado corto"];
    }

    $nombre = mb_convert_case($nombre, MB_CASE_TITLE, "UTF-8");

    return [true, $nombre];
}

try {

    /* ============================
       🟢 CREAR JOVEN
    ============================ */
    if (isset($_POST["crear_joven"])) {

        if (!tienePermiso('gestionar_jovenes')) {
            die("Acceso denegado.");
        }

        [$ok, $nombre] = limpiarNombre($_POST["nombre_completo"] ?? '');

        if (!$ok) {
            $_SESSION["error"] = $nombre;
            header("Location: ../views/jovenes/crear.php");
            exit();
        }

        $fecha_nacimiento = $_POST["fecha_nacimiento"] ?? null;
        $fecha_ingreso = $_POST["fecha_ingreso"] ?? null;
        $telefono = trim($_POST["telefono"] ?? '');
        $genero = $_POST["genero"] ?? null;
        $estado = $_POST["estado_espiritual"] ?? null;
        $es_servidor = isset($_POST["es_servidor"]) ? (int) $_POST["es_servidor"] : 0;

        if (empty($nombre) || empty($fecha_nacimiento) || empty($fecha_ingreso)) {
            $_SESSION["error"] = "Campos obligatorios incompletos";
            header("Location: ../views/jovenes/crear.php");
            exit();
        }

        // duplicados
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM jovenes WHERE nombre_completo = :nombre");
        $stmtCheck->execute(["nombre" => $nombre]);

        if ($stmtCheck->fetchColumn() > 0) {
            $_SESSION["error"] = "Este joven ya está registrado";
            header("Location: ../views/jovenes/crear.php");
            exit();
        }

        // teléfono
        if (!empty($telefono) && !preg_match('/^3[0-9]{9}$/', $telefono)) {
            $_SESSION["error"] = "Teléfono inválido";
            header("Location: ../views/jovenes/crear.php");
            exit();
        }

        $stmt = $pdo->prepare("
            INSERT INTO jovenes
            (nombre_completo, fecha_nacimiento, fecha_ingreso, telefono, es_servidor, genero, estado_espiritual, estado_actividad)
            VALUES
            (:nombre, :fn, :fi, :tel, :servidor, :genero, :estado, 'ACTIVO')
        ");

        $stmt->execute([
            "nombre" => $nombre,
            "fn" => $fecha_nacimiento,
            "fi" => $fecha_ingreso,
            "tel" => $telefono ?: null,
            "servidor" => $es_servidor,
            "genero" => $genero,
            "estado" => $estado
        ]);

        header("Location: ../views/jovenes/index.php");
        exit();
    }

    /* ============================
       🟡 EDITAR JOVEN
    ============================ */
    if (isset($_POST["editar_joven"])) {

        if (!tienePermiso('gestionar_jovenes')) {
            die("Acceso denegado.");
        }

        $id = (int)($_POST["id"] ?? 0);

        if ($id <= 0) {
            die("ID inválido.");
        }

        [$ok, $nombre] = limpiarNombre($_POST["nombre_completo"] ?? '');

        if (!$ok) {
            $_SESSION["error"] = $nombre;
            header("Location: ../views/jovenes/editar.php?id=" . $id);
            exit();
        }

        $telefono = trim($_POST["telefono"] ?? '');

        if (!empty($telefono) && !preg_match('/^3[0-9]{9}$/', $telefono)) {
            $_SESSION["error"] = "Teléfono inválido";
            header("Location: ../views/jovenes/editar.php?id=" . $id);
            exit();
        }

        // evitar duplicados (excepto el mismo)
        $stmtCheck = $pdo->prepare("
            SELECT COUNT(*) FROM jovenes 
            WHERE nombre_completo = :nombre AND id != :id
        ");
        $stmtCheck->execute([
            "nombre" => $nombre,
            "id" => $id
        ]);

        if ($stmtCheck->fetchColumn() > 0) {
            $_SESSION["error"] = "Ya existe otro joven con ese nombre";
            header("Location: ../views/jovenes/editar.php?id=" . $id);
            exit();
        }

        $stmt = $pdo->prepare("
            UPDATE jovenes
            SET nombre_completo = :nombre,
                telefono = :telefono,
                fecha_nacimiento = :fn,
                fecha_ingreso = :fi,
                genero = :genero,
                estado_espiritual = :estado,
                observaciones = :obs
            WHERE id = :id
        ");

        $stmt->execute([
            "nombre" => $nombre,
            "telefono" => $telefono ?: null,
            "fn" => $_POST["fecha_nacimiento"] ?: null,
            "fi" => $_POST["fecha_ingreso"] ?: null,
            "genero" => $_POST["genero"] ?: null,
            "estado" => $_POST["estado_espiritual"] ?: null,
            "obs" => trim($_POST["observaciones"]) ?: null,
            "id" => $id
        ]);

        header("Location: ../views/jovenes/index.php");
        exit();
    }

    /* ============================
       🔴 ELIMINAR JOVEN
    ============================ */
    if (isset($_POST["eliminar_joven"])) {

        if (!tienePermiso('eliminar_jovenes')) {
            die("Acceso denegado.");
        }

        $id = (int)($_POST["id"] ?? 0);

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
    die("Error en base de datos.");
}