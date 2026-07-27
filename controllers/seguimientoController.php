<?php

<<<<<<< HEAD
session_start();

require_once "../middleware/auth.php";
require_once "../middleware/permiso.php";

require_once "../config/conexion.php";

require_once "../helpers/redirect.php";
require_once "../helpers/csrf.php";
require_once "../helpers/validaciones.php";

require_once "../services/jovenService.php";

/* =====================================================
   CONSTANTES
===================================================== */

const MODALIDADES_VALIDAS = [
    'WHATSAPP',
    'LLAMADA',
    'VISITA'
];

const ESTADOS_VALIDOS = [
    'PENDIENTE',
    'EN_PROCESO',
    'FINALIZADO'
];

$pdo->setAttribute(
    PDO::ATTR_ERRMODE,
    PDO::ERRMODE_EXCEPTION
);

try {

    if (isset($_POST["crear_seguimiento"])) {

        crearSeguimiento($pdo);
=======
declare(strict_types=1);

session_start();

require_once __DIR__ . "/../middleware/auth.php";
require_once __DIR__ . "/../middleware/permiso.php";

require_once __DIR__ . "/../config/conexion.php";

require_once __DIR__ . "/../helpers/csrf.php";
require_once __DIR__ . "/../helpers/redirect.php";
require_once __DIR__ . "/../helpers/validaciones.php";

require_once __DIR__ . "/../services/seguimientoService.php";

/* =========================================================
   SOLO PETICIONES POST
========================================================= */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    redirect(

        "../views/seguimientos/index.php",

        "error",

        "Acceso inválido."

    );

}

/* =========================================================
   PERMISOS
========================================================= */

if (!tienePermiso("gestionar_seguimientos")) {

    redirect(

        "../views/dashboard.php",

        "error",

        "No tienes permisos para realizar esta acción."

    );

}

/* =========================================================
   CSRF
========================================================= */

validarCsrf();

/* =========================================================
   ACCIÓN
========================================================= */

$action = strtolower(

    trim(

        (string) ($_POST["action"] ?? "")

    )

);

/* =========================================================
   CONTROLADOR
========================================================= */

try {

    switch ($action) {

        /* =============================================
           CREAR
        ============================================== */

        case "crear":

            crear($pdo);

            break;

        /* =============================================
           EDITAR
        ============================================== */

        case "editar":

            editar($pdo);

            break;

        /* =============================================
           ELIMINAR
        ============================================== */

        case "eliminar":

            eliminar($pdo);

            break;

        /* =============================================
           DEFAULT
        ============================================== */

        default:

            throw new Exception(

                "La acción solicitada no es válida."

            );

>>>>>>> 3e2d89c (Actualización del proyecto)
    }

} catch (PDOException $e) {

<<<<<<< HEAD
    error_log($e->getMessage());

    redirect(
        "../views/seguimientos/index.php",
        "error",
        "Error en base de datos."
=======
    if ($pdo->inTransaction()) {

        $pdo->rollBack();

    }

    error_log($e->getMessage());

    redirect(

        "../views/seguimientos/index.php",

        "error",

        "Ocurrió un error interno en la base de datos."

>>>>>>> 3e2d89c (Actualización del proyecto)
    );

} catch (Exception $e) {

<<<<<<< HEAD
    redirect(
        "../views/seguimientos/index.php",
        "error",
        $e->getMessage()
    );
}


/* =====================================================
   CREAR SEGUIMIENTO
===================================================== */

function crearSeguimiento(PDO $pdo): void
{
    if (!tienePermiso('gestionar_seguimientos')) {

        throw new Exception(
            'Acceso denegado.'
        );
    }

    validarCsrf();

    $joven_id = (int) (
        $_POST['joven_id'] ?? 0
    );

    $fecha_contacto =
        $_POST['fecha_contacto'] ?? null;

    $modalidad = trim(
        $_POST['modalidad_contacto'] ?? ''
    );

    $estado = trim(
        $_POST['estado_proceso']
        ?? 'PENDIENTE'
    );

    $responsable_id = (int) (
        $_POST['responsable_id'] ?? 0
    );

    $observaciones = trim(
        $_POST['observaciones'] ?? ''
    );

    $observaciones =
        $observaciones !== ''
        ? $observaciones
        : null;

    $responsable_id =
        $responsable_id > 0
        ? $responsable_id
        : null;

    /* =========================
       VALIDACIONES
    ========================= */

    if (!validarId($joven_id)) {

        throw new Exception(
            'Joven inválido.'
        );
    }

    if (
        !validarFecha($fecha_contacto) ||
        $fecha_contacto > date('Y-m-d')
    ) {

        throw new Exception(
            'La fecha no puede ser futura.'
        );
    }

    if (
        !validarOpcion(
            $modalidad,
            MODALIDADES_VALIDAS
        )
    ) {

        throw new Exception(
            'Modalidad inválida.'
        );
    }

    if (
        !validarOpcion(
            $estado,
            ESTADOS_VALIDOS
        )
    ) {

        $estado = 'PENDIENTE';
    }

    if (
        $observaciones !== null
        && mb_strlen($observaciones) > 2000
    ) {

        throw new Exception(
            'Las observaciones son demasiado largas.'
        );
    }

    /* =========================
       VALIDAR JOVEN
    ========================= */

    $joven = obtenerJovenPorId(
        $joven_id
    );

    if (!$joven) {

        throw new Exception(
            'El joven no existe.'
        );
    }

    /* =========================
       VALIDAR RESPONSABLE
    ========================= */

    if ($responsable_id !== null) {

        $stmt = $pdo->prepare("
            SELECT id
            FROM usuarios
            WHERE id = :id
            LIMIT 1
        ");

        $stmt->execute([
            ':id' => $responsable_id
        ]);

        if (!$stmt->fetch()) {

            throw new Exception(
                'El responsable seleccionado no existe.'
            );
        }
    }

    /* =========================
       EVITAR DUPLICADOS
    ========================= */

    $stmt = $pdo->prepare("
        SELECT id

        FROM seguimientos

        WHERE joven_id = :joven_id

        AND MONTH(fecha_contacto) = MONTH(:fecha)

        AND YEAR(fecha_contacto) = YEAR(:fecha)

        LIMIT 1
    ");

    $stmt->execute([

        ':joven_id' => $joven_id,

        ':fecha' => $fecha_contacto

    ]);

    if ($stmt->fetch()) {

        throw new Exception(
            'Este joven ya tiene un seguimiento registrado durante este mes.'
        );
    }

    /* =========================
       TRANSACCIÓN
    ========================= */

    $pdo->beginTransaction();

    try {

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

            'joven_id' =>
                $joven_id,

            'fecha_contacto' =>
                $fecha_contacto,

            'modalidad' =>
                $modalidad,

            'estado' =>
                $estado,

            'responsable_id' =>
                $responsable_id,

            'observaciones' =>
                $observaciones

        ]);

        $stmt = $pdo->prepare("
            UPDATE jovenes

            SET

                ultima_actividad = NOW(),

                estado_actividad = 'ACTIVO'

            WHERE id = :id
        ");

        $stmt->execute([
            'id' => $joven_id
        ]);

        $pdo->commit();

    } catch (Exception $e) {

        $pdo->rollBack();

        throw $e;
    }

    redirect(
        "../views/jovenes/ver.php?id={$joven_id}",
        "success",
        "Seguimiento registrado correctamente."
    );
=======
    if ($pdo->inTransaction()) {

        $pdo->rollBack();

    }

    redirect(

        "../views/seguimientos/index.php",

        "error",

        $e->getMessage()

    );

}                                                                                                                                               /* =========================================================
   CREAR
========================================================= */

function crear(PDO $pdo): void
{
    $datos = [

        "joven_id"            => (int) ($_POST["joven_id"] ?? 0),

        "fecha_contacto"      => $_POST["fecha_contacto"] ?? "",

        "modalidad_contacto"  => $_POST["modalidad_contacto"] ?? "",

        "estado_proceso"      => $_POST["estado_proceso"] ?? "PENDIENTE",

        "responsable_id"      => $_POST["responsable_id"] ?? null,

        "observaciones"       => $_POST["observaciones"] ?? ""

    ];

    /* =====================================
       VALIDAR JOVEN
    ====================================== */

    if (!validarId($datos["joven_id"])) {

        throw new Exception(

            "El joven seleccionado no es válido."

        );

    }

    if (!existeJoven(

        $pdo,

        $datos["joven_id"]

    )) {

        throw new Exception(

            "El joven no existe."

        );

    }

    /* =====================================
       VALIDAR RESTO DE DATOS
    ====================================== */

    validarDatosSeguimiento(

        $pdo,

        $datos

    );

    /* =====================================
       GUARDAR
    ====================================== */

    crearSeguimiento(

        $pdo,

        $datos

    );

    redirect(

        "../views/jovenes/ver.php?id={$datos["joven_id"]}",

        "success",

        "Seguimiento registrado correctamente."

    );
}

/* =========================================================
   EDITAR
========================================================= */

function editar(PDO $pdo): void
{
    $id = (int) ($_POST["id"] ?? 0);

    if (!validarId($id)) {

        throw new Exception(

            "El seguimiento seleccionado no es válido."

        );

    }

    if (!existeSeguimiento(

        $pdo,

        $id

    )) {

        throw new Exception(

            "El seguimiento no existe."

        );

    }

    $datos = [

        "fecha_contacto"      => $_POST["fecha_contacto"] ?? "",

        "modalidad_contacto"  => $_POST["modalidad_contacto"] ?? "",

        "estado_proceso"      => $_POST["estado_proceso"] ?? "",

        "responsable_id"      => $_POST["responsable_id"] ?? null,

        "observaciones"       => $_POST["observaciones"] ?? ""

    ];

    /* =====================================
       VALIDAR DATOS
    ====================================== */

    validarDatosSeguimiento(

        $pdo,

        $datos

    );

    /* =====================================
       ACTUALIZAR
    ====================================== */

    actualizarSeguimiento(

        $pdo,

        $id,

        $datos

    );

    redirect(

        "../views/seguimientos/index.php",

        "success",

        "Seguimiento actualizado correctamente."

    );
}

/* =========================================================
   ELIMINAR
========================================================= */

function eliminar(PDO $pdo): void
{
    $id = (int) ($_POST["id"] ?? 0);

    if (!validarId($id)) {

        throw new Exception(

            "El seguimiento seleccionado no es válido."

        );

    }

    if (!existeSeguimiento(

        $pdo,

        $id

    )) {

        throw new Exception(

            "El seguimiento no existe."

        );

    }

    eliminarSeguimiento(

        $pdo,

        $id

    );

    redirect(

        "../views/seguimientos/index.php",

        "success",

        "Seguimiento eliminado correctamente."

    );
}                                                                                                                                                /* =========================================================
   VALIDAR DATOS DEL SEGUIMIENTO
========================================================= */

function validarDatosSeguimiento(
    PDO $pdo,
    array &$datos
): void
{
    /* =====================================
       NORMALIZAR
    ====================================== */

    $datos["fecha_contacto"] = trim(

        (string) ($datos["fecha_contacto"] ?? "")

    );

    $datos["modalidad_contacto"] = strtoupper(

        trim(

            (string) ($datos["modalidad_contacto"] ?? "")

        )

    );

    $datos["estado_proceso"] = strtoupper(

        trim(

            (string) ($datos["estado_proceso"] ?? "")

        )

    );

    $datos["observaciones"] = trim(

        (string) ($datos["observaciones"] ?? "")

    );

    $datos["responsable_id"] = (int) (

        $datos["responsable_id"] ?? 0

    );

    if ($datos["responsable_id"] <= 0) {

        $datos["responsable_id"] = null;

    }

    if ($datos["observaciones"] === "") {

        $datos["observaciones"] = null;

    }

    /* =====================================
       FECHA
    ====================================== */

    if (!validarFecha($datos["fecha_contacto"])) {

        throw new Exception(

            "La fecha es inválida."

        );

    }

    if (

        strtotime($datos["fecha_contacto"]) > time()

    ) {

        throw new Exception(

            "La fecha no puede ser futura."

        );

    }

    /* =====================================
       MODALIDAD
    ====================================== */

    if (

        !in_array(

            $datos["modalidad_contacto"],

            MODALIDADES_SEGUIMIENTO,

            true

        )

    ) {

        throw new Exception(

            "La modalidad seleccionada no es válida."

        );

    }

    /* =====================================
       ESTADO
    ====================================== */

    if (

        !in_array(

            $datos["estado_proceso"],

            ESTADOS_SEGUIMIENTO,

            true

        )

    ) {

        throw new Exception(

            "El estado seleccionado no es válido."

        );

    }

    /* =====================================
       RESPONSABLE
    ====================================== */

    if (

        !existeResponsable(

            $pdo,

            $datos["responsable_id"]

        )

    ) {

        throw new Exception(

            "El responsable seleccionado no existe."

        );

    }

    /* =====================================
       OBSERVACIONES
    ====================================== */

    if (

        $datos["observaciones"] !== null

        &&

        mb_strlen(

            $datos["observaciones"]

        ) > 2000

    ) {

        throw new Exception(

            "Las observaciones no pueden superar los 2000 caracteres."

        );

    }
>>>>>>> 3e2d89c (Actualización del proyecto)
}