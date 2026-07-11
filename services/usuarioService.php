<?php

declare(strict_types=1);

/* ==========================================================
   USUARIO SERVICE
========================================================== */

/*
|--------------------------------------------------------------------------
| Este servicio centraliza toda la lógica relacionada con la
| administración de usuarios.
|
| Responsabilidades
|
| • Consultar usuarios
| • Validar información
| • Crear usuarios
| • Editar usuarios
| • Cambiar contraseña
|
|--------------------------------------------------------------------------
*/

/* ==========================================================
   CONSULTAS
========================================================== */

/* ==========================================================
   OBTENER USUARIO POR ID
========================================================== */

function obtenerUsuarioPorId(
    PDO $pdo,
    int $id
): array|false
{

    $stmt = $pdo->prepare("

        SELECT

            u.id,
            u.nombre,
            u.usuario,
            u.correo,
            u.password,
            u.rol_id,
            u.activo,
            u.fecha_creacion,
            r.nombre AS rol_nombre

        FROM usuarios u

        INNER JOIN roles r
            ON r.id = u.rol_id

        WHERE
            u.id = :id

        LIMIT 1

    ");

    $stmt->execute([

        ':id' => $id

    ]);

    return $stmt->fetch(PDO::FETCH_ASSOC);

}

/* ==========================================================
   OBTENER USUARIO POR USERNAME
========================================================== */

function obtenerUsuarioPorUsername(
    PDO $pdo,
    string $usuario
): array|false
{

    $stmt = $pdo->prepare("

        SELECT

            u.id,
            u.nombre,
            u.usuario,
            u.correo,
            u.password,
            u.rol_id,
            u.activo,
            u.fecha_creacion,
            r.nombre AS rol_nombre

        FROM usuarios u

        INNER JOIN roles r
            ON r.id = u.rol_id

        WHERE

            u.usuario = :usuario

            AND u.activo = 1

        LIMIT 1

    ");

    $stmt->execute([

        ':usuario' => $usuario

    ]);

    return $stmt->fetch(PDO::FETCH_ASSOC);

}

/* ==========================================================
   OBTENER USUARIO POR CORREO
========================================================== */

function obtenerUsuarioPorCorreo(
    PDO $pdo,
    string $correo
): array|false
{

    $stmt = $pdo->prepare("

        SELECT

            u.id,
            u.nombre,
            u.usuario,
            u.correo,
            u.password,
            u.rol_id,
            u.activo,
            u.fecha_creacion,
            r.nombre AS rol_nombre

        FROM usuarios u

        INNER JOIN roles r
            ON r.id = u.rol_id

        WHERE

            u.correo = :correo

            AND u.activo = 1

        LIMIT 1

    ");

    $stmt->execute([

        ':correo' => $correo

    ]);

    return $stmt->fetch(PDO::FETCH_ASSOC);

}

/* ==========================================================
   OBTENER USUARIO POR CREDENCIAL
========================================================== */

function obtenerUsuarioPorCredencial(
    PDO $pdo,
    string $credencial
): array|false
{

    if (

        filter_var(

            $credencial,

            FILTER_VALIDATE_EMAIL

        )

    ) {

        return obtenerUsuarioPorCorreo(

            $pdo,

            $credencial

        );

    }

    return obtenerUsuarioPorUsername(

        $pdo,

        $credencial

    );

}

/* ==========================================================
   EXISTE USUARIO
========================================================== */

function existeUsuario(
    PDO $pdo,
    int $id
): bool
{

    return obtenerUsuarioPorId(

        $pdo,

        $id

    ) !== false;

}

/* ==========================================================
   VALIDACIONES
========================================================== */

/* ==========================================================
   VALIDAR USERNAME DISPONIBLE
========================================================== */

function validarUsernameDisponible(
    PDO $pdo,
    string $usuario
): bool
{

    $stmt = $pdo->prepare("

        SELECT id

        FROM usuarios

        WHERE usuario = :usuario

        LIMIT 1

    ");

    $stmt->execute([

        ':usuario' => $usuario

    ]);

    return !$stmt->fetch();

}

/* ==========================================================
   VALIDAR USERNAME EN EDICIÓN
========================================================== */

function validarUsernameEdicion(
    PDO $pdo,
    int $id,
    string $usuario
): bool
{

    $stmt = $pdo->prepare("

        SELECT id

        FROM usuarios

        WHERE

            usuario = :usuario

            AND id != :id

        LIMIT 1

    ");

    $stmt->execute([

        ':usuario' => $usuario,

        ':id' => $id

    ]);

    return !$stmt->fetch();

}

/* ==========================================================
   VALIDAR CORREO DISPONIBLE
========================================================== */

function validarCorreoDisponible(
    PDO $pdo,
    string $correo
): bool
{

    $stmt = $pdo->prepare("

        SELECT id

        FROM usuarios

        WHERE correo = :correo

        LIMIT 1

    ");

    $stmt->execute([

        ':correo' => $correo

    ]);

    return !$stmt->fetch();

}

/* ==========================================================
   VALIDAR CORREO EN EDICIÓN
========================================================== */

function validarCorreoEdicion(
    PDO $pdo,
    int $id,
    string $correo
): bool
{

    $stmt = $pdo->prepare("

        SELECT id

        FROM usuarios

        WHERE

            correo = :correo

            AND id != :id

        LIMIT 1

    ");

    $stmt->execute([

        ':correo' => $correo,

        ':id' => $id

    ]);

    return !$stmt->fetch();

}

/* ==========================================================
   USUARIOS
========================================================== */

/* ==========================================================
   CREAR USUARIO
========================================================== */

function crearUsuario(
    PDO $pdo,
    array $datos
): void
{

    $nombre = trim(

        $datos['nombre'] ?? ''

    );

    $usuario = trim(

        $datos['usuario'] ?? ''

    );

    $correo = trim(

        $datos['correo'] ?? ''

    );

    $password = trim(

        $datos['password'] ?? ''

    );

    $rolId = (int) (

        $datos['rol_id'] ?? 0

    );

    /* ======================================================
       VALIDACIONES
    ====================================================== */

    if (

        empty($nombre) ||

        empty($usuario) ||

        empty($correo) ||

        empty($password) ||

        $rolId <= 0

    ) {

        throw new Exception(

            'Todos los campos son obligatorios.'

        );

    }

    if (

        !filter_var(

            $correo,

            FILTER_VALIDATE_EMAIL

        )

    ) {

        throw new Exception(

            'El correo electrónico no es válido.'

        );

    }

    if (

        !validarUsernameDisponible(

            $pdo,

            $usuario

        )

    ) {

        throw new Exception(

            'El nombre de usuario ya existe.'

        );

    }

    if (

        !validarCorreoDisponible(

            $pdo,

            $correo

        )

    ) {

        throw new Exception(

            'El correo electrónico ya está registrado.'

        );

    }

    /* ======================================================
       INSERTAR
    ====================================================== */

    $stmt = $pdo->prepare("

        INSERT INTO usuarios (

            nombre,
            usuario,
            correo,
            password,
            rol_id,
            activo

        )

        VALUES (

            :nombre,
            :usuario,
            :correo,
            :password,
            :rol_id,
            1

        )

    ");

    $stmt->execute([

        ':nombre'   => $nombre,

        ':usuario'  => $usuario,

        ':correo'   => $correo,

        ':password' => encriptarPassword(

            $password

        ),

        ':rol_id'   => $rolId

    ]);

}

/* ==========================================================
   EDITAR USUARIO
========================================================== */

function editarUsuario(
    PDO $pdo,
    array $datos
): void
{

    $id = (int) (

        $datos['id'] ?? 0

    );

    $nombre = trim(

        $datos['nombre'] ?? ''

    );

    $usuario = trim(

        $datos['usuario'] ?? ''

    );

    $correo = trim(

        $datos['correo'] ?? ''

    );

    $password = trim(

        $datos['password'] ?? ''

    );

    $rolId = (int) (

        $datos['rol_id'] ?? 0

    );

    /* ======================================================
       VALIDACIONES
    ====================================================== */

    if (

        $id <= 0

    ) {

        throw new Exception(

            'ID de usuario inválido.'

        );

    }

    if (

        !existeUsuario(

            $pdo,

            $id

        )

    ) {

        throw new Exception(

            'El usuario no existe.'

        );

    }

    if (

        empty($nombre) ||

        empty($usuario) ||

        empty($correo) ||

        $rolId <= 0

    ) {

        throw new Exception(

            'Todos los campos son obligatorios.'

        );

    }

    if (

        !filter_var(

            $correo,

            FILTER_VALIDATE_EMAIL

        )

    ) {

        throw new Exception(

            'El correo electrónico no es válido.'

        );

    }

    if (

        !validarUsernameEdicion(

            $pdo,

            $id,

            $usuario

        )

    ) {

        throw new Exception(

            'El nombre de usuario ya existe.'

        );

    }

    if (

        !validarCorreoEdicion(

            $pdo,

            $id,

            $correo

        )

    ) {

        throw new Exception(

            'El correo electrónico ya está registrado.'

        );

    }

    /* ======================================================
       ACTUALIZAR
    ====================================================== */

    if (

        !empty($password)

    ) {

        $stmt = $pdo->prepare("

            UPDATE usuarios

            SET

                nombre   = :nombre,
                usuario  = :usuario,
                correo   = :correo,
                password = :password,
                rol_id   = :rol_id

            WHERE id = :id

        ");

        $stmt->execute([

            ':nombre'   => $nombre,

            ':usuario'  => $usuario,

            ':correo'   => $correo,

            ':password' => encriptarPassword(

                $password

            ),

            ':rol_id'   => $rolId,

            ':id'       => $id

        ]);

        return;

    }

    $stmt = $pdo->prepare("

        UPDATE usuarios

        SET

            nombre  = :nombre,
            usuario = :usuario,
            correo  = :correo,
            rol_id  = :rol_id

        WHERE id = :id

    ");

    $stmt->execute([

        ':nombre'  => $nombre,

        ':usuario' => $usuario,

        ':correo'  => $correo,

        ':rol_id'  => $rolId,

        ':id'      => $id

    ]);

}

/* ==========================================================
   CAMBIAR CONTRASEÑA
========================================================== */

function cambiarPassword(
    PDO $pdo,
    int $id,
    string $password
): void
{

    if (

        $id <= 0

    ) {

        throw new Exception(

            'Usuario inválido.'

        );

    }

    if (

        !existeUsuario(

            $pdo,

            $id

        )

    ) {

        throw new Exception(

            'El usuario no existe.'

        );

    }

    if (

        empty($password)

    ) {

        throw new Exception(

            'La contraseña es obligatoria.'

        );

    }

    $stmt = $pdo->prepare("

        UPDATE usuarios

        SET

            password = :password

        WHERE id = :id

    ");

    $stmt->execute([

        ':password' => encriptarPassword(

            $password

        ),

        ':id' => $id

    ]);

}

/* ==========================================================
   UTILIDADES
========================================================== */

/* ==========================================================
   ENCRIPTAR CONTRASEÑA
========================================================== */

function encriptarPassword(
    string $password
): string
{

    return password_hash(

        $password,

        PASSWORD_DEFAULT

    );

}


/* ==========================================================
   OBTENER TODOS LOS USUARIOS
========================================================== */

function obtenerUsuarios(PDO $pdo): array
{
    $stmt = $pdo->query("
        SELECT
            u.*,
            r.nombre AS rol
        FROM usuarios u
        LEFT JOIN roles r
            ON r.id = u.rol_id
        ORDER BY u.nombre ASC
    ");

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/* ==========================================================
   FIN DEL SERVICIO
========================================================== */