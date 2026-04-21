<?php
require_once("../config/conexion.php");
require_once("../middleware/auth.php");
require_once("../middleware/permiso.php");

/*
|--------------------------------------------------------------------------
| ACTUALIZAR REUNIÓN
|--------------------------------------------------------------------------
*/

if (isset($_POST["actualizar"])) {

    $id = (int)$_POST["id"];
    $fecha = $_POST["fecha"];
    $tipo = $_POST["tipo"];

    $stmt = $pdo->prepare("
        UPDATE reuniones
        SET fecha = ?, tipo = ?
        WHERE id = ?
    ");

    $stmt->execute([$fecha, $tipo, $id]);

    header("Location: ../views/reuniones/index.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| ELIMINAR REUNIÓN
|--------------------------------------------------------------------------
*/

if (isset($_GET["eliminar"])) {

    $id = (int)$_GET["eliminar"];

    $pdo->beginTransaction();

    try {

        // Borrar asistencias relacionadas
        $stmt = $pdo->prepare("DELETE FROM asistencia WHERE reunion_id = ?");
        $stmt->execute([$id]);

        // Borrar reunión
        $stmt = $pdo->prepare("DELETE FROM reuniones WHERE id = ?");
        $stmt->execute([$id]);

        $pdo->commit();

    } catch (Exception $e) {

        $pdo->rollBack();
        die("Error al eliminar la reunión");
    }

    header("Location: ../views/reuniones/index.php");
    exit();
}