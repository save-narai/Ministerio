<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../config/conexion.php';

require_once __DIR__ . '/../helpers/redirect.php';
require_once __DIR__ . '/../helpers/validaciones.php';

require_once __DIR__ . '/../middleware/csrf.php';

require_once __DIR__ . '/../services/usuarioService.php';

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
   SECURITY
========================================================== */

validarCSRF();

/* ==========================================================
   ACTION
========================================================== */

$action = strtolower(

    trim(

        (string) ($_POST['action'] ?? '')

    )

);

/* ==========================================================
   CONTROLLER
========================================================== */

try {

    switch ($action) {

        /* ==================================================
           CREAR USUARIO
        =================================================== */

        case 'crear_usuario':

            crearUsuario(

                $pdo,

                $_POST

            );

            redirect(

                '../views/usuarios/index.php',

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

                '../views/usuarios/index.php',

                'success',

                'Usuario actualizado correctamente.'

            );

            break;

        /* ==================================================
           CAMBIAR CONTRASEÑA
        =================================================== */

        case 'cambiar_password':

            $password = trim(

                $_POST['password'] ?? ''

            );

            $confirmarPassword = trim(

                $_POST['confirmar_password'] ?? ''

            );

            if (

                $password !== $confirmarPassword

            ) {

                throw new Exception(

                    'Las contraseñas no coinciden.'

                );

            }

            cambiarPassword(

                $pdo,

                (int) ($_POST['id'] ?? 0),

                $password

            );

            redirect(

                '../views/usuarios/index.php',

                'success',

                'Contraseña actualizada correctamente.'

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

    error_log(

        $e->getMessage()

    );

    redirect(

        '../views/usuarios/index.php',

        'error',

        'Error interno del sistema.'

    );

} catch (Exception $e) {

    redirect(

        '../views/usuarios/index.php',

        'error',

        $e->getMessage()

    );

}