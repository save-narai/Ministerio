
<?php

session_start();

require_once "../middleware/auth.php";
require_once "../middleware/permiso.php";
require_once "../config/conexion.php";

/* =========================
   HELPERS
========================= */

require_once "../helpers/validaciones.php";
require_once "../helpers/toast.php";

/* =========================
   SERVICES
========================= */

require_once "../services/jovenService.php";

try {

    /* =====================================================
       CREAR SEGUIMIENTO
    ===================================================== */

    if (isset($_POST["crear_seguimiento"])) {

        /* =========================
           PERMISOS
        ========================== */

        if (!tienePermiso('gestionar_seguimientos')) {

            die("Acceso denegado.");
        }

        /* =========================
           VARIABLES
        ========================== */

        $joven_id = (int)(
            $_POST["joven_id"] ?? 0
        );

        $fecha_contacto =
            $_POST["fecha_contacto"] ?? null;

        $modalidad =
            trim($_POST["modalidad_contacto"] ?? '');

        $estado =
            trim($_POST["estado_proceso"] ?? 'PENDIENTE');

        $responsable_id = (int)(
            $_POST["responsable_id"] ?? 0
        );

        $observaciones =
            trim($_POST["observaciones"] ?? '');

        /* =========================
           NORMALIZAR
        ========================== */

        $observaciones =
            $observaciones !== ''
            ? $observaciones
            : null;

        if ($responsable_id <= 0) {

            $responsable_id = null;
        }

        /* =========================
           VALIDACIONES
        ========================== */

        if ($joven_id <= 0) {

            setToast(
                "Joven inválido.",
                "error"
            );

            header(
                "Location: ../views/jovenes/index.php"
            );

            exit();
        }

        if (
            empty($fecha_contacto)
            || $fecha_contacto > date('Y-m-d')
        ) {

            setToast(
                "La fecha no puede ser futura.",
                "error"
            );

            header(
                "Location: ../views/seguimientos/crear.php?id="
                . $joven_id
            );

            exit();
        }

        $modalidadesValidas = [
            "WHATSAPP",
            "LLAMADA",
            "VISITA",
            "MENSAJE"
        ];

        if (
            !in_array(
                $modalidad,
                $modalidadesValidas
            )
        ) {

            setToast(
                "Modalidad inválida.",
                "error"
            );

            header(
                "Location: ../views/seguimientos/crear.php?id="
                . $joven_id
            );

            exit();
        }

        $estadosValidos = [
            "PENDIENTE",
            "EN_PROCESO",
            "FINALIZADO"
        ];

        if (
            !in_array(
                $estado,
                $estadosValidos
            )
        ) {

            $estado = "PENDIENTE";
        }

        /* =========================
           VALIDAR JOVEN
        ========================== */

        $joven = obtenerJovenPorId(
            $pdo,
            $joven_id
        );

        if (!$joven) {

            setToast(
                "Joven inválido.",
                "error"
            );

            header(
                "Location: ../views/jovenes/index.php"
            );

            exit();
        }

        /* =========================
           TRANSACCIÓN
        ========================== */

        $pdo->beginTransaction();

        try {

            /* =====================================================
               INSERT SEGUIMIENTO
            ===================================================== */

            $stmt = $pdo->prepare("
                INSERT INTO seguimientos
                (
                    joven_id,
                    fecha_contacto,
                    modalidad_contacto,
                    estado_proceso,
                    responsable_id,
                    observaciones
                )
                VALUES
                (
                    :joven_id,
                    :fecha_contacto,
                    :modalidad,
                    :estado,
                    :responsable_id,
                    :observaciones
                )
            ");

            $stmt->execute([

                "joven_id" => $joven_id,

                "fecha_contacto" =>
                    $fecha_contacto,

                "modalidad" =>
                    $modalidad,

                "estado" =>
                    $estado,

                "responsable_id" =>
                    $responsable_id,

                "observaciones" =>
                    $observaciones
            ]);

            /* =====================================================
               ACTUALIZAR ACTIVIDAD
            ===================================================== */

            $stmt = $pdo->prepare("
                UPDATE jovenes

                SET

                    ultima_actividad = NOW(),

                    estado_actividad = 'ACTIVO'

                WHERE id = :id
            ");

            $stmt->execute([

                "id" => $joven_id
            ]);

            /* =========================
               COMMIT
            ========================== */

            $pdo->commit();

            setToast(
                "Seguimiento registrado correctamente.",
                "success"
            );

            /* =====================================================
               VOLVER AL PERFIL DEL JOVEN
            ===================================================== */

            header(
                "Location: ../views/jovenes/ver.php?id="
                . $joven_id
            );

            exit();

        } catch (Exception $e) {

            $pdo->rollBack();

            throw $e;
        }
    }

} catch (PDOException $e) {

    error_log($e->getMessage());

    setToast(
        "Error en base de datos.",
        "error"
    );

    header(
        "Location: ../views/jovenes/index.php"
    );

    exit();

} catch (Exception $e) {

    setToast(
        $e->getMessage(),
        "error"
    );

    header(
        "Location: ../views/jovenes/index.php"
    );

    exit();
}