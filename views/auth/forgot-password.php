<?php

declare(strict_types=1);

require_once __DIR__ . '/../../middleware/guest.php';
require_once __DIR__ . '/../../middleware/csrf.php';
require_once __DIR__ . '/../../helpers/flash.php';

exigirInvitado();

generarCSRF();

$error = flash('error');
$success = flash('success');

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="description"
        content="Recuperación de contraseña"
    >

    <title>

        Recuperar contraseña | Remanente

    </title>

    <!-- Google Fonts -->

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <!-- Font Awesome -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    >

    <!-- SCSS compilado -->

    <link
        rel="stylesheet"
        href="<?= BASE_URL ?>/assets/css/main.css"
    >

</head>

<body>

<div class="login-page">

    <div class="login-background">

        <img
            src="<?= BASE_URL ?>/assets/img/1396974.png"
            alt="Fondo"
            class="login-background-image"
        >

        <div class="login-overlay"></div>

        <canvas id="fx"></canvas>

    </div>

    <main class="login-container">

        <section class="login-hero">

        <!-- =========================================
             PANEL IZQUIERDO
        ========================================== -->

        <div class="login-hero__content">

            <div class="login-brand">

                <div class="login-brand__logo">

                    <i class="fa-solid fa-fire-flame-curved"></i>

                </div>

                <span class="login-brand__name">

                    Ministerio Remanente

                </span>

            </div>

            <div class="login-hero__text">

                <span class="login-hero__badge">

                    Recuperación de Cuenta

                </span>

                <h1 class="login-hero__title">

                    Recupera el acceso
                    a tu cuenta de forma segura.

                </h1>

                <p class="login-hero__description">

                    Si olvidaste tu contraseña,
                    ingresa el correo electrónico
                    o el usuario asociado a tu cuenta.
                    Te enviaremos las instrucciones
                    necesarias para crear una nueva contraseña.

                </p>

            </div>

            <!-- PASOS -->

            <div class="login-features">

                <div class="login-feature">

                    <i class="fa-solid fa-envelope-open-text"></i>

                    <div>

                        <strong>

                            Solicita el enlace

                        </strong>

                        <span>

                            Ingresa tu correo o usuario.

                        </span>

                    </div>

                </div>

                <div class="login-feature">

                    <i class="fa-solid fa-paper-plane"></i>

                    <div>

                        <strong>

                            Recibe las instrucciones

                        </strong>

                        <span>

                            Verifica tu bandeja de entrada.

                        </span>

                    </div>

                </div>

                <div class="login-feature">

                    <i class="fa-solid fa-key"></i>

                    <div>

                        <strong>

                            Crea una nueva contraseña

                        </strong>

                        <span>

                            Accede nuevamente al sistema.

                        </span>

                    </div>

                </div>

            </div>

            <!-- MENSAJE -->

            <blockquote class="login-verse">

                <i class="fa-solid fa-shield-heart"></i>

                <p>

                    Tu información está protegida.
                    Solo tú podrás restablecer
                    el acceso a tu cuenta.

                </p>

            </blockquote>

        </div>

        <!-- =========================================
             PANEL DERECHO
        ========================================== -->

        <div class="login-panel">

            <div class="login-card">

                <div class="login-card__header">

                    <div class="login-card__icon">

                        <i class="fa-solid fa-unlock-keyhole"></i>

                    </div>

                    <h2>

                        Recuperar contraseña

                    </h2>

                    <p>

                        Ingresa la información de tu cuenta para
                        enviarte las instrucciones de recuperación.

                    </p>

                </div>

                <!-- =========================================
                     MENSAJES
                ========================================== -->

                <?php if (!empty($error)): ?>

                    <div class="login-alert login-alert--error">

                        <i class="fa-solid fa-circle-exclamation"></i>

                        <span>

                            <?= htmlspecialchars($error) ?>

                        </span>

                    </div>

                <?php endif; ?>

                <?php if (!empty($success)): ?>

                    <div class="login-alert login-alert--success">

                        <i class="fa-solid fa-circle-check"></i>

                        <span>

                            <?= htmlspecialchars($success) ?>

                        </span>

                    </div>

                <?php endif; ?>

                <!-- =========================================
                     FORMULARIO
                ========================================== -->

                <form
                    id="forgotPasswordForm"
                    class="login-form"
                    action="<?= BASE_URL ?>/controllers/authController.php"
                    method="POST"
                    autocomplete="off"
                >

                    <?= csrfField(); ?>

                    <input
                        type="hidden"
                        name="action"
                        value="forgot_password"
                    >

                    <!-- CORREO O USUARIO -->

                    <div class="login-field">

                        <label
                            for="usuario"
                            class="login-label"
                        >

                            Usuario o correo electrónico

                        </label>

                        <div class="login-input-group">

                            <i class="fa-solid fa-envelope"></i>

                            <input
                                id="usuario"
                                type="text"
                                name="usuario"
                                class="login-input"
                                placeholder="Ingresa tu usuario o correo"
                                autocomplete="username"
                                required
                            >

                        </div>

                    </div>

                    <!-- INFORMACIÓN -->

                    <div class="login-info">

                        <i class="fa-solid fa-circle-info"></i>

                        <span>

                            Si la cuenta existe, recibirás un enlace para
                            restablecer tu contraseña.

                        </span>

                    </div>

                    <!-- BOTÓN -->

                    <button
                        type="submit"
                        id="btnForgotPassword"
                        class="login-button"
                    >

                        <span class="login-button-text">

                            Enviar enlace de recuperación

                        </span>

                        <span class="login-button-loader">

                            <i class="fa-solid fa-spinner fa-spin"></i>

                        </span>

                        <i class="fa-solid fa-paper-plane"></i>

                    </button>

                </form>

                <!-- =========================================
                     ACCIONES
                ========================================== -->

                <div class="login-card__actions">

                    <a
                        href="<?= BASE_URL ?>/views/auth/login.php"
                        class="login-link"

                    >

                        <i class="fa-solid fa-arrow-left"></i>

                        Volver al inicio de sesión

                    </a>

                </div>

                <!-- =========================================
                     FOOTER
                ========================================== -->

                <div class="login-card__footer">

                    <span>

                        ¿Recordaste tu contraseña?

                    </span>

                    <a
                        href="<?= BASE_URL ?>/views/auth/login.php"
                        class="login-link"

                    >

                        Iniciar sesión

                    </a>

                </div>
                </div>

        </div>

    </section>

</main>

<!-- =========================================
     FOOTER GENERAL
========================================== -->

<footer class="login-footer">

    <div class="login-footer__left">

        <span>

            © <?= date('Y') ?>

            Ministerio Remanente

        </span>

    </div>

    <div class="login-footer__center">

        <span>

            Sistema de Seguimiento Ministerial

        </span>

    </div>

    <div class="login-footer__right">

        <span>

            Versión 2.0

        </span>

    </div>

</footer>

</div>

<!-- =========================================
     VARIABLES GLOBALES
========================================== -->

<script>

window.BASE_URL = "<?= BASE_URL ?>";

</script>

<!-- =========================================
     AUTH MODULE
========================================== -->

<script
    src="<?= BASE_URL ?>/assets/js/modules/auth/background.js">
</script>

<script
    src="<?= BASE_URL ?>/assets/js/modules/auth/particles.js">
</script>

<script
    src="<?= BASE_URL ?>/assets/js/modules/auth/login.js">
</script>

<script
    src="<?= BASE_URL ?>/assets/js/modules/auth/validation.js">
</script>

<script
    src="<?= BASE_URL ?>/assets/js/modules/auth/ui.js">
</script>

</body>

</html>