<?php

declare(strict_types=1);

require_once __DIR__ . '/controller.php';

require_once __DIR__ . '/../services/UsuarioService.php';
require_once __DIR__ . '/../services/MailService.php';

controllerInit();

$pdo = controllerPdo();

controllerRun(

    [

        /* ==================================================
           CREAR USUARIO
        =================================================== */

        'crear_usuario' => function () use ($pdo) {

            $usuario = crearUsuario(
                $pdo,
                $_POST
            );

            /*
            |--------------------------------------------------
            | Enviar credenciales por correo
            |--------------------------------------------------
            |
            | Se habilitará cuando terminemos MailService.
            |
            */

            // enviarCredencialesUsuario($usuario);

            return controllerSuccess(
                'Usuario creado correctamente.'
            );

        },

        /* ==================================================
           EDITAR USUARIO
        =================================================== */

        'editar_usuario' => function () use ($pdo) {

            editarUsuario(
                $pdo,
                $_POST
            );

            return controllerSuccess(
                'Usuario actualizado correctamente.'
            );

        },

        /* ==================================================
           CAMBIAR CONTRASEÑA
        =================================================== */

        'cambiar_password' => function () use ($pdo) {

         $password = $_POST['password'] ?? '';
$confirmar = $_POST['confirmar_password'] ?? '';

if ($password !== $confirmar) {
    throw new Exception('Las contraseñas no coinciden.');
}

cambiarPassword(
    $pdo,
    (int) ($_POST['id'] ?? 0),
    $password
);

            return controllerSuccess(
                'Contraseña actualizada correctamente.'
            );

        },

        /* ==================================================
           ACTIVAR USUARIO
        =================================================== */

        'activar_usuario' => function () use ($pdo) {

          activarUsuario(

    $pdo,

    usuarioId(),

    (int) ($_POST['id'] ?? 0)

);

            return controllerSuccess(
                'Usuario activado correctamente.'
            );

        },

        /* ==================================================
           DESACTIVAR USUARIO
        =================================================== */

        'desactivar_usuario' => function () use ($pdo) {

         desactivarUsuario(

    $pdo,

    usuarioId(),

    (int) ($_POST['id'] ?? 0)

);

            return controllerSuccess(
                'Usuario desactivado correctamente.'
            );

        },

        /* ==================================================
           ELIMINAR USUARIO
        =================================================== */

        'eliminar_usuario' => function () use ($pdo) {

         eliminarUsuario(

    $pdo,

    usuarioId(),

    (int) ($_POST['id'] ?? 0)

);

            return controllerSuccess(
                'Usuario eliminado correctamente.'
            );

        }

    ],

    [

        'redirect' => '../views/usuarios/index.php'

    ]

);