<?php

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
    }

} catch (PDOException $e) {

    error_log($e->getMessage());

    redirect(
        "../views/seguimientos/index.php",
        "error",
        "Error en base de datos."
    );

} catch (Exception $e) {

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
}