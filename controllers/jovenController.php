<?php

session_start();

require_once "../middleware/auth.php";
require_once "../middleware/permiso.php";
require_once "../config/conexion.php";

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* ============================
   CONFIG
============================ */

const GENEROS_VALIDOS = ['M', 'F'];

/* ============================
   HELPERS
============================ */

function redireccionar($ruta, $mensaje){

    $_SESSION["error"] = $mensaje;

    header("Location: $ruta");

    exit;
}

function validarCsrf(){

    if (
        !isset($_POST["csrf_token"]) ||
        !isset($_SESSION["csrf_token"]) ||
        $_POST["csrf_token"] !== $_SESSION["csrf_token"]
    ){
        die("Token CSRF inválido");
    }
}

function limpiarNombre($nombre){

    $nombre = trim($nombre);

    $nombre = preg_replace('/\s+/', ' ', $nombre);

    if (!preg_match('/^[\p{L}\'\- ]+$/u', $nombre)){

        return [false, "Nombre inválido"];
    }

    if (mb_strlen($nombre) < 3){

        return [false, "Nombre demasiado corto"];
    }

    $nombre = mb_convert_case(
        $nombre,
        MB_CASE_TITLE,
        "UTF-8"
    );

    return [true, $nombre];
}

function validarTelefono($telefono){

    $telefono = preg_replace('/\D/', '', $telefono);

    if (!preg_match('/^3\d{9}$/', $telefono)){

        return [false, "Teléfono inválido"];
    }

    if (preg_match('/^(\d)\1+$/', $telefono)){

        return [false, "Teléfono inválido"];
    }

    return [true, $telefono];
}

/* ============================
   ELIMINAR (SOFT DELETE)
============================ */

