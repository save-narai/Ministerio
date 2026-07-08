<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/bootstrap.php';

require_once __DIR__ . '/../../middleware/guest.php';

require_once __DIR__ . '/../../middleware/csrf.php';

exigirInvitado();

generarCSRF();

$config = $GLOBALS['config'];

$error = getFlash('error');

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
        content="Sistema de Seguimiento Ministerial Remanente"
    >

    <title>

        Remanente | Sistema de Seguimiento

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

    <!-- Login CSS -->

    <link
        rel="stylesheet"
        href="<?= BASE_URL ?>/assets/css/app.css"
    >

</head>
<body>

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

        Acompañando el crecimiento
        <strong>espiritual</strong>
        de cada joven.

    </h1>

    <!-- ==============================================
         DESCRIPCIÓN
    =============================================== -->

    <p class="login-description">

        Plataforma diseñada para fortalecer el discipulado,
        registrar el proceso espiritual de cada joven
        y facilitar el acompañamiento del liderazgo.

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

                    Bienvenido

                </h3>

                <p class="login-card-description">

                    Inicia sesión para acceder al sistema.

                </p>

            </div>

            <div class="login-card-divider"></div>

        </div>

        <!-- ==========================================
             ERROR
        =========================================== -->

        <?php if (!empty($error)): ?>

            <div class="login-alert">

                <i class="fa-solid fa-circle-exclamation"></i>

                <p>

                    <?= htmlspecialchars($error) ?>

                </p>

            </div>

        <?php endif; ?>

        <!-- ==========================================
             FORMULARIO
        =========================================== -->

<div class="login-card-body">


        <form
            id="loginForm"
            class="login-form"
            action="<?= BASE_URL ?>/controllers/authController.php"
            method="POST"
            autocomplete="on"
        >

            <?= csrfField(); ?>

            <!-- ======================================
                 USUARIO
            ======================================= -->

            <div class="login-group">

                <label
                    class="login-label"
                    for="usuario"
                >

                    <i class="fa-solid fa-user"></i>

                    Usuario

                </label>

                <div class="login-input-wrapper">

                    <i class="fa-solid fa-user login-input-icon"></i>

                    <input

                        id="usuario"

                        name="usuario"
 
                        type="text"

                        class="login-input"

                        placeholder="Ingresa tu usuario"

                        autocomplete="username"

                        autofocus

                        required

                    >

                </div>

            </div>

            <!-- ======================================
                 CONTRASEÑA
            ======================================= -->

            <div class="login-group">

                <label
                    class="login-label"
                    for="password"
                >

                    <i class="fa-solid fa-lock"></i>

                    Contraseña

                </label>

                <div class="login-input-wrapper">

                    <i class="fa-solid fa-lock login-input-icon"></i>

                    <input

                        id="password"

                        name="password"

                        type="password"

                        class="login-input"

                        placeholder="Ingresa tu contraseña"

                        autocomplete="current-password"

                        required

                    >

                    <button

                        type="button"

                        id="togglePassword"

                        class="password-toggle"

                        aria-label="Mostrar contraseña"

                    >

                        <i

                            id="togglePasswordIcon"

                            class="fa-solid fa-eye"

                        ></i>

                    </button>

                </div>

            </div>

            <!-- ======================================
                 OPCIONES
            ======================================= -->

            <div class="login-options">

                <label class="login-remember">

                    <input
                        type="checkbox"
                        id="rememberMe"
                        name="remember"
                    >

                    <span class="login-check"></span>

                    <span>

                        Recordarme

                    </span>

                </label>

                <a
                    href="<?= BASE_URL ?>/views/auth/forgot-password.php"
                    class="login-forgot"
                >

                    ¿Olvidaste tu contraseña?

                </a>

            </div>

            <!-- ======================================
                 BOTÓN
            ======================================= -->

            <button
                type="submit"
                id="btnLogin"
                class="login-button"
            >

                <span>

    Ingresar al sistema

</span>

<i

    class="fa-solid fa-arrow-right login-button-icon"

></i>
            </button>

        </form>
    
    </div>



        <!-- ==========================================
             FOOTER
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

<!-- ==========================================================
   SCRIPTS
========================================================== -->
<script src="<?= BASE_URL ?>/assets/js/modulos/auth/background.js"></script>

<script src="<?= BASE_URL ?>/assets/js/modulos/auth/particles.js"></script>

<script src="<?= BASE_URL ?>/assets/js/modulos/auth/login.js"></script>

<script src="<?= BASE_URL ?>/assets/js/modulos/auth/validation.js"></script>

<script src="<?= BASE_URL ?>/assets/js/modulos/auth/ui.js"></script>

<script src="<?= BASE_URL ?>/assets/js/modulos/auth/remember.js"></script>

</body>

</html>