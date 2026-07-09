<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/bootstrap.php';

require_once __DIR__ . '/../../middleware/guest.php';

require_once __DIR__ . '/../../middleware/csrf.php';

exigirInvitado();

generarCSRF();

$config = $GLOBALS['config'];

$error = getFlash('error');

$success = getFlash('success');

$version = $config['version'] ?? '2.0';



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
        content="Recuperación segura de contraseña del Sistema de Seguimiento Ministerial."
    >

    <title>

        Remanente | Recuperar contraseña

    </title>

    <!-- ======================================================
         GOOGLE FONTS
    ======================================================= -->

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

    <!-- ======================================================
         FONT AWESOME
    ======================================================= -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    >

    <!-- ======================================================
         APP CSS
    ======================================================= -->

    <link
        rel="stylesheet"
        href="<?= BASE_URL ?>/assets/css/app.css"
    >

</head>

<body class="auth auth-forgot">
    
<!-- ==========================================================
     LOGIN PAGE
========================================================== -->

<div class="login-page">

    <!-- ======================================================
         BACKGROUND
    ======================================================= -->

    <div class="login-background">

        <img
            src="<?= BASE_URL ?>/assets/img/1396974.png"
            alt="Background"
            class="login-background-image"
        >

        <div class="login-overlay"></div>

        <canvas id="fx"></canvas>

    </div>

 <!-- ======================================================
     CONTENT
====================================================== -->

<main class="login-container">

    <!-- ==================================================
         HERO
    =================================================== -->

    <section class="login-hero">

        <!-- ==============================================
             LOGO
        =============================================== -->

        <div class="login-logo">

            <img
                src="<?= BASE_URL ?>/assets/img/logo.png"
                alt="Ministerio Remanente"
            >

            <div class="login-logo-text">

                <h2>

                    SIG Remanente

                </h2>

                <span>

                    Sistema de Seguimiento

                </span>

            </div>

        </div>

        <!-- ==============================================
             TÍTULO
        =============================================== -->

        <h1 class="login-title">

            Recupera el acceso
            a tu <strong>cuenta</strong>
            de forma segura.

        </h1>

        <!-- ==============================================
             DESCRIPCIÓN
        =============================================== -->

        <p class="login-description">

            Si olvidaste tu contraseña, ingresa el usuario
            o correo electrónico asociado a tu cuenta.
            Te enviaremos las instrucciones necesarias para
            restablecer el acceso de forma segura.

        </p>

    </section>

    <!-- ==================================================
         LOGIN PANEL
    =================================================== -->

    <section class="login-panel">

        <div class="login-card">

            <!-- ==========================================
                 HEADER
            =========================================== -->

            <div class="login-card-header">

                <div class="login-card-heading">

                    <h3 class="login-card-title">

                        Recuperar contraseña

                    </h3>

                    <p class="login-card-description">

                        Ingresa tu usuario o correo para
                        enviarte el enlace de recuperación.

                    </p>

                </div>

                <div class="login-card-divider"></div>

            </div>

            <!-- ==========================================
                 BODY
            =========================================== -->

            <div class="login-card-body">

                <!-- ==========================================
                     MENSAJES
                =========================================== -->

                <?php if (!empty($error)): ?>

                    <div class="login-alert login-alert-error">

                        <i class="fa-solid fa-circle-exclamation"></i>

                        <span>

                            <?= htmlspecialchars($error) ?>

                        </span>

                    </div>

                <?php endif; ?>

                <?php if (!empty($success)): ?>

                    <div class="login-alert login-alert-success">

                        <i class="fa-solid fa-circle-check"></i>

                        <span>

                            <?= htmlspecialchars($success) ?>

                        </span>

                    </div>

                <?php endif; ?>

                <!-- ==========================================
                     FORMULARIO
                =========================================== -->

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

                    <!-- ======================================
                         USUARIO O CORREO
                    ======================================= -->

                    <div class="login-group">

                        <label
                            class="login-label"
                            for="usuario"
                        >

                            <i class="fa-solid fa-envelope"></i>

                            Usuario o correo electrónico

                        </label>

                        <div class="login-input-wrapper">

                            <i class="fa-solid fa-envelope login-input-icon"></i>

                            <input

                                id="usuario"

                                name="usuario"

                                type="text"

                                class="login-input"

                                placeholder="Ingresa tu usuario o correo"

                                autocomplete="username"

                                required

                            >

                        </div>

                    </div>

    <!-- ======================================
                         INFORMACIÓN
                    ======================================= -->

                    <div class="login-info">

                        <i class="fa-solid fa-circle-info"></i>

                        <span>

                            Si la cuenta existe, recibirás un enlace
                            para restablecer tu contraseña de forma
                            segura.

                        </span>

                    </div>

                    <!-- ======================================
                         BOTÓN
                    ======================================= -->

                    <button
                        type="submit"
                        id="btnForgotPassword"
                        class="login-button"
                    >

                        <span>

                            Enviar enlace de recuperación

                        </span>

                        <i class="fa-solid fa-paper-plane"></i>

                    </button>

                </form>

                <!-- ==========================================
                     ACCIONES
                =========================================== -->

                <div class="login-links">

                    <a
                        href="<?= BASE_URL ?>/views/auth/login.php"
                    >

                        <i class="fa-solid fa-arrow-left"></i>

                        Volver al inicio de sesión

                    </a>

                </div>

            </div>

            <!-- ==========================================
                 FOOTER CARD
            =========================================== -->

            <footer class="login-card-footer">

                <small class="login-copyright">

                    Sistema de Seguimiento Ministerial

                </small>

                <span class="login-version">

                    Versión <?= htmlspecialchars($version) ?>

                </span>

            </footer>

        </div>

    </section>

</main>

<!-- ======================================================
     FOOTER
====================================================== -->

<footer class="login-footer">

    <p>

        © <?= date('Y') ?>

        Ministerio Remanente · Todos los derechos reservados.

    </p>

</footer>


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
    src="<?= BASE_URL ?>/assets/js/modulos/auth/background.js">
</script>

<script
    src="<?= BASE_URL ?>/assets/js/modulos/auth/particles.js">
</script>

<script
    src="<?= BASE_URL ?>/assets/js/modulos/auth/login.js">
</script>

<script
    src="<?= BASE_URL ?>/assets/js/modulos/auth/validation.js">
</script>

<script
    src="<?= BASE_URL ?>/assets/js/modulos/auth/ui.js">
</script>

</body>

</html>