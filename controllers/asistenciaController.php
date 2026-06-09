```php
<?php

session_start();

require_once "../config/conexion.php";

require_once "../middleware/auth.php";

require_once "../middleware/permiso.php";

try {

    /* =====================================================
       CREAR REUNIÓN
    ===================================================== */

    if (isset($_POST["crear_reunion"])) {

        if (!tienePermiso('gestionar_reuniones')) {

            die("Acceso denegado.");
        }

        $tipo =
            trim($_POST["tipo"] ?? '');

        $tipoPersonalizado =
            trim($_POST["tipo_personalizado"] ?? '');

        $fecha =
            $_POST["fecha"] ?? null;

        $tiposValidos = [

            "REUNION_JOVENES",

            "GRUPO_CONEXION",

            "DISCIPULADO",

            "EVENTO_ESPECIAL",

            "OTRO"
        ];

        if (!in_array($tipo, $tiposValidos)) {

            die("Tipo inválido.");
        }

        if ($tipo === "OTRO") {

            $tipo =
                !empty($tipoPersonalizado)
                ? strtoupper($tipoPersonalizado)
                : "OTRO";
        }

        $stmt = $pdo->prepare("
            INSERT INTO reuniones
            (
                tipo,
                fecha,
                creado_por
            )
            VALUES
            (
                :tipo,
                :fecha,
                :usuario
            )
        ");

        $stmt->execute([

            "tipo" => $tipo,

            "fecha" => $fecha,

            "usuario" => $_SESSION["user_id"]
        ]);

        header(
            "Location: ../views/reuniones/index.php"
        );

        exit();
    }

    /* =====================================================
       GUARDAR ASISTENCIA
    ===================================================== */

    if (isset($_POST["guardar_asistencia"])) {

        if (!tienePermiso('gestionar_reuniones')) {

            die("Acceso denegado.");
        }

        if (empty($_POST["reunion_id"])) {

            throw new Exception(
                "Reunión inválida."
            );
        }

        $pdo->beginTransaction();

        $reunion_id =
            (int)$_POST["reunion_id"];

        $asistieron =
            $_POST["asistencia"] ?? [];

        $discipulados =
            $_POST["discipulado"] ?? [];

        $conexion =
            $_POST["conexion"] ?? [];

        $primeraVez =
            $_POST["primera_vez"] ?? [];

        $grupos =
            $_POST["grupo_edad"] ?? [];

        /* =========================
           REUNIÓN
        ========================== */

        $stmt = $pdo->prepare("
            SELECT tipo
            FROM reuniones
            WHERE id = ?
        ");

        $stmt->execute([$reunion_id]);

        $tipoReunion =
            $stmt->fetchColumn();

        if (!$tipoReunion) {

            throw new Exception(
                "La reunión no existe."
            );
        }

        /* =========================
           DESACTIVAR DISCIPULADOS
        ========================== */

        $pdo->prepare("
            UPDATE jovenes

            SET

                discipulado_activo = 0,

                es_nuevo = 0

            WHERE discipulado_activo = 1

            AND discipulado_fin <= CURDATE()
        ")->execute();

        /* =========================
           JÓVENES
        ========================== */

        $jovenes = $pdo->query("
            SELECT id

            FROM jovenes

            WHERE estado_actividad != 'ELIMINADO'
        ")->fetchAll(PDO::FETCH_COLUMN);

        /* =========================
           LOOP
        ========================== */

        foreach ($jovenes as $joven_id) {

            $asistio =
                in_array($joven_id, $asistieron)
                ? 1
                : 0;

            $participa_discipulado = 0;

            $grupoConexion = 0;

            /* =========================
               AUTO DISCIPULADO
            ========================== */

            if ($tipoReunion === "DISCIPULADO") {

                $participa_discipulado =
                    $asistio;
            }

            /* =========================
               AUTO CONEXIÓN
            ========================== */

            if ($tipoReunion === "GRUPO_CONEXION") {

                $grupoConexion =
                    $asistio;
            }

            /* =========================
               MANUAL
            ========================== */

            if (in_array($joven_id, $discipulados)) {

                $participa_discipulado = 1;
            }

            if (in_array($joven_id, $conexion)) {

                $grupoConexion = 1;
            }

            $esPrimera =
                in_array($joven_id, $primeraVez)
                ? 1
                : 0;

            $grupo_edad =
                $grupos[$joven_id] ?? null;

            /* =========================
               UPSERT
            ========================== */

            $stmt = $pdo->prepare("
                INSERT INTO asistencia
                (
                    reunion_id,
                    joven_id,
                    asistio,
                    grupo_edad,
                    participa_discipulado,
                    grupo_conexion,
                    primera_vez_discipulado
                )
                VALUES
                (
                    :reunion,
                    :joven,
                    :asistio,
                    :grupo,
                    :discipulado,
                    :conexion,
                    :primera
                )

                ON DUPLICATE KEY UPDATE

                    asistio = VALUES(asistio),

                    grupo_edad = VALUES(grupo_edad),

                    participa_discipulado =
                    VALUES(participa_discipulado),

                    grupo_conexion =
                    VALUES(grupo_conexion),

                    primera_vez_discipulado =
                    VALUES(primera_vez_discipulado)
            ");

            $stmt->execute([

                "reunion" => $reunion_id,

                "joven" => $joven_id,

                "asistio" => $asistio,

                "grupo" => $grupo_edad,

                "discipulado" => $participa_discipulado,

                "conexion" => $grupoConexion,

                "primera" => $esPrimera
            ]);

            /* =========================
               ACTIVIDAD
            ========================== */

            if ($asistio) {

                $stmt = $pdo->prepare("
                    UPDATE jovenes

                    SET ultima_actividad = NOW()

                    WHERE id = :id
                ");

                $stmt->execute([
                    "id" => $joven_id
                ]);
            }

            /* =========================
               NUEVOS
            ========================== */

            if ($esPrimera) {

                $inicio =
                    date("Y-m-d");

                $fin =
                    date(
                        "Y-m-d",
                        strtotime("+1 month")
                    );

                $stmt = $pdo->prepare("
                    UPDATE jovenes

                    SET

                        discipulado_activo = 1,

                        discipulado_inicio = :inicio,

                        discipulado_fin = :fin,

                        es_nuevo = 1

                    WHERE id = :id
                ");

                $stmt->execute([

                    "inicio" => $inicio,

                    "fin" => $fin,

                    "id" => $joven_id
                ]);
            }
        }

        $pdo->commit();

        header(
            "Location: ../views/reuniones/ver.php?id="
            . $reunion_id
        );

        exit();
    }

} catch (Exception $e) {

    if ($pdo->inTransaction()) {

        $pdo->rollBack();
    }

    die(
        "Error: " . $e->getMessage()
    );
}