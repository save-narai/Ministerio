<?php

declare(strict_types=1);

http_response_code(403);

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../helpers/csrf.php';

$config = $GLOBALS['config'];

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
        content="Acceso denegado"
    >

    <title>

        Acceso denegado | Remanente

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

    <!-- APP CSS -->

    <link
        rel="stylesheet"
        href="<?= BASE_URL ?>/assets/css/app.css"
    >

</head>

<body class="auth unauthorized">

<!-- ==========================================================
     UNAUTHORIZED PAGE
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
    ======================================================= -->

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

                Acceso
                <strong>restringido</strong>

            </h1>

            <!-- ==============================================
                 DESCRIPCIÓN
            =============================================== -->

            <p class="login-description">

                Tu sesión es válida, pero el rol asignado a tu
                usuario no cuenta con autorización para acceder
                a esta sección del sistema.

            </p>

            <!-- ==============================================
                 INFORMACIÓN
            =============================================== -->

            <div class="login-features">

                <div class="login-feature">

                    <i class="fa-solid fa-user-lock"></i>

                    <div>

                        <h4>

                            Acceso controlado

                        </h4>

                        <p>

                            Cada módulo está protegido mediante
                            permisos específicos.

                        </p>

                    </div>

                </div>

                <div class="login-feature">

                    <i class="fa-solid fa-shield-halved"></i>

                    <div>

                        <h4>

                            Seguridad

                        </h4>

                        <p>

                            Esta restricción protege la información
                            del ministerio y de sus usuarios.

                        </p>

                    </div>

                </div>

            </div>

        </section>

        <!-- ==================================================
             PANEL
        =================================================== -->

        <section class="login-panel">

            <div class="login-card">

                <!-- ==========================================
                     HEADER
                =========================================== -->

                <div class="login-card-header">

                    <div class="login-card-heading">

                        <h3 class="login-card-title">

                            Acceso denegado

                        </h3>

                        <p class="login-card-description">

                            No tienes permisos suficientes para
                            ingresar a esta sección.

                        </p>

                    </div>

                    <div class="login-card-divider"></div>

                </div>

                <!-- ==========================================
                     BODY
                =========================================== -->

                <div class="login-card-body">

                    <!-- ======================================
                         MENSAJE
                    ======================================= -->

                    <div class="login-alert">

                        <i class="fa-solid fa-lock"></i>

                        <p>

                            Si consideras que deberías tener acceso
                            a este módulo, comunícate con el
                            administrador del sistema.

                        </p>

                    </div>

                    <!-- ======================================
                         ACCIONES
                    ======================================= -->

                    <div class="unauthorized-actions">

                        <a
                            href="<?= BASE_URL ?>/views/dashboard.php"
                            class="login-button"
                        >

                            <span>

                                Volver al Dashboard

                            </span>

                            <i class="fa-solid fa-house"></i>

                        </a>

                        <!-- ==================================
                             LOGOUT
                        =================================== -->

                        <form
                            action="<?= BASE_URL ?>/controllers/authController.php"
                            method="POST"
                            class="logout-form"
                        >

                            <?= csrfField(); ?>

                            <input
                                type="hidden"
                                name="action"
                                value="logout"
                            >

                            <button
                                type="submit"
                                class="login-link logout-button"
                            >

                                <i class="fa-solid fa-right-from-bracket"></i>

                                Cerrar sesión

                            </button>

                        </form>

                    </div>

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

    </main>

</div>

<!-- ======================================================
     FOOTER
====================================================== -->

<footer class="login-footer">

    <p>

        © <?= date('Y') ?>

        Ministerio Remanente · Todos los derechos reservados.

    </p>

</footer>

<!-- ======================================================
     VARIABLES GLOBALES
====================================================== -->

<script>

    window.BASE_URL = "<?= BASE_URL ?>";

</script>

<!-- ======================================================
     AUTH MODULES
====================================================== -->

<script
    src="<?= BASE_URL ?>/assets/js/modules/auth/background.js">
</script>

<script
    src="<?= BASE_URL ?>/assets/js/modules/auth/particles.js">
</script>

<script
    src="<?= BASE_URL ?>/assets/js/modules/auth/ui.js">
</script>

</body>

</html>

