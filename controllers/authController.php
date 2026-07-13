<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../helpers/redirect.php';
require_once __DIR__ . '/../helpers/validaciones.php';
require_once __DIR__ . '/../services/MailService.php';
require_once __DIR__ . '/../middleware/csrf.php';
require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../services/UsuarioService.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../index.php', 'error', 'Acceso inválido.');
}

validarCSRF();

$action = strtolower(trim((string) ($_POST['action'] ?? 'login')));

/*
|--------------------------------------------------------------------------
| Ruta de retorno en caso de error
|--------------------------------------------------------------------------
*/

$redirectError = match ($action) {

    'forgot_password' => '../views/auth/forgot-password.php',

    'reset_password' => '../views/auth/reset-password.php?token=' .
        urlencode($_POST['token'] ?? ''),

    default => '../index.php',

};

try {

    switch ($action) {

        /* ==================================================
           LOGIN
        ================================================== */

        case 'login':

            $usuario = limpiarTexto($_POST['usuario'] ?? '');
            $password = (string) ($_POST['password'] ?? '');

            if (empty($usuario) || empty($password)) {
                throw new Exception('Debe ingresar usuario y contraseña.');
            }

            $usuarioSistema = loginUsuario(
                $pdo,
                $usuario,
                $password
            );

            redirect(
                '../views/dashboard.php',
                'success',
                "Bienvenido {$usuarioSistema['nombre']}"
            );

            break;

        /* ==================================================
           FORGOT PASSWORD
        ================================================== */

        case 'forgot_password':

$credencial = limpiarTexto($_POST['credencial'] ?? '');

            if (empty($credencial)) {
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

            redirect(
                '../views/auth/forgot-password.php',
                'success',
                'Si la cuenta existe, recibirás un enlace para restablecer la contraseña.'
            );

            break;

        /* ==================================================
           RESET PASSWORD
        ================================================== */

        case 'reset_password':

            $token = trim((string) ($_POST['token'] ?? ''));
            $password = (string) ($_POST['password'] ?? '');
            $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

            if (
                empty($token) ||
                empty($password) ||
                empty($confirmPassword)
            ) {
                throw new Exception('Debe completar todos los campos.');
            }

            if ($password !== $confirmPassword) {
                throw new Exception('Las contraseñas no coinciden.');
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

            redirect(
                '../views/auth/login.php',
                'success',
                'La contraseña fue actualizada correctamente.'
            );

            break;

        /* ==================================================
           LOGOUT
        ================================================== */

        case 'logout':

            logoutUsuario();

            redirect(
                '../views/auth/login.php',
                'success',
                'La sesión se cerró correctamente.'
            );

            break;

        default:
            throw new Exception('Acción no válida.');
    }

} catch (PDOException $e) {

    error_log($e->getMessage());

    redirect(
        $redirectError,
        'error',
        'Error interno del sistema.'
    );

} catch (Exception $e) {

    redirect(
        $redirectError,
        'error',
        $e->getMessage()
    );
}