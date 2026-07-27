<?php

<<<<<<< HEAD


session_start();



require_once '../config/conexion.php';

require_once '../helpers/redirect.php';

require_once '../helpers/csrf.php';

require_once '../middleware/auth.php';

require_once '../middleware/permiso.php';



const ROL_ADMIN = 'ADMIN';



/* =====================================

   SEGURIDAD

===================================== */



if (!tienePermiso('gestionar_roles')) {



    redirect(

        '../views/dashboard.php',

        'error',

        'No tienes permisos para realizar esta acción.'

=======
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../config/conexion.php';

require_once __DIR__ . '/../helpers/redirect.php';
require_once __DIR__ . '/../helpers/csrf.php';

require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../middleware/permiso.php';

const ROL_ADMIN = 'ADMIN';

/* ==========================================================
   SOLO PETICIONES POST
========================================================== */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    redirect(
        '../views/roles/index.php',
        'error',
        'Acceso inválido.'
>>>>>>> 3e2d89c (Actualización del proyecto)
    );

}

<<<<<<< HEAD


validarCSRF();



try {



    /* =====================================

       CREAR ROL

    ===================================== */



    if (isset($_POST['crear_rol'])) {



        crearRol($pdo);



    /* =====================================

       EDITAR ROL

    ===================================== */



    } elseif (

        isset($_POST['editar_rol']) ||

        isset($_POST['guardar_permisos'])

    ) {



        editarRol($pdo);



    /* =====================================

       ELIMINAR ROL

    ===================================== */



    } elseif (isset($_POST['eliminar_rol'])) {



        eliminarRol($pdo);

    }



} catch (Exception $e) {



    if ($pdo->inTransaction()) {
=======
/* ==========================================================
   SEGURIDAD
========================================================== */

if (!tienePermiso('gestionar_roles')) {

    redirect(
        '../views/dashboard.php',
        'error',
        'No tienes permisos para realizar esta acción.'
    );

}

validarCsrf();

/* ==========================================================
   ACCIÓN
========================================================== */

$action = strtolower(

    trim(

        (string) (

            $_POST['action'] ?? ''

        )

    )

);

/* ==========================================================
   CONTROLADOR
========================================================== */

try {

    switch ($action) {

        /* ==================================================
           CREAR ROL
        ================================================== */

        case 'crear_rol':

            crearRol($pdo);

            break;

        /* ==================================================
           EDITAR ROL
        ================================================== */

        case 'editar_rol':

        case 'guardar_permisos':

            editarRol($pdo);

            break;

        /* ==================================================
           ELIMINAR ROL
        ================================================== */

        case 'eliminar_rol':

            eliminarRol($pdo);

            break;

        /* ==================================================
           DEFAULT
        ================================================== */

        default:

            throw new Exception(
                'Acción no válida.'
            );

    }

} catch (Exception $e) {

    if (

        $pdo->inTransaction()

    ) {
>>>>>>> 3e2d89c (Actualización del proyecto)

        $pdo->rollBack();

    }

<<<<<<< HEAD


=======
>>>>>>> 3e2d89c (Actualización del proyecto)
    redirect(

        '../views/roles/index.php',

        'error',

        $e->getMessage()

    );

}

<<<<<<< HEAD




/* =========================================================

   CREAR ROL

========================================================= */



function crearRol(PDO $pdo): void

{

    $nombre = trim($_POST['nombre'] ?? '');



    if (mb_strlen($nombre) > 80) {



        throw new Exception(

            'El nombre del rol es demasiado largo.'

=======
/* ==========================================================
   CREAR ROL
========================================================== */                               function crearRol(PDO $pdo): void
{
    $nombre = strtoupper(

        trim($_POST['nombre'] ?? '')

    );

    if (empty($nombre)) {

        throw new Exception(
            'Debe ingresar el nombre del rol.'
>>>>>>> 3e2d89c (Actualización del proyecto)
        );

    }

<<<<<<< HEAD


    $permisos = $_POST['permisos'] ?? [];



    if (!is_array($permisos)) {



=======
    if (mb_strlen($nombre) > 80) {

        throw new Exception(
            'El nombre del rol es demasiado largo.'
        );

    }

    $permisos = $_POST['permisos'] ?? [];

    if (!is_array($permisos)) {

>>>>>>> 3e2d89c (Actualización del proyecto)
        $permisos = [];

    }

<<<<<<< HEAD


    if (empty($nombre)) {



        throw new Exception(

            'Debe ingresar el nombre del rol.'

        );

    }



    $nombre = strtoupper($nombre);



    $stmt = $pdo->prepare("

        SELECT id

        FROM roles

        WHERE nombre = :nombre

        LIMIT 1

    ");



    $stmt->execute([

        ':nombre' => $nombre

    ]);



    if ($stmt->fetch()) {



        throw new Exception(

            'Ya existe un rol con ese nombre.'

=======
    $stmt = $pdo->prepare("
        SELECT id
        FROM roles
        WHERE nombre = :nombre
        LIMIT 1
    ");

    $stmt->execute([
        ':nombre' => $nombre
    ]);

    if ($stmt->fetch()) {

        throw new Exception(
            'Ya existe un rol con ese nombre.'
>>>>>>> 3e2d89c (Actualización del proyecto)
        );

    }

<<<<<<< HEAD


    $pdo->beginTransaction();



    $stmt = $pdo->prepare("

        INSERT INTO roles(nombre)

        VALUES(:nombre)

    ");



    $stmt->execute([

        ':nombre' => $nombre

    ]);



    $rolId = (int) $pdo->lastInsertId();


=======
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        INSERT INTO roles (
            nombre
        )
        VALUES (
            :nombre
        )
    ");

    $stmt->execute([
        ':nombre' => $nombre
    ]);

    $rolId = (int)$pdo->lastInsertId();
>>>>>>> 3e2d89c (Actualización del proyecto)

    guardarPermisos(

        $pdo,

        $rolId,

        $permisos

    );

<<<<<<< HEAD


    $pdo->commit();



=======
    $pdo->commit();

>>>>>>> 3e2d89c (Actualización del proyecto)
    redirect(

        '../views/roles/index.php',

        'success',

        'Rol creado correctamente.'

    );
<<<<<<< HEAD

}





/* =========================================================

   EDITAR ROL

========================================================= */



function editarRol(PDO $pdo): void

{

    $id = (int) (

        $_POST['id']

        ?? $_POST['rol_id']

        ?? 0

    );



    $nombre = trim($_POST['nombre'] ?? '');



    if (mb_strlen($nombre) > 80) {



        throw new Exception(

            'El nombre del rol es demasiado largo.'

=======
}

/* =========================================================
   EDITAR ROL
========================================================= */

function editarRol(PDO $pdo): void
{
    $id = (int)(

        $_POST['id']
        ??
        $_POST['rol_id']
        ??
        0

    );

    if ($id <= 0) {

        throw new Exception(
            'Rol inválido.'
>>>>>>> 3e2d89c (Actualización del proyecto)
        );

    }

<<<<<<< HEAD


    $permisos = $_POST['permisos'] ?? [];



    if (!is_array($permisos)) {



=======
    $nombre = strtoupper(

        trim($_POST['nombre'] ?? '')

    );

    $permisos = $_POST['permisos'] ?? [];

    if (!is_array($permisos)) {

>>>>>>> 3e2d89c (Actualización del proyecto)
        $permisos = [];

    }

<<<<<<< HEAD


    if ($id <= 0) {



        throw new Exception(

            'Rol inválido.'

        );

    }



    $pdo->beginTransaction();



    /* VALIDAR DUPLICADO */



    if (!empty($nombre)) {



        $nombre = strtoupper($nombre);



        $stmt = $pdo->prepare("

            SELECT id

            FROM roles

            WHERE nombre = :nombre

            AND id != :id

            LIMIT 1

        ");



        $stmt->execute([

            ':nombre' => $nombre,

            ':id'     => $id

        ]);



        if ($stmt->fetch()) {



            throw new Exception(

                'Ya existe un rol con ese nombre.'

=======
    $pdo->beginTransaction();

    if (!empty($nombre)) {

        if (mb_strlen($nombre) > 80) {

            throw new Exception(
                'El nombre del rol es demasiado largo.'
>>>>>>> 3e2d89c (Actualización del proyecto)
            );

        }

<<<<<<< HEAD


        $stmt = $pdo->prepare("

            UPDATE roles

            SET nombre = :nombre

            WHERE id = :id

        ");



=======
        $stmt = $pdo->prepare("
            SELECT id
            FROM roles
            WHERE nombre = :nombre
            AND id <> :id
            LIMIT 1
        ");

>>>>>>> 3e2d89c (Actualización del proyecto)
        $stmt->execute([

            ':nombre' => $nombre,

<<<<<<< HEAD
            ':id'     => $id
=======
            ':id' => $id

        ]);

        if ($stmt->fetch()) {

            throw new Exception(
                'Ya existe un rol con ese nombre.'
            );

        }

        $stmt = $pdo->prepare("
            UPDATE roles
            SET nombre = :nombre
            WHERE id = :id
        ");

        $stmt->execute([

            ':nombre' => $nombre,

            ':id' => $id
>>>>>>> 3e2d89c (Actualización del proyecto)

        ]);

    }

<<<<<<< HEAD


    /* ELIMINAR PERMISOS ACTUALES */



    $stmt = $pdo->prepare("

        DELETE FROM rol_permiso

        WHERE rol_id = :rol_id

    ");



    $stmt->execute([

        ':rol_id' => $id

    ]);



    /* INSERTAR NUEVOS PERMISOS */



=======
    $stmt = $pdo->prepare("
        DELETE
        FROM rol_permiso
        WHERE rol_id = :rol
    ");

    $stmt->execute([

        ':rol' => $id

    ]);

>>>>>>> 3e2d89c (Actualización del proyecto)
    guardarPermisos(

        $pdo,

        $id,

        $permisos

    );

<<<<<<< HEAD


    $pdo->commit();



=======
    $pdo->commit();

>>>>>>> 3e2d89c (Actualización del proyecto)
    redirect(

        '../views/roles/index.php',

        'success',

        'Rol actualizado correctamente.'

    );
<<<<<<< HEAD

}                                                                                                                                                                            /* =========================================================

   ELIMINAR ROL

========================================================= */



function eliminarRol(PDO $pdo): void

{

    $id = (int) ($_POST['id'] ?? 0);



    if ($id <= 0) {



        throw new Exception(

            'Rol inválido.'

=======
}

/* =========================================================
   ELIMINAR ROL
========================================================= */

function eliminarRol(PDO $pdo): void
{
    $id = (int)(

        $_POST['id'] ?? 0

    );

    if ($id <= 0) {

        throw new Exception(
            'Rol inválido.'
>>>>>>> 3e2d89c (Actualización del proyecto)
        );

    }

<<<<<<< HEAD


    $stmt = $pdo->prepare("

        SELECT

            nombre

        FROM roles

        WHERE id = :id

        LIMIT 1

    ");



=======
    $stmt = $pdo->prepare("
        SELECT nombre
        FROM roles
        WHERE id = :id
        LIMIT 1
    ");

>>>>>>> 3e2d89c (Actualización del proyecto)
    $stmt->execute([

        ':id' => $id

    ]);

<<<<<<< HEAD


    $rol = $stmt->fetch(PDO::FETCH_ASSOC);



    if (!$rol) {



        throw new Exception(

            'Rol no encontrado.'

=======
    $rol = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$rol) {

        throw new Exception(
            'Rol no encontrado.'
>>>>>>> 3e2d89c (Actualización del proyecto)
        );

    }

<<<<<<< HEAD


    if ($rol['nombre'] === ROL_ADMIN) {



        throw new Exception(

            'No se puede eliminar el rol ADMIN.'

=======
    if ($rol['nombre'] === ROL_ADMIN) {

        throw new Exception(
            'No se puede eliminar el rol ADMIN.'
>>>>>>> 3e2d89c (Actualización del proyecto)
        );

    }

<<<<<<< HEAD


    /* VALIDAR USUARIOS ASIGNADOS */



    $stmt = $pdo->prepare("

        SELECT COUNT(*)

        FROM usuarios

        WHERE rol_id = :id

    ");



=======
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM usuarios
        WHERE rol_id = :id
    ");

>>>>>>> 3e2d89c (Actualización del proyecto)
    $stmt->execute([

        ':id' => $id

    ]);

<<<<<<< HEAD


    if ((int) $stmt->fetchColumn() > 0) {



        throw new Exception(

            'No puedes eliminar un rol que tiene usuarios asignados.'

=======
    if ((int)$stmt->fetchColumn() > 0) {

        throw new Exception(
            'No puedes eliminar un rol que tiene usuarios asignados.'
>>>>>>> 3e2d89c (Actualización del proyecto)
        );

    }

<<<<<<< HEAD


    $pdo->beginTransaction();



    $stmt = $pdo->prepare("

        DELETE FROM rol_permiso

        WHERE rol_id = :id

    ");



=======
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        DELETE
        FROM rol_permiso
        WHERE rol_id = :id
    ");

>>>>>>> 3e2d89c (Actualización del proyecto)
    $stmt->execute([

        ':id' => $id

    ]);

<<<<<<< HEAD


    $stmt = $pdo->prepare("

        DELETE FROM roles

        WHERE id = :id

    ");



=======
    $stmt = $pdo->prepare("
        DELETE
        FROM roles
        WHERE id = :id
    ");

>>>>>>> 3e2d89c (Actualización del proyecto)
    $stmt->execute([

        ':id' => $id

    ]);

<<<<<<< HEAD


    $pdo->commit();



=======
    $pdo->commit();

>>>>>>> 3e2d89c (Actualización del proyecto)
    redirect(

        '../views/roles/index.php',

        'success',

        'Rol eliminado correctamente.'

    );
<<<<<<< HEAD

}





/* =========================================================

   GUARDAR PERMISOS

========================================================= */



function guardarPermisos(

    PDO $pdo,

    int $rolId,

    array $permisos

): void {



    if (empty($permisos)) {

        return;

    }



    $stmt = $pdo->prepare("

        INSERT INTO rol_permiso (

            rol_id,

            permiso_id

        )

        VALUES (

            :rol_id,

            :permiso_id

        )

    ");



    foreach ($permisos as $permisoId) {



        $permisoId = (int) $permisoId;



        if ($permisoId <= 0) {

            continue;

        }



        $stmt->execute([

            ':rol_id'     => $rolId,

            ':permiso_id' => $permisoId

        ]);

    }

=======
}

/* =========================================================
   GUARDAR PERMISOS
========================================================= */

function guardarPermisos(
    PDO $pdo,
    int $rolId,
    array $permisos
): void
{
    if (empty($permisos)) {
        return;
    }

    $stmt = $pdo->prepare("
        INSERT INTO rol_permiso (
            rol_id,
            permiso_id
        )
        VALUES (
            :rol,
            :permiso
        )
    ");

    foreach ($permisos as $permisoId) {

        $permisoId = (int)$permisoId;

        if ($permisoId <= 0) {
            continue;
        }

        $stmt->execute([

            ':rol' => $rolId,

            ':permiso' => $permisoId

        ]);
    }
>>>>>>> 3e2d89c (Actualización del proyecto)
}