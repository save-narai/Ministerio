<?php

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
    }

} catch (PDOException $e) {

    error_log($e->getMessage());

    redirect(
        "../views/jovenes/index.php",
        "error",
        "Error en base de datos."
    );

} catch (Exception $e) {

    redirect(
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

    $stmt = $pdo->prepare("
        SELECT id
        FROM jovenes
        WHERE id = :id
    ");

    $stmt->execute([
        "id" => $id
    ]);

    if (!$stmt->fetch()) {

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
}


/* =========================================================
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
}


/* =========================================================
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

        "serv" => $datos["esServidor"]
    ]);

    redirect(
        "../views/jovenes/index.php",
        "success",
        "Joven creado correctamente."
    );
}


/* =========================================================
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

    $stmt = $pdo->prepare("
        SELECT id
        FROM jovenes
        WHERE id = :id
    ");

    $stmt->execute([
        "id" => $id
    ]);

    if (!$stmt->fetch()) {

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