if (isset($_POST["eliminar_joven"])) {

    if (!tienePermiso('eliminar_jovenes')) {

        die("Acceso denegado.");
    }

    validarCsrf();

    $id = (int)($_POST["id"] ?? 0);

    $stmt = $pdo->prepare("
        SELECT id
        FROM jovenes
        WHERE id = :id
    ");

    $stmt->execute([
        "id" => $id
    ]);

    if (!$stmt->fetch()) {

        $_SESSION["error"] = "Joven no existe";

        header("Location: ../views/jovenes/index.php");

        exit;
    }

    $stmt = $pdo->prepare("
        UPDATE jovenes

        SET estado_actividad = 'ELIMINADO'

        WHERE id = :id
    ");

    $stmt->execute([
        "id" => $id
    ]);

    $_SESSION["success"] = "Joven eliminado correctamente";

    header("Location: ../views/jovenes/index.php");

    exit;
}

/* ============================
   RECUPERAR JOVEN
============================ */

if (isset($_POST["recuperar_joven"])) {

    if (!tienePermiso('gestionar_jovenes')) {

        die("Acceso denegado.");
    }

    validarCsrf();

    $id = (int)($_POST["id"] ?? 0);

    $stmt = $pdo->prepare("
        UPDATE jovenes

        SET estado_actividad = 'ACTIVO'

        WHERE id = :id
    ");

    $stmt->execute([
        "id" => $id
    ]);

    $_SESSION["success"] =
        "Joven recuperado correctamente";

    header(
        "Location: ../views/jovenes/index.php?filtro=eliminados"
    );

    exit;
}

/* ============================
   ELIMINAR DEFINITIVO
============================ */

if (isset($_POST["eliminar_definitivo"])) {

    if (!tienePermiso('eliminar_jovenes')) {

        die("Acceso denegado.");
    }

    validarCsrf();

    $id = (int)($_POST["id"] ?? 0);

    /*
    |--------------------------------------------------------------------------
    | ELIMINAR RELACIONES
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | ELIMINAR JOVEN
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        DELETE FROM jovenes
        WHERE id = :id
    ");

    $stmt->execute([
        "id" => $id
    ]);

    $_SESSION["success"] =
        "Joven eliminado definitivamente";

    header(
        "Location: ../views/jovenes/index.php?filtro=eliminados"
    );

    exit;
}

/* ============================
   CREAR JOVEN
============================ */

if (isset($_POST["crear_joven"])) {

    if (!tienePermiso('gestionar_jovenes')) {

        die("Acceso denegado.");
    }

    validarCsrf();

    /*
    |--------------------------------------------------------------------------
    | NOMBRE
    |--------------------------------------------------------------------------
    */

    [$ok, $nombre] = limpiarNombre(
        $_POST["nombre_completo"] ?? ''
    );

    if (!$ok){

        redireccionar(
            "../views/jovenes/crear.php",
            $nombre
        );
    }

    /*
    |--------------------------------------------------------------------------
    | GÉNERO
    |--------------------------------------------------------------------------
    */

    $genero = $_POST["genero"] ?? null;

    if (
        $genero &&
        !in_array($genero, GENEROS_VALIDOS)
    ) {

        redireccionar(
            "../views/jovenes/crear.php",
            "Género inválido"
        );
    }

    /*
    |--------------------------------------------------------------------------
    | FECHA INGRESO
    |--------------------------------------------------------------------------
    */

    $fechaIngreso =
        $_POST["fecha_ingreso"] ?? null;

    if (
        !$fechaIngreso ||
        !strtotime($fechaIngreso)
    ) {

        redireccionar(
            "../views/jovenes/crear.php",
            "Fecha ingreso inválida"
        );
    }

    /*
    |--------------------------------------------------------------------------
    | EDAD / FECHA NACIMIENTO
    |--------------------------------------------------------------------------
    */

    $fechaNacimiento =
        $_POST["fecha_nacimiento"] ?: null;

    $edadManual =
        $_POST["edad_manual"] ?: null;

    if (!$fechaNacimiento && !$edadManual){

        redireccionar(
            "../views/jovenes/crear.php",
            "Debes ingresar edad o fecha"
        );
    }

    if ($fechaNacimiento){

        $edadManual = null;

        $fechaActualizacionEdad = null;

    } else {

        $fechaActualizacionEdad = date("Y-m-d");
    }

    /*
    |--------------------------------------------------------------------------
    | TELÉFONO
    |--------------------------------------------------------------------------
    */

    $sinTelefono =
        isset($_POST["sinTelefono"]);

    $telefono =
        $_POST["telefono"] ?? '';

    if ($sinTelefono){

        $telefonoFinal = null;

    } else {

        if (empty($telefono)){

            redireccionar(
                "../views/jovenes/crear.php",
                "Debes ingresar teléfono"
            );
        }

        [$okTel, $telefono] =
            validarTelefono($telefono);

        if (!$okTel){

            redireccionar(
                "../views/jovenes/crear.php",
                $telefono
            );
        }

        $telefonoFinal = $telefono;
    }

    /*
    |--------------------------------------------------------------------------
    | DUPLICADOS
    |--------------------------------------------------------------------------
    */

    if ($telefonoFinal){

        $stmt = $pdo->prepare("
            SELECT COUNT(*)

            FROM jovenes

            WHERE nombre_completo = :nombre

            AND telefono = :tel
        ");

        $stmt->execute([
            "nombre" => $nombre,
            "tel" => $telefonoFinal
        ]);

        if ($stmt->fetchColumn() > 0){

            redireccionar(
                "../views/jovenes/crear.php",
                "Este joven ya existe"
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | INSERT
    |--------------------------------------------------------------------------
    */

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

        "nombre" => $nombre,

        "fn" => $fechaNacimiento,

        "edad" => $edadManual,

        "fa" => $fechaActualizacionEdad,

        "tel" => $telefonoFinal,

        "genero" => $genero,

        "estado" =>
            $_POST["estado_espiritual"] ?? null,

        "fi" => $fechaIngreso,

        "serv" =>
            $_POST["es_servidor"] ?? 0
    ]);

    $_SESSION["success"] =
        "Joven creado correctamente";

    header("Location: ../views/jovenes/index.php");

    exit;
}

/* ============================
   EDITAR JOVEN
============================ */

if (isset($_POST["editar_joven"])) {

    if (!tienePermiso('gestionar_jovenes')) {

        die("Acceso denegado.");
    }

    validarCsrf();

    $id = (int)($_POST["id"] ?? 0);

    [$ok, $nombre] = limpiarNombre(
        $_POST["nombre_completo"] ?? ''
    );

    if (!$ok){

        redireccionar(
            "../views/jovenes/editar.php?id=".$id,
            $nombre
        );
    }

    /*
    |--------------------------------------------------------------------------
    | GÉNERO
    |--------------------------------------------------------------------------
    */

    $genero = $_POST["genero"] ?? null;

    if (
        $genero &&
        !in_array($genero, GENEROS_VALIDOS)
    ) {

        redireccionar(
            "../views/jovenes/editar.php?id=".$id,
            "Género inválido"
        );
    }

    /*
    |--------------------------------------------------------------------------
    | FECHA INGRESO
    |--------------------------------------------------------------------------
    */

    $fechaIngreso =
        $_POST["fecha_ingreso"] ?? null;

    if (
        !$fechaIngreso ||
        !strtotime($fechaIngreso)
    ) {

        redireccionar(
            "../views/jovenes/editar.php?id=".$id,
            "Fecha ingreso inválida"
        );
    }

    /*
    |--------------------------------------------------------------------------
    | EDAD
    |--------------------------------------------------------------------------
    */

    $fechaNacimiento =
        $_POST["fecha_nacimiento"] ?: null;

    $edadManual =
        $_POST["edad_manual"] ?: null;

    if (!$fechaNacimiento && !$edadManual){

        redireccionar(
            "../views/jovenes/editar.php?id=".$id,
            "Debes ingresar edad o fecha"
        );
    }

    if ($fechaNacimiento){

        $edadManual = null;

        $fechaActualizacionEdad = null;

    } else {

        $fechaActualizacionEdad = date("Y-m-d");
    }

    /*
    |--------------------------------------------------------------------------
    | TELÉFONO
    |--------------------------------------------------------------------------
    */

    $sinTelefono =
        isset($_POST["sinTelefono"]);

    $telefono =
        $_POST["telefono"] ?? '';

    if ($sinTelefono){

        $telefonoFinal = null;

    } else {

        if (empty($telefono)) {

            redireccionar(
                "../views/jovenes/editar.php?id=".$id,
                "Debes ingresar teléfono"
            );
        }

        [$okTel, $telefono] =
            validarTelefono($telefono);

        if (!$okTel){

            redireccionar(
                "../views/jovenes/editar.php?id=".$id,
                $telefono
            );
        }

        $telefonoFinal = $telefono;
    }

    /*
    |--------------------------------------------------------------------------
    | DUPLICADOS
    |--------------------------------------------------------------------------
    */

    if ($telefonoFinal){

        $stmt = $pdo->prepare("
            SELECT COUNT(*)

            FROM jovenes

            WHERE nombre_completo = :nombre

            AND telefono = :tel

            AND id != :id
        ");

        $stmt->execute([

            "nombre" => $nombre,

            "tel" => $telefonoFinal,

            "id" => $id
        ]);

        if ($stmt->fetchColumn() > 0){

            redireccionar(
                "../views/jovenes/editar.php?id=".$id,
                "Ya existe otro joven con ese nombre y teléfono"
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

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

        "nombre" => $nombre,

        "fn" => $fechaNacimiento,

        "edad" => $edadManual,

        "fa" => $fechaActualizacionEdad,

        "tel" => $telefonoFinal,

        "genero" => $genero,

        "estado" =>
            $_POST["estado_espiritual"] ?? null,

        "fi" => $fechaIngreso,

        "serv" =>
            $_POST["es_servidor"] ?? 0,

        "obs" =>
            trim($_POST["observaciones"] ?? '') ?: null,

        "id" => $id
    ]);

    $_SESSION["success"] =
        "Joven actualizado correctamente";

    header("Location: ../views/jovenes/index.php");

    exit;
}