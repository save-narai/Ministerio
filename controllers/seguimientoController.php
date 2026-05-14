<?php
session_start();

require_once "../middleware/auth.php";
require_once "../middleware/permiso.php";
require_once "../config/conexion.php";

/* HELPERS */
require_once "../helpers/validaciones.php";
require_once "../helpers/toast.php";

/* SERVICES */
require_once "../services/jovenService.php";

try {

    /* ============================
       🟢 CREAR SEGUIMIENTO
    ============================ */
    if (isset($_POST["crear_seguimiento"])) {

        // 🔥 PERMISO CORRECTO
        if (!tienePermiso('gestionar_seguimientos')) {
            die("Acceso denegado.");
        }

        $joven_id = (int) ($_POST["joven_id"] ?? 0);
        $fecha_contacto = $_POST["fecha_contacto"] ?? null;
        $modalidad = $_POST["modalidad_contacto"] ?? null;
        $estado = $_POST["estado_proceso"] ?? "PENDIENTE";
        $responsable_id = (int) ($_POST["responsable_id"] ?? 0);
        $observaciones = trim($_POST["observaciones"] ?? '') ?: null;

        /* ============================
           🔥 VALIDACIONES
        ============================ */

        if ($joven_id <= 0) {
            setToast("Joven inválido.");
            header("Location: ../views/jovenes/index.php");
            exit();
        }

        if (empty($fecha_contacto) || $fecha_contacto > date('Y-m-d')) {
            setToast("La fecha no puede ser futura.");
            header("Location: ../views/jovenes/ver.php?id=" . $joven_id);
            exit();
        }

        if (empty($modalidad)) {
            setToast("Debe seleccionar una modalidad.");
            header("Location: ../views/jovenes/ver.php?id=" . $joven_id);
            exit();
        }

        // 🔥 ENUM CONTROLADO
        $estadosValidos = ["PENDIENTE", "EN_PROCESO", "FINALIZADO"];
        if (!in_array($estado, $estadosValidos)) {
            $estado = "PENDIENTE";
        }

        // 🔥 RESPONSABLE LIMPIO
        if ($responsable_id <= 0) {
            $responsable_id = null;
        }

        /* VALIDAR JOVEN EXISTE */
        $joven = obtenerJovenPorId($joven_id);
        if (!$joven) {
            setToast("El joven no existe.");
            header("Location: ../views/jovenes/index.php");
            exit();
        }

        /* ============================
           🔥 DATOS AUTOMÁTICOS
        ============================ */
        $mes = date('Y-m');

        /* ============================
           🔒 TRANSACCIÓN
        ============================ */
        $pdo->beginTransaction();

        try {

            /* ============================
               🧠 INSERT SEGUIMIENTO
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
               🔥 UPDATE ACTIVIDAD
            ============================ */
            $stmt = $pdo->prepare("
                UPDATE jovenes
                SET ultima_actividad = NOW(),
                    estado_actividad = 'ACTIVO'
                WHERE id = :id
            ");

            $stmt->execute(["id" => $joven_id]);

            $pdo->commit();

            // ✅ TOAST PRO
            setToast("Seguimiento registrado correctamente", "success");

            header("Location: ../views/jovenes/ver.php?id=" . $joven_id);
            exit();

        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

} catch (PDOException $e) {
    error_log($e->getMessage());
    setToast("Error en base de datos.");
    header("Location: ../views/jovenes/index.php");
    exit();

} catch (Exception $e) {
    setToast($e->getMessage());
    header("Location: ../views/jovenes/index.php");
    exit();
}