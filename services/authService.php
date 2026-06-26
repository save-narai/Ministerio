<?php

/* =========================================================
   OBTENER USUARIO POR USERNAME
========================================================= */

function obtenerUsuarioPorUsername(
    PDO $pdo,
    string $usuario
): array|false {

    $stmt = $pdo->prepare("
        SELECT

            u.id,
            u.nombre,
            u.usuario,
            u.password,
            u.rol_id,
            r.nombre AS rol_nombre

        FROM usuarios u

        INNER JOIN roles r
            ON u.rol_id = r.id

        WHERE

            u.usuario = :usuario

            AND u.activo = 1

        LIMIT 1
    ");

    $stmt->execute([
        ':usuario' => $usuario
    ]);

    return $stmt->fetch(
        PDO::FETCH_ASSOC
    );
}


/* =========================================================
   AUTENTICAR USUARIO
========================================================= */

function autenticarUsuario(
    PDO $pdo,
    string $usuario,
    string $password
): array {

    $usuarioSistema =
        obtenerUsuarioPorUsername(
            $pdo,
            $usuario
        );

    if (
        !$usuarioSistema
        || !password_verify(
            $password,
            $usuarioSistema['password']
        )
    ) {

        throw new Exception(
            'Usuario o contraseña incorrectos.'
        );
    }

    return $usuarioSistema;
}