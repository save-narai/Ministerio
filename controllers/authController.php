<?php

declare(strict_types=1);

require_once __DIR__ . '/controller.php';
require_once __DIR__ . '/../services/AuthService.php';

controllerInit();

$pdo = controllerPdo();

controllerRun(

    [

        /* ==================================================
           LOGIN
        ================================================== */

        'login' => function () use ($pdo) {

            $usuario = limpiarTexto(
                $_POST['usuario'] ?? ''
            );

            $password = (string) (
                $_POST['password'] ?? ''
            );

            if (

                $usuario === ''

                ||

                $password === ''

            ) {

                throw new Exception(
                    'Debe ingresar usuario y contraseña.'
                );

            }

            $usuarioSistema = loginUsuario(

                $pdo,

                $usuario,

                $password

            );

            return controllerRedirect(

                '../views/dashboard.php',

                "Bienvenido {$usuarioSistema['nombre']}"

            );

        },

        /* ==================================================
           RECUPERAR CONTRASEÑA
        ================================================== */

        'forgot_password' => function () use ($pdo) {

            $credencial = limpiarTexto(
                $_POST['credencial'] ?? ''
            );

            if ($credencial === '') {

                throw new Exception(
                    'Debe ingresar un usuario o correo electrónico.'
                );

            }

            $usuarioSistema = obtenerUsuarioPorCredencial(

                $pdo,

                $credencial

            );

            if (!$usuarioSistema) {

                throw new Exception(
                    'No existe una cuenta asociada a la información proporcionada.'
                );

            }

            limpiarTokensExpirados($pdo);

            $token = generarTokenRecuperacion();

            guardarTokenRecuperacion(

                $pdo,

                (int) $usuarioSistema['id'],

                $token

            );

            enviarCorreoRecuperacion(

                $usuarioSistema,

                $token

            );

            return controllerSuccess(

                'Si la cuenta existe, recibirás un enlace para restablecer la contraseña.'

            );

        },                                                                                                                          /* ==================================================
           RESTABLECER CONTRASEÑA
        ================================================== */

        'reset_password' => function () use ($pdo) {

            $token = trim(

                (string) (

                    $_POST['token'] ?? ''

                )

            );

            $password = (string) (

                $_POST['password'] ?? ''

            );

            $confirmPassword = (string) (

                $_POST['confirm_password'] ?? ''

            );

            if (

                $token === ''

                ||

                $password === ''

                ||

                $confirmPassword === ''

            ) {

                throw new Exception(
                    'Debe completar todos los campos.'
                );

            }

            if ($password !== $confirmPassword) {

                throw new Exception(
                    'Las contraseñas no coinciden.'
                );

            }

            $usuarioSistema = validarTokenRecuperacion(

                $pdo,

                $token

            );

            if (!$usuarioSistema) {

                throw new Exception(
                    'El enlace de recuperación no es válido o ha expirado.'
                );

            }

            cambiarPassword(

                $pdo,

                (int) $usuarioSistema['id'],

                $password

            );

            eliminarTokenRecuperacion(

                $pdo,

                $token

            );

            return controllerRedirect(

                '../views/auth/login.php',

                'La contraseña fue actualizada correctamente.'

            );

        },

        /* ==================================================
           CERRAR SESIÓN
        ================================================== */

        'logout' => function () {

            logoutUsuario();

            return controllerRedirect(

                '../views/auth/login.php',

                'La sesión se cerró correctamente.'

            );

        }

    ],

    [

        /*
        |--------------------------------------------------------------------------
        | CONFIGURACIÓN DEL CONTROLLER
        |--------------------------------------------------------------------------
        |
        | redirect : Ruta por defecto en caso de error.
        | method   : Método HTTP permitido.
        | csrf     : Validación automática del token CSRF.
        |
        */

        'redirect' => '../index.php',

        'method' => 'POST',

        'csrf' => true

    ]

);