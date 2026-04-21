<?php
session_start();
require_once "../config/conexion.php";

try {

    /* ============================
       CREAR SEGUIMIENTO
    ============================ */
    if (isset($_POST["crear_seguimiento"])) {

        $joven_id = (int) $_POST["joven_id"];
        $fecha_contacto = $_POST["fecha_contacto"];
        $modalidad = $_POST["modalidad_contacto"];
        $estado = $_POST["estado_proceso"] ?? "PENDIENTE";
        $responsable_id = (int) $_POST["responsable_id"];
        $observaciones = trim($_POST["observaciones"]) ?: null;

        // 🔹 Generar mes automáticamente (FORMATO CORRECTO)
        $mes = date('Y-m');

        if (!$joven_id || empty($fecha_contacto) || empty($modalidad)) {
            throw new Exception("Datos incompletos.");
        }

        /* ============================
           INSERTAR SEGUIMIENTO
        ============================ */
        $stmt = $pdo->prepare("
            INSERT INTO seguimientos
            (joven_id, mes, fecha_contacto, modalidad_contacto,
             estado_proceso, responsable_id, observaciones)
            VALUES
            (:joven_id, :mes, :fecha_contacto, :modalidad,
             :estado, :responsable_id, :observaciones)
        ");

        $stmt->execute([
            "joven_id" => $joven_id,
            "mes" => $mes,
            "fecha_contacto" => $fecha_contacto,
            "modalidad" => $modalidad,
            "estado" => $estado,
            "responsable_id" => $responsable_id,
            "observaciones" => $observaciones
        ]);

        /* ============================
           ACTUALIZAR ACTIVIDAD DEL JOVEN
        ============================ */
        $stmt = $pdo->prepare("
            UPDATE jovenes
            SET ultima_actividad = NOW(),
                estado_actividad = 'ACTIVO'
            WHERE id = :id
        ");

        $stmt->execute(["id" => $joven_id]);

        header("Location: ../views/jovenes/ver.php?id=" . $joven_id);
        exit();
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
