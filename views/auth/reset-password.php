<?php

declare(strict_types=1);

require_once __DIR__ . '/../../middleware/guest.php';
require_once __DIR__ . '/../../middleware/csrf.php';
require_once __DIR__ . '/../../helpers/flash.php';

exigirInvitado();

generarCSRF();

$error = flash('error');
$success = flash('success');

/*
|--------------------------------------------------------------------------
| Token
|--------------------------------------------------------------------------
|
| Aquí posteriormente validaremos que el token exista en la base
| de datos y no haya expirado.
|
*/

$token = trim($_GET['token'] ?? '');

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
        content="Restablecer contraseña"
    >

    <title>

        Nueva contraseña | Remanente

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

    <!-- FontAwesome -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    >

    <!-- CSS -->

    <link
        rel="stylesheet"
        href="<?= BASE_URL ?>/assets/css/main.css"
    >

</head>

<body>

<div class="login-page">

    <!-- =====================================
         BACKGROUND
    ====================================== -->

    <div class="login-background">

        <img
            src="<?= BASE_URL ?>/assets/img/1396974.png"
            alt="Background"
            class="login-background-image"
        >

        <div class="login-overlay"></div>

        <canvas id="fx"></canvas>

    </div>

    <!-- =====================================
         CONTENEDOR
    ====================================== -->

    <main class="login-container">

        <section class="login-hero">

        <!-- =========================================
             PANEL IZQUIERDO
        ========================================== -->

        <div class="login-hero__content">

            <div class="login-brand">

                <div class="login-brand__logo">

                    <i class="fa-solid fa-shield-halved"></i>

                </div>

                <span class="login-brand__name">

                    Ministerio Remanente

                </span>

            </div>

            <div class="login-hero__text">

                <span class="login-hero__badge">

                    Seguridad de la Cuenta

                </span>

                <h1 class="login-hero__title">

                    Crea una nueva contraseña
                    para proteger tu cuenta.

                </h1>

                <p class="login-hero__description">

                    Estás a un paso de recuperar el acceso.
                    Utiliza una contraseña segura que solo tú
                    conozcas y evita reutilizar claves de otros
                    servicios.

                </p>

            </div>

            <!-- RECOMENDACIONES -->

            <div class="login-features">

                <div class="login-feature">

                    <i class="fa-solid fa-lock"></i>

                    <div>

                        <strong>

                            Contraseña segura

                        </strong>

                        <span>

                            Usa mínimo 8 caracteres.

                        </span>

                    </div>

                </div>

                <div class="login-feature">

                    <i class="fa-solid fa-user-shield"></i>

                    <div>

                        <strong>

                            Solo para ti

                        </strong>

                        <span>

                            No compartas tu contraseña.

                        </span>

                    </div>

                </div>

                <div class="login-feature">

                    <i class="fa-solid fa-shield-heart"></i>

                    <div>

                        <strong>

                            Protección continua

                        </strong>

                        <span>

                            Mantén tu cuenta siempre segura.

                        </span>

                    </div>

                </div>

            </div>

            <!-- MENSAJE -->

            <blockquote class="login-verse">

                <i class="fa-solid fa-circle-check"></i>

                <p>

                    Una contraseña fuerte es la primera
                    línea de defensa para proteger
                    tu información.

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

                        <i class="fa-solid fa-key"></i>

                    </div>

                    <h2>

                        Nueva contraseña

                    </h2>

                    <p>

                        Define una nueva contraseña
                        para recuperar el acceso a tu cuenta.

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
                    id="resetPasswordForm"
                    class="login-form"
                    action="<?= BASE_URL ?>/controllers/authController.php"
                    method="POST"
                    autocomplete="off"
                >

                    <?= csrfField(); ?>

                    <input
                        type="hidden"
                        name="action"
                        value="reset_password"
                    >

                    <input
                        type="hidden"
                        name="token"
                        value="<?= htmlspecialchars($token) ?>"
                    >

                    <!-- NUEVA CONTRASEÑA -->

                    <div class="login-field">

                        <label
                            for="password"
                            class="login-label"
                        >

                            Nueva contraseña

                        </label>

                        <div class="login-input-group">

                            <i class="fa-solid fa-lock"></i>

                            <input
                                id="password"
                                type="password"
                                name="password"
                                class="login-input"
                                placeholder="Nueva contraseña"
                                autocomplete="new-password"
                                required
                            >

                            <button
                                type="button"
                                class="login-password-toggle"
                                data-toggle="#password"
                            >

                                <i class="fa-solid fa-eye"></i>

                            </button>

                        </div>

                    </div>

                    <!-- BARRA DE SEGURIDAD -->

                    <div class="password-strength">

                        <div
                            class="password-strength__bar"
                            id="passwordStrengthBar"
                        ></div>

                    </div>

                    <div
                        class="password-strength__text"
                        id="passwordStrengthText"
                    >

                        Seguridad de la contraseña

                    </div>

                    <!-- CONFIRMAR -->

                    <div class="login-field">

                        <label
                            for="confirm_password"
                            class="login-label"
                        >

                            Confirmar contraseña

                        </label>

                        <div class="login-input-group">

                            <i class="fa-solid fa-lock"></i>

                            <input
                                id="confirm_password"
                                type="password"
                                name="confirm_password"
                                class="login-input"
                                placeholder="Repite la contraseña"
                                autocomplete="new-password"
                                required
                            >

                            <button
                                type="button"
                                class="login-password-toggle"
                                data-toggle="#confirm_password"
                            >

                                <i class="fa-solid fa-eye"></i>

                            </button>

                        </div>

                    </div>

                    <!-- CHECKLIST -->

                    <div class="password-checklist">

                        <div
                            class="password-check"
                            data-check="length"
                        >

                            <i class="fa-solid fa-circle"></i>

                            Mínimo 8 caracteres

                        </div>

                        <div
                            class="password-check"
                            data-check="upper"
                        >

                            <i class="fa-solid fa-circle"></i>

                            Una letra mayúscula

                        </div>

                        <div
                            class="password-check"
                            data-check="lower"
                        >

                            <i class="fa-solid fa-circle"></i>

                            Una letra minúscula

                        </div>

                        <div
                            class="password-check"
                            data-check="number"
                        >

                            <i class="fa-solid fa-circle"></i>

                            Un número

                        </div>

                        <div
                            class="password-check"
                            data-check="symbol"
                        >

                            <i class="fa-solid fa-circle"></i>

                            Un carácter especial

                        </div>

                        <div
                            class="password-check"
                            data-check="match"
                        >

                            <i class="fa-solid fa-circle"></i>

                            Las contraseñas coinciden

                        </div>

                    </div>

                    <!-- BOTÓN -->

                    <button
                        type="submit"
                        id="btnResetPassword"
                        class="login-button"
                    >

                        <span class="login-button-text">

                            Guardar nueva contraseña

                        </span>

                        <span class="login-button-loader">

                            <i class="fa-solid fa-spinner fa-spin"></i>

                        </span>

                        <i class="fa-solid fa-floppy-disk"></i>

                    </button>

                </form>

                <!-- ACCIONES -->

                <div class="login-card__actions">

                    <a
                        href="<?= BASE_URL ?>/views/auth/login.php"
                        class="login-link"
                    >

                        <i class="fa-solid fa-arrow-left"></i>

                        Volver al inicio de sesión

                    </a>

                </div>

                <!-- FOOTER -->

                <div class="login-card__footer">

                    <span>

                        Tu contraseña será actualizada inmediatamente.

                    </span>

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

window.RESET_PASSWORD = true;

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