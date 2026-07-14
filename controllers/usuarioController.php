<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../config/conexion.php';

require_once __DIR__ . '/../helpers/redirect.php';
require_once __DIR__ . '/../helpers/validaciones.php';

require_once __DIR__ . '/../middleware/csrf.php';

require_once __DIR__ . '/../services/UsuarioService.php';
require_once __DIR__ . '/../services/MailService.php';

/* ==========================================================
   REQUEST
========================================================== */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    redirect(

        '../views/usuarios/index.php',

        'error',

        'Acceso inválido.'

    );

}

/* ==========================================================
   SEGURIDAD
========================================================== */

validarCSRF();

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
   RUTA DE RETORNO
========================================================== */

$redirect = '../views/usuarios/index.php';

/* ==========================================================
   CONTROLADOR
========================================================== */

try {

    switch ($action) {


        /* ==================================================
           CREAR USUARIO
        =================================================== */

        case 'crear_usuario':

            $usuario = crearUsuario(

                $pdo,

                $_POST

            );

            /*
            |--------------------------------------------------------------------------
            | Enviar credenciales por correo
            |--------------------------------------------------------------------------
            |
            | Se habilitará cuando terminemos MailService.
            |
            */

            // enviarCredencialesUsuario(
            //     $usuario
            // );

            redirect(

                $redirect,

                'success',

                'Usuario creado correctamente.'

            );

            break;

        /* ==================================================
           EDITAR USUARIO
        =================================================== */

        case 'editar_usuario':

            editarUsuario(

                $pdo,

                $_POST

            );

            redirect(

                $redirect,

                'success',

                'Usuario actualizado correctamente.'

            );

            break;

        /* ==================================================
           CAMBIAR CONTRASEÑA
        =================================================== */

        case 'cambiar_password':

            cambiarPassword(

                $pdo,

                (int) (

                    $_POST['id'] ?? 0

                ),

                (string) (

                    $_POST['password'] ?? ''

                )

            );

            redirect(

                $redirect,

                'success',

                'Contraseña actualizada correctamente.'

            );

            break;

        /* ==================================================
           ACTIVAR USUARIO
        =================================================== */

        case 'activar_usuario':

            activarUsuario(

                $pdo,

                (int) $_SESSION['usuario']['id'],

                (int) (

                    $_POST['id'] ?? 0

                )

            );

            redirect(

                $redirect,

                'success',

                'Usuario activado correctamente.'

            );

            break;

        /* ==================================================
           DESACTIVAR USUARIO
        =================================================== */

        case 'desactivar_usuario':

            desactivarUsuario(

                $pdo,

                (int) $_SESSION['usuario']['id'],

                (int) (

                    $_POST['id'] ?? 0

                )

            );

            redirect(

                $redirect,

                'success',

                'Usuario desactivado correctamente.'

            );

            break;

        /* ==================================================
           DEFAULT
        =================================================== */

        default:

            throw new Exception(

                'Acción no válida.'

            );

    }

    } catch (PDOException $e) {

    /*
    |--------------------------------------------------------------------------
    | Registrar el error para depuración.
    |--------------------------------------------------------------------------
    */

    error_log(

        $e->getMessage()

    );

    redirect(

        $redirect,

        'error',

        'Ocurrió un error interno del sistema.'

    );

} catch (Exception $e) {

    redirect(

        $redirect,

        'error',

        $e->getMessage()

    );

}

/* ==========================================================
   FIN DEL CONTROLADOR
========================================================== */
