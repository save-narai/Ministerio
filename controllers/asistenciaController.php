<?php

session_start();

require_once "../config/conexion.php";

require_once "../middleware/auth.php";
require_once "../middleware/permiso.php";

require_once "../helpers/redirect.php";
require_once "../helpers/csrf.php";
require_once "../helpers/validaciones.php";

require_once "../services/asistenciaService.php";

try {

    /* =====================================================
       CREAR REUNIÓN
    ===================================================== */

    if (isset($_POST["crear_reunion"])) {

        crearReunion($pdo);
    }

    /* =====================================================
       GUARDAR ASISTENCIA
    ===================================================== */

    if (isset($_POST["guardar_asistencia"])) {

        guardarAsistencia($pdo);
    }

} catch (Exception $e) {

    if ($pdo->inTransaction()) {

        $pdo->rollBack();
    }

    redirect(
        "../views/reuniones/index.php",
        "error",
        $e->getMessage()
    );
}


/* =========================================================
   CREAR REUNIÓN
========================================================= */

function crearReunion(PDO $pdo): void
{
    if (!tienePermiso(
        'gestionar_reuniones'
    )) {

        throw new Exception(
            "Acceso denegado."
        );
    }

    validarCsrf();

    $tipo = trim(
        $_POST["tipo"] ?? ''
    );

    $tipoPersonalizado = trim(
        $_POST["tipo_personalizado"] ?? ''
    );

    $fecha = $_POST["fecha"] ?? null;

    $tiposValidos = [

        "REUNION_JOVENES",
        "GRUPO_CONEXION",
        "DISCIPULADO",
        "EVENTO_ESPECIAL",
        "OTRO"
    ];

    if (
        !in_array(
            $tipo,
            $tiposValidos
        )
    ) {

        throw new Exception(
            "Tipo inválido."
        );
    }

    if ($tipo === "OTRO") {

        $tipo = !empty(
            $tipoPersonalizado
        )

        ? strtoupper(
            limpiarTexto(
                $tipoPersonalizado
            )
        )

        : "OTRO";
    }

    if (!validarFecha($fecha)) {

        throw new Exception(
            "Fecha inválida."
        );
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

        ":tipo" => $tipo,

        ":fecha" => $fecha,

        ":usuario" =>
            $_SESSION["user_id"]
    ]);

    redirect(
        "../views/reuniones/index.php",
        "success",
        "Reunión creada correctamente."
    );
}


/* =========================================================
   GUARDAR ASISTENCIA
========================================================= */

function guardarAsistencia(PDO $pdo): void
{
    if (!tienePermiso(
        'gestionar_reuniones'
    )) {

        throw new Exception(
            "Acceso denegado."
        );
    }

    validarCsrf();

    $reunion_id = (int)(
        $_POST["reunion_id"] ?? 0
    );

    if (!validarId($reunion_id)) {

        throw new Exception(
            "Reunión inválida."
        );
    }

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

    /* =====================================
       VALIDAR REUNIÓN
    ===================================== */

    $tipoReunion =
        obtenerTipoReunion(
            $pdo,
            $reunion_id
        );

    if (!$tipoReunion) {

        throw new Exception(
            "La reunión no existe."
        );
    }

    $pdo->beginTransaction();

    try {

        /* =====================================
           LIMPIAR DISCIPULADOS VENCIDOS
        ===================================== */

        desactivarDiscipuladosVencidos(
            $pdo
        );

        /* =====================================
           JÓVENES
        ===================================== */

        $jovenes =
            obtenerJovenesActivos(
                $pdo
            );

        foreach (
            $jovenes as $joven_id
        ) {

            $asistio =
                in_array(
                    $joven_id,
                    $asistieron
                )
                ? 1
                : 0;

            $participa_discipulado = 0;

            $grupoConexion = 0;

            /* =========================
               AUTOMÁTICOS
            ========================= */

            if (
                $tipoReunion ===
                "DISCIPULADO"
            ) {

                $participa_discipulado =
                    $asistio;
            }

            if (
                $tipoReunion ===
                "GRUPO_CONEXION"
            ) {

                $grupoConexion =
                    $asistio;
            }

            /* =========================
               MANUALES
            ========================= */

            if (
                in_array(
                    $joven_id,
                    $discipulados
                )
            ) {

                $participa_discipulado = 1;
            }

            if (
                in_array(
                    $joven_id,
                    $conexion
                )
            ) {

                $grupoConexion = 1;
            }

            $esPrimera =
                in_array(
                    $joven_id,
                    $primeraVez
                )
                ? 1
                : 0;

            $grupo_edad =
                $grupos[$joven_id]
                ?? null;

            /* =========================
               GUARDAR
            ========================= */

            guardarRegistroAsistencia(
                $pdo,
                [

                    "reunion_id" =>
                        $reunion_id,

                    "joven_id" =>
                        $joven_id,

                    "asistio" =>
                        $asistio,

                    "grupo_edad" =>
                        $grupo_edad,

                    "participa_discipulado" =>
                        $participa_discipulado,

                    "grupo_conexion" =>
                        $grupoConexion,

                    "primera_vez" =>
                        $esPrimera
                ]
            );

            /* =========================
               ACTIVIDAD
            ========================= */

            if ($asistio) {

                actualizarActividadJoven(
                    $pdo,
                    $joven_id
                );
            }

            /* =========================
               DISCIPULADO NUEVO
            ========================= */

            if ($esPrimera) {

                activarDiscipulado(
                    $pdo,
                    $joven_id
                );
            }
        }

        $pdo->commit();

    } catch (Exception $e) {

        $pdo->rollBack();

        throw $e;
    }

    redirect(
        "../views/reuniones/ver.php?id="
        . $reunion_id,
        "success",
        "Asistencia guardada correctamente."
    );
}