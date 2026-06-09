```php id="dmlh8i"
<?php

require_once("../config/conexion.php");

require_once("../middleware/auth.php");

require_once("../middleware/permiso.php");

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

    /* =========================
       VALIDAR TIPO
    ========================== */

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

    /* =========================
       OTRO
    ========================== */

    if ($tipo === "OTRO") {

        $tipo =
            !empty($tipoPersonalizado)
            ? strtoupper($tipoPersonalizado)
            : "OTRO";
    }

    /* =========================
       FECHA
    ========================== */

    if (empty($fecha)) {

        die("Fecha inválida.");
    }

    /* =========================
       INSERT
    ========================== */

    $stmt = $pdo->prepare("
        INSERT INTO reuniones
        (
            tipo,
            fecha
        )
        VALUES
        (
            ?,
            ?
        )
    ");

    $stmt->execute([
        $tipo,
        $fecha
    ]);

    header(
        "Location: ../views/reuniones/index.php"
    );

    exit();
}

/* =====================================================
   ACTUALIZAR REUNIÓN
===================================================== */

if (isset($_POST["actualizar"])) {

    $id = (int)$_POST["id"];

    $fecha = $_POST["fecha"];

    $tipo = $_POST["tipo"];

    $stmt = $pdo->prepare("
        UPDATE reuniones

        SET

            fecha = ?,

            tipo = ?

        WHERE id = ?
    ");

    $stmt->execute([
        $fecha,
        $tipo,
        $id
    ]);

    header(
        "Location: ../views/reuniones/index.php"
    );

    exit();
}

/* =====================================================
   ELIMINAR REUNIÓN
===================================================== */

if (isset($_GET["eliminar"])) {

    $id = (int)$_GET["eliminar"];

    $pdo->beginTransaction();

    try {

        /* =========================
           BORRAR ASISTENCIA
        ========================== */

        $stmt = $pdo->prepare("
            DELETE FROM asistencia
            WHERE reunion_id = ?
        ");

        $stmt->execute([$id]);

        /* =========================
           BORRAR REUNIÓN
        ========================== */

        $stmt = $pdo->prepare("
            DELETE FROM reuniones
            WHERE id = ?
        ");

        $stmt->execute([$id]);

        $pdo->commit();

    } catch (Exception $e) {

        $pdo->rollBack();

        die("Error al eliminar reunión.");
    }

    header(
        "Location: ../views/reuniones/index.php"
    );

    exit();
}