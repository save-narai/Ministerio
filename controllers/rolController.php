<?php
session_start();
require_once "../config/conexion.php";
// require_once "../middleware/auth.php";

try {

    if (isset($_POST["guardar_permisos"])) {

        $rol_id = (int) $_POST["rol_id"];
        $permisos = $_POST["permisos"] ?? [];

        if ($rol_id <= 0) {
            throw new Exception("Rol inválido.");
        }

        $pdo->beginTransaction();

        // Eliminar permisos actuales
        $stmt = $pdo->prepare("DELETE FROM rol_permiso WHERE rol_id = :rol_id");
        $stmt->execute(["rol_id" => $rol_id]);

        // Insertar nuevos permisos
        foreach ($permisos as $permiso_id) {

            $stmt = $pdo->prepare("
                INSERT INTO rol_permiso (rol_id, permiso_id)
                VALUES (:rol_id, :permiso_id)
            ");

            $stmt->execute([
                "rol_id" => $rol_id,
                "permiso_id" => (int) $permiso_id
            ]);
        }

        $pdo->commit();

        header("Location: ../views/roles/index.php");
        exit();
    }

} catch (Exception $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    echo "Error: " . $e->getMessage();
}
