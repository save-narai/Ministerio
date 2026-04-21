<?php
session_start();
require_once "../config/conexion.php";

try {

    /* =========================
       CREAR REUNIÓN
    ==========================*/
    if (isset($_POST["crear_reunion"])) {

        if (empty($_POST["tipo"]) || empty($_POST["fecha"]) || empty($_SESSION["user_id"])) {
            throw new Exception("Datos incompletos.");
        }

        $stmt = $pdo->prepare("
            INSERT INTO reuniones (tipo, fecha, creado_por)
            VALUES (:tipo, :fecha, :usuario)
        ");

        $stmt->execute([
            "tipo" => $_POST["tipo"],
            "fecha" => $_POST["fecha"],
            "usuario" => $_SESSION["user_id"]
        ]);

        header("Location: ../views/reuniones/index.php");
        exit();
    }

    /* =========================
       GUARDAR ASISTENCIA
    ==========================*/
    if (isset($_POST["guardar_asistencia"])) {

        if (empty($_POST["reunion_id"])) {
            throw new Exception("Reunión no válida.");
        }

        $pdo->beginTransaction();

        $reunion_id   = (int) $_POST["reunion_id"];
        $asistieron   = $_POST["asistencia"] ?? [];
        $discipulados = $_POST["discipulado"] ?? [];
        $conexion     = $_POST["conexion"] ?? [];
        $primeraVez   = $_POST["primera_vez"] ?? [];
        $grupos       = $_POST["grupo_edad"] ?? [];

        /* DESACTIVAR DISCIPULADOS VENCIDOS */
        $pdo->prepare("
            UPDATE jovenes
            SET discipulado_activo = 0,
                es_nuevo = 0
            WHERE discipulado_activo = 1
            AND discipulado_fin <= CURDATE()
        ")->execute();

        /* BORRAR ASISTENCIA ANTERIOR */
        $stmtDelete = $pdo->prepare("
            DELETE FROM asistencia WHERE reunion_id = :id
        ");
        $stmtDelete->execute(["id" => $reunion_id]);

        /* OBTENER JÓVENES ACTIVOS */
        $jovenes = $pdo->query("
            SELECT id FROM jovenes
            WHERE estado_actividad = 'ACTIVO'
        ")->fetchAll(PDO::FETCH_COLUMN);

        foreach ($jovenes as $joven_id) {

            $asistio = in_array($joven_id, $asistieron) ? 1 : 0;
            $participa_discipulado = in_array($joven_id, $discipulados) ? 1 : 0;
            $esConexion = in_array($joven_id, $conexion) ? 1 : 0;
            $esPrimera  = in_array($joven_id, $primeraVez) ? 1 : 0;
            $grupo_edad = $grupos[$joven_id] ?? null;

            /* INSERTAR ASISTENCIA */
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
            ");

            $stmt->execute([
                "reunion" => $reunion_id,
                "joven" => $joven_id,
                "asistio" => $asistio,
                "grupo" => $grupo_edad,
                "discipulado" => $participa_discipulado,
                "conexion" => $esConexion,
                "primera" => $esPrimera
            ]);

            /* ACTUALIZAR ACTIVIDAD */
            if ($asistio === 1) {

                $stmt2 = $pdo->prepare("
                    UPDATE jovenes
                    SET ultima_actividad = NOW(),
                        estado_actividad = 'ACTIVO'
                    WHERE id = :id
                ");

                $stmt2->execute(["id" => $joven_id]);
            }

            /* SI ES PRIMERA VEZ EN DISCIPULADO */
            if ($esPrimera == 1) {

                $inicio = date("Y-m-d");
                $fin = date("Y-m-d", strtotime("+3 months"));

                $stmt3 = $pdo->prepare("
                    UPDATE jovenes
                    SET discipulado_activo = 1,
                        discipulado_inicio = :inicio,
                        discipulado_fin = :fin,
                        es_nuevo = 1
                    WHERE id = :id
                ");

                $stmt3->execute([
                    "inicio" => $inicio,
                    "fin" => $fin,
                    "id" => $joven_id
                ]);
            }
        }

        $pdo->commit();

        header("Location: ../views/reuniones/index.php");
        exit();
    }

} catch (Exception $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    echo "Error: " . $e->getMessage();
}
?>