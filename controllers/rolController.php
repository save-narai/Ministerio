<?php



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

    );

}



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

        $pdo->rollBack();

    }



    redirect(

        '../views/roles/index.php',

        'error',

        $e->getMessage()

    );

}





/* =========================================================

   CREAR ROL

========================================================= */



function crearRol(PDO $pdo): void

{

    $nombre = trim($_POST['nombre'] ?? '');



    if (mb_strlen($nombre) > 80) {



        throw new Exception(

            'El nombre del rol es demasiado largo.'

        );

    }



    $permisos = $_POST['permisos'] ?? [];



    if (!is_array($permisos)) {



        $permisos = [];

    }



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

        );

    }



    $pdo->beginTransaction();



    $stmt = $pdo->prepare("

        INSERT INTO roles(nombre)

        VALUES(:nombre)

    ");



    $stmt->execute([

        ':nombre' => $nombre

    ]);



    $rolId = (int) $pdo->lastInsertId();



    guardarPermisos(

        $pdo,

        $rolId,

        $permisos

    );



    $pdo->commit();



    redirect(

        '../views/roles/index.php',

        'success',

        'Rol creado correctamente.'

    );

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

        );

    }



    $permisos = $_POST['permisos'] ?? [];



    if (!is_array($permisos)) {



        $permisos = [];

    }



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

            );

        }



        $stmt = $pdo->prepare("

            UPDATE roles

            SET nombre = :nombre

            WHERE id = :id

        ");



        $stmt->execute([

            ':nombre' => $nombre,

            ':id'     => $id

        ]);

    }



    /* ELIMINAR PERMISOS ACTUALES */



    $stmt = $pdo->prepare("

        DELETE FROM rol_permiso

        WHERE rol_id = :rol_id

    ");



    $stmt->execute([

        ':rol_id' => $id

    ]);



    /* INSERTAR NUEVOS PERMISOS */



    guardarPermisos(

        $pdo,

        $id,

        $permisos

    );



    $pdo->commit();



    redirect(

        '../views/roles/index.php',

        'success',

        'Rol actualizado correctamente.'

    );

}                                                                                                                                                                            /* =========================================================

   ELIMINAR ROL

========================================================= */



function eliminarRol(PDO $pdo): void

{

    $id = (int) ($_POST['id'] ?? 0);



    if ($id <= 0) {



        throw new Exception(

            'Rol inválido.'

        );

    }



    $stmt = $pdo->prepare("

        SELECT

            nombre

        FROM roles

        WHERE id = :id

        LIMIT 1

    ");



    $stmt->execute([

        ':id' => $id

    ]);



    $rol = $stmt->fetch(PDO::FETCH_ASSOC);



    if (!$rol) {



        throw new Exception(

            'Rol no encontrado.'

        );

    }



    if ($rol['nombre'] === ROL_ADMIN) {



        throw new Exception(

            'No se puede eliminar el rol ADMIN.'

        );

    }



    /* VALIDAR USUARIOS ASIGNADOS */



    $stmt = $pdo->prepare("

        SELECT COUNT(*)

        FROM usuarios

        WHERE rol_id = :id

    ");



    $stmt->execute([

        ':id' => $id

    ]);



    if ((int) $stmt->fetchColumn() > 0) {



        throw new Exception(

            'No puedes eliminar un rol que tiene usuarios asignados.'

        );

    }



    $pdo->beginTransaction();



    $stmt = $pdo->prepare("

        DELETE FROM rol_permiso

        WHERE rol_id = :id

    ");



    $stmt->execute([

        ':id' => $id

    ]);



    $stmt = $pdo->prepare("

        DELETE FROM roles

        WHERE id = :id

    ");



    $stmt->execute([

        ':id' => $id

    ]);



    $pdo->commit();



    redirect(

        '../views/roles/index.php',

        'success',

        'Rol eliminado correctamente.'

    );

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

}