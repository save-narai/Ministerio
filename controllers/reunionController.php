<?php

session_start();

require_once "../config/conexion.php";

require_once "../middleware/auth.php";
require_once "../middleware/permiso.php";

require_once "../helpers/redirect.php";
require_once "../helpers/csrf.php";
require_once "../helpers/validaciones.php";

/* =========================================================
   CONSTANTES
========================================================= */

const TIPOS_REUNION_VALIDOS = [

    "REUNION_JOVENES",

    "GRUPO_CONEXION",

    "DISCIPULADO",

    "EVENTO_ESPECIAL",

    "OTRO"
];

try {

    /* =====================================================
       CREAR REUNIÓN
    ===================================================== */

    if (isset($_POST["crear_reunion"])) {

        crearReunion($pdo);
    }

    /* =====================================================
       ACTUALIZAR REUNIÓN
    ===================================================== */

    if (isset($_POST["actualizar_reunion"])) {

        actualizarReunion($pdo);
    }

    /* =====================================================
       ELIMINAR REUNIÓN
    ===================================================== */

    if (isset($_POST["eliminar_reunion"])) {

        eliminarReunion($pdo);
    }

} catch (PDOException $e) {

    error_log($e->getMessage());

    redirect(
        "../views/reuniones/index.php",
        "error",
        "Error en base de datos."
    );

} catch (Exception $e) {

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

    if (
        !in_array(
            $tipo,
            TIPOS_REUNION_VALIDOS
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
        INSERT INTO reuniones(
            tipo,
            fecha
        )
        VALUES(
            :tipo,
            :fecha
        )
    ");

    $stmt->execute([

        ":tipo" => $tipo,

        ":fecha" => $fecha
    ]);

    redirect(
        "../views/reuniones/index.php",
        "success",
        "Reunión creada correctamente."
    );
}


/* =========================================================
   ACTUALIZAR REUNIÓN
========================================================= */

function actualizarReunion(PDO $pdo): void
{
    if (!tienePermiso(
        'gestionar_reuniones'
    )) {

        throw new Exception(
            "Acceso denegado."
        );
    }

    validarCsrf();

    $id = (int)($_POST["id"] ?? 0);

    if (!validarId($id)) {

        throw new Exception(
            "ID inválido."
        );
    }

    /* VALIDAR EXISTENCIA */

    $stmt = $pdo->prepare("
        SELECT id
        FROM reuniones
        WHERE id = :id
    ");

    $stmt->execute([
        ":id" => $id
    ]);

    if (!$stmt->fetch()) {

        throw new Exception(
            "La reunión no existe."
        );
    }

    $fecha = $_POST["fecha"] ?? null;

    if (!validarFecha($fecha)) {

        throw new Exception(
            "Fecha inválida."
        );
    }

    $tipo = trim(
        $_POST["tipo"] ?? ''
    );

    if (
        !in_array(
            $tipo,
            TIPOS_REUNION_VALIDOS
        )
    ) {

        throw new Exception(
            "Tipo inválido."
        );
    }

    $stmt = $pdo->prepare("
        UPDATE reuniones

        SET

            fecha = :fecha,
            tipo = :tipo

        WHERE id = :id
    ");

    $stmt->execute([

        ":fecha" => $fecha,

        ":tipo" => $tipo,

        ":id" => $id
    ]);

    redirect(
        "../views/reuniones/index.php",
        "success",
        "Reunión actualizada correctamente."
    );
}


/* =========================================================
   ELIMINAR REUNIÓN
========================================================= */

function eliminarReunion(PDO $pdo): void
{
    if (!tienePermiso(
        'gestionar_reuniones'
    )) {

        throw new Exception(
            "Acceso denegado."
        );
    }

    validarCsrf();

    $id = (int)(
        $_POST["id"] ?? 0
    );

    if (!validarId($id)) {

        throw new Exception(
            "ID inválido."
        );
    }

    /* VALIDAR EXISTENCIA */

    $stmt = $pdo->prepare("
        SELECT id
        FROM reuniones
        WHERE id = :id
    ");

    $stmt->execute([
        ":id" => $id
    ]);

    if (!$stmt->fetch()) {

        throw new Exception(
            "La reunión no existe."
        );
    }

    $pdo->beginTransaction();

    try {

        $stmt = $pdo->prepare("
            DELETE FROM asistencia
            WHERE reunion_id = :id
        ");

        $stmt->execute([
            ":id" => $id
        ]);

        $stmt = $pdo->prepare("
            DELETE FROM reuniones
            WHERE id = :id
        ");

        $stmt->execute([
            ":id" => $id
        ]);

        $pdo->commit();

    } catch (Exception $e) {

        $pdo->rollBack();

        throw new Exception(
            "Error al eliminar la reunión."
        );
    }

    redirect(
        "../views/reuniones/index.php",
        "success",
        "Reunión eliminada correctamente."
    );
}