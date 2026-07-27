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

$pdo->setAttribute(
    PDO::ATTR_ERRMODE,
    PDO::ERRMODE_EXCEPTION
);

try {

    if (isset($_POST["eliminar_joven"])) {

        eliminarJoven($pdo);
    }

    if (isset($_POST["recuperar_joven"])) {

        recuperarJoven($pdo);
    }

    if (isset($_POST["eliminar_definitivo"])) {

        eliminarDefinitivo($pdo);
    }

    if (isset($_POST["crear_joven"])) {

        crearJoven($pdo);
    }

    if (isset($_POST["editar_joven"])) {

        editarJoven($pdo);
=======
declare(strict_types=1);

session_start();

require_once __DIR__ . "/../config/conexion.php";

require_once __DIR__ . "/../helpers/redirect.php";
require_once __DIR__ . "/../helpers/csrf.php";
require_once __DIR__ . "/../helpers/validaciones.php";

require_once __DIR__ . "/../middleware/auth.php";
require_once __DIR__ . "/../middleware/permiso.php";

require_once __DIR__ . "/../services/jovenService.php";

/* =========================================================
   SOLO PETICIONES POST
========================================================= */

echo "1 - Entró al controlador<br>";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    die("NO ES POST");

}

echo "2 - Es POST<br>";

/* =========================================================
   CSRF
========================================================= */

validarCsrf();

echo "3 - Pasó CSRF<br>";

/* =========================================================
   ACCIÓN
========================================================= */

$action = strtolower(

    trim(

        (string) ($_POST["action"] ?? "")

    )

);

/* =========================================================
   RUTA
========================================================= */

$redirect = "../views/jovenes/index.php";

/* =========================================================
   CONTROLADOR
========================================================= */

try {

    switch ($action) {

        case "crear_joven":

            crearJoven($pdo, $_POST);

            redirect(
                $redirect,
                "success",
                "Joven creado correctamente."
            );

            break;

        case "editar_joven":

            editarJoven($pdo, $_POST);

            redirect(
                $redirect,
                "success",
                "Joven actualizado correctamente."
            );

            break;

        case "eliminar_joven":

            eliminarJoven($pdo, $_POST);

            redirect(
                $redirect,
                "success",
                "Joven eliminado correctamente."
            );

            break;

        case "recuperar_joven":

            recuperarJoven($pdo, $_POST);

            redirect(
                $redirect . "?filtro=eliminados",
                "success",
                "Joven recuperado correctamente."
            );

            break;

        case "eliminar_definitivo":

            eliminarDefinitivo($pdo, $_POST);

            redirect(
                $redirect . "?filtro=eliminados",
                "success",
                "Joven eliminado definitivamente."
            );

            break;

        default:

            throw new Exception(
                "Acción no válida."
            );

>>>>>>> 3e2d89c (Actualización del proyecto)
    }

} catch (PDOException $e) {

    error_log($e->getMessage());

    redirect(
<<<<<<< HEAD
        "../views/jovenes/index.php",
        "error",
        "Error en base de datos."
=======
        $redirect,
        "error",
        "Ocurrió un error interno del sistema."
>>>>>>> 3e2d89c (Actualización del proyecto)
    );

} catch (Exception $e) {

    redirect(
<<<<<<< HEAD
        "../views/jovenes/index.php",
        "error",
        $e->getMessage()
    );
}


/* =========================================================
   ELIMINAR JOVEN
========================================================= */

function eliminarJoven(PDO $pdo): void
{
    if (!tienePermiso('eliminar_jovenes')) {

        throw new Exception(
            "Acceso denegado."
        );
    }

    validarCsrf();

    $id = (int) ($_POST["id"] ?? 0);

    if (!validarId($id)) {

        throw new Exception(
            "ID inválido."
        );
    }

    if (!existeJoven($pdo, $id)) {

        throw new Exception(
            "El joven no existe."
        );
    }

    $stmt = $pdo->prepare("
        UPDATE jovenes
        SET estado_actividad = 'ELIMINADO'
        WHERE id = :id
    ");

    $stmt->execute([
        "id" => $id
    ]);

    redirect(
        "../views/jovenes/index.php",
        "success",
        "Joven eliminado correctamente."
    );
}                                                                                                                                                        /* =========================================================
   RECUPERAR JOVEN
========================================================= */

function recuperarJoven(PDO $pdo): void
{
    if (!tienePermiso('gestionar_jovenes')) {

        throw new Exception(
            "Acceso denegado."
        );
    }

    validarCsrf();

    $id = (int) ($_POST["id"] ?? 0);

    if (!validarId($id)) {

        throw new Exception(
            "ID inválido."
        );
    }

    if (!existeJoven($pdo, $id)) {

        throw new Exception(
            "El joven no existe."
        );
    }

    $stmt = $pdo->prepare("
        UPDATE jovenes
        SET estado_actividad = 'ACTIVO'
        WHERE id = :id
    ");

    $stmt->execute([
        "id" => $id
    ]);

    redirect(
        "../views/jovenes/index.php?filtro=eliminados",
        "success",
        "Joven recuperado correctamente."
    );
}


/* =========================================================
   ELIMINAR DEFINITIVAMENTE
========================================================= */

function eliminarDefinitivo(PDO $pdo): void
{
    if (!tienePermiso('eliminar_jovenes')) {

        throw new Exception(
            "Acceso denegado."
        );
    }

    validarCsrf();

    $id = (int) ($_POST["id"] ?? 0);

    if (!validarId($id)) {

        throw new Exception(
            "ID inválido."
        );
    }

    if (!existeJoven($pdo, $id)) {

        throw new Exception(
            "El joven no existe."
        );
    }

    $pdo->beginTransaction();

    try {

        $stmt = $pdo->prepare("
            DELETE FROM asistencia
            WHERE joven_id = :id
        ");

        $stmt->execute([
            "id" => $id
        ]);

        $stmt = $pdo->prepare("
            DELETE FROM seguimientos
            WHERE joven_id = :id
        ");

        $stmt->execute([
            "id" => $id
        ]);

        $stmt = $pdo->prepare("
            DELETE FROM jovenes
            WHERE id = :id
        ");

        $stmt->execute([
            "id" => $id
        ]);

        $pdo->commit();

    } catch (Exception $e) {

        $pdo->rollBack();

        throw $e;
    }

    redirect(
        "../views/jovenes/index.php?filtro=eliminados",
        "success",
        "Joven eliminado definitivamente."
    );
}                                                                                                                                                   /* =========================================================
   CREAR JOVEN
========================================================= */

function crearJoven(PDO $pdo): void
{
    if (!tienePermiso('gestionar_jovenes')) {

        throw new Exception(
            "Acceso denegado."
        );
    }

    validarCsrf();

    $datos = prepararDatosJoven($pdo);

    $pdo->beginTransaction();

    try {

        $stmt = $pdo->prepare("
            INSERT INTO jovenes (

                nombre_completo,
                fecha_nacimiento,
                edad_manual,
                fecha_actualizacion_edad,
                telefono,
                genero,
                estado_espiritual,
                fecha_ingreso,
                es_servidor,
                observaciones,
                estado_actividad

            ) VALUES (

                :nombre,
                :fn,
                :edad,
                :fa,
                :tel,
                :genero,
                :estado,
                :fi,
                :serv,
                :obs,
                'ACTIVO'
            )
        ");

        $stmt->execute([

            "nombre" => $datos["nombre"],

            "fn" => $datos["fechaNacimiento"],

            "edad" => $datos["edadManual"],

            "fa" => $datos["fechaActualizacionEdad"],

            "tel" => $datos["telefono"],

            "genero" => $datos["genero"],

            "estado" => $datos["estadoEspiritual"],

            "fi" => $datos["fechaIngreso"],

            "serv" => $datos["esServidor"],

            "obs" => $datos["observaciones"]

        ]);

        /* =====================================
           ID DEL NUEVO JOVEN
        ===================================== */

        $jovenId = (int)$pdo->lastInsertId();

        // Reservado para futuras ampliaciones:
        // - Auditoría
        // - Historial
        // - Registro de actividad

        $pdo->commit();

    } catch (Exception $e) {

        $pdo->rollBack();

        throw $e;
    }

    redirect(
        "../views/jovenes/index.php",
        "success",
        "Joven creado correctamente."
    );
}                                                                                                                                                         /* =========================================================
   EDITAR JOVEN
========================================================= */

function editarJoven(PDO $pdo): void
{
    if (!tienePermiso('gestionar_jovenes')) {

        throw new Exception(
            "Acceso denegado."
        );
    }

    validarCsrf();

    $id = (int) ($_POST["id"] ?? 0);

    if (!validarId($id)) {

        throw new Exception(
            "ID inválido."
        );
    }

    if (!existeJoven($pdo, $id)) {

        throw new Exception(
            "El joven no existe."
        );
    }

    $datos = prepararDatosJoven(
        $pdo,
        $id
    );

    $stmt = $pdo->prepare("
        UPDATE jovenes

        SET

            nombre_completo = :nombre,
            fecha_nacimiento = :fn,
            edad_manual = :edad,
            fecha_actualizacion_edad = :fa,
            telefono = :tel,
            genero = :genero,
            estado_espiritual = :estado,
            fecha_ingreso = :fi,
            es_servidor = :serv,
            observaciones = :obs

        WHERE id = :id
    ");

    $stmt->execute([

        "nombre" => $datos["nombre"],

        "fn" => $datos["fechaNacimiento"],

        "edad" => $datos["edadManual"],

        "fa" => $datos["fechaActualizacionEdad"],

        "tel" => $datos["telefono"],

        "genero" => $datos["genero"],

        "estado" => $datos["estadoEspiritual"],

        "fi" => $datos["fechaIngreso"],

        "serv" => $datos["esServidor"],

        "obs" => $datos["observaciones"],

        "id" => $id
    ]);

    redirect(
        "../views/jovenes/index.php",
        "success",
        "Joven actualizado correctamente."
    );
}


/* =========================================================
   EXISTE JOVEN
========================================================= */

function existeJoven(
    PDO $pdo,
    int $id
): bool {

    $stmt = $pdo->prepare("
        SELECT id
        FROM jovenes
        WHERE id = :id
        LIMIT 1
    ");

    $stmt->execute([
        "id" => $id
    ]);

    return (bool)$stmt->fetchColumn();
=======
        $redirect,
        "error",
        $e->getMessage()
    );

>>>>>>> 3e2d89c (Actualización del proyecto)
}