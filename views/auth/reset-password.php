<?php

declare(strict_types=1);
require_once __DIR__ . '/../../config/bootstrap.php';

require_once __DIR__ . '/../../config/conexion.php';

require_once __DIR__ . '/../../middleware/guest.php';
require_once __DIR__ . '/../../helpers/csrf.php';
require_once __DIR__ . '/../../services/AuthService.php';


exigirInvitado();

generarCsrf();

/* ==========================================================
   CONFIG
========================================================== */

$config = $GLOBALS['config'];

$version = $config['version'] ?? '2.0';

/* ==========================================================
   FLASH
========================================================== */

$error = getFlash('error');

$success = getFlash('success');

/* ==========================================================
   TOKEN
========================================================== */

$token = trim($_GET['token'] ?? '');

/* ==========================================================
   VALIDAR TOKEN
========================================================== */

if (

    empty($token)

) {

    redirect(

        'login.php',

        'error',

        'El enlace de recuperación no es válido.'

    );

}

$usuario = validarTokenRecuperacion(

    $pdo,

    $token

);

if (

    !$usuario

) {

    redirect(

        'login.php',

        'error',

        'El enlace de recuperación ha expirado o no es válido.'

    );

}

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

<body class="auth auth-reset">

<!-- ==========================================================
     RESET PASSWORD
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

        Crea una nueva
        <strong>contraseña</strong>
        segura.

    </h1>

    <!-- ==============================================
         DESCRIPCIÓN
    =============================================== -->

    <p class="login-description">

        Estás a un paso de recuperar el acceso a tu cuenta.
        Define una contraseña segura y continúa utilizando
        el Sistema de Seguimiento Ministerial con total confianza.

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

                    Nueva contraseña

                </h3>

                <p class="login-card-description">

                    Define una nueva contraseña para recuperar
                    el acceso a tu cuenta.

                </p>

            </div>

            <div class="login-card-divider"></div>

        </div>

        <!-- ==========================================
             MENSAJES
        =========================================== -->

        <?php if (!empty($error)): ?>

            <div class="login-alert">

                <i class="fa-solid fa-circle-exclamation"></i>

                <p>

                    <?= htmlspecialchars($error) ?>

                </p>

            </div>

        <?php endif; ?>

        <?php if (!empty($success)): ?>

            <div class="login-alert login-alert-success">

                <i class="fa-solid fa-circle-check"></i>

                <p>

                    <?= htmlspecialchars($success) ?>

                </p>

            </div>

        <?php endif; ?>

        <!-- ==========================================
             FORMULARIO
        =========================================== -->

        <div class="login-card-body">

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

                <!-- ======================================
     NUEVA CONTRASEÑA
====================================== -->

<div class="login-group">

    <label
        class="login-label"
        for="password"
    >

        <i class="fa-solid fa-lock"></i>

        Nueva contraseña

    </label>

    <div class="login-input-wrapper">

        <i class="fa-solid fa-lock login-input-icon"></i>

        <input

            id="password"

            name="password"

            type="password"

            class="login-input"

            placeholder="Ingresa tu nueva contraseña"

            autocomplete="new-password"

            required

        >

        <button

            type="button"

            class="password-toggle"

            data-toggle="#password"

            aria-label="Mostrar contraseña"

        >

            <i class="fa-solid fa-eye"></i>

        </button>

    </div>

</div>

<!-- ======================================
     SEGURIDAD
====================================== -->

<div class="password-strength">

    <div

        id="passwordStrengthBar"

        class="password-strength-bar"

    ></div>

</div>

<div
    id="passwordStrengthText"
    class="password-strength-text"
    aria-live="polite"
>

    Seguridad de la contraseña

</div>

<!-- ======================================
     CONFIRMAR CONTRASEÑA
====================================== -->

<div class="login-group">

    <label
        class="login-label"
        for="confirm_password"
    >

        <i class="fa-solid fa-lock"></i>

        Confirmar contraseña

    </label>

    <div class="login-input-wrapper">

        <i class="fa-solid fa-lock login-input-icon"></i>

        <input

            id="confirm_password"

            name="confirm_password"

            type="password"

            class="login-input"

            placeholder="Confirma tu contraseña"

            autocomplete="new-password"

            required

        >

        <button

            type="button"

            class="password-toggle"

            data-toggle="#confirm_password"

            aria-label="Mostrar contraseña"

        >

            <i class="fa-solid fa-eye"></i>

        </button>

    </div>

</div>

<!-- ======================================
     CHECKLIST
====================================== -->

<div class="password-checklist">

    <div
        class="password-check"
        data-check="length"
    >

        <i class="fa-solid fa-circle"></i>

        <span>Mínimo 8 caracteres</span>

    </div>

    <div
        class="password-check"
        data-check="upper"
    >

        <i class="fa-solid fa-circle"></i>

        <span>Una letra mayúscula</span>

    </div>

    <div
        class="password-check"
        data-check="lower"
    >

        <i class="fa-solid fa-circle"></i>

        <span>Una letra minúscula</span>

    </div>

    <div
        class="password-check"
        data-check="number"
    >

        <i class="fa-solid fa-circle"></i>

        <span>Un número</span>

    </div>

    <div
        class="password-check"
        data-check="symbol"
    >

        <i class="fa-solid fa-circle"></i>

        <span>Un carácter especial</span>

    </div>

    <div
        class="password-check"
        data-check="match"
    >

        <i class="fa-solid fa-circle"></i>

        <span>Las contraseñas coinciden</span>

    </div>

</div>

<!-- ======================================
     BOTÓN
====================================== -->

<button
    type="submit"
    id="btnResetPassword"
    class="login-button"
>

    <span>

        Guardar nueva contraseña

    </span>

    <i class="fa-solid fa-floppy-disk login-button-icon"></i>

</button>

</form>

</div>

<!-- ==========================================
             FOOTER CARD
        =========================================== -->

        <footer class="login-card-footer">

            <small class="login-copyright">

                Tu contraseña será actualizada
                inmediatamente después de guardarla.

            </small>

            <span class="login-version">

                Versión <?= htmlspecialchars($version) ?>

            </span>

        </footer>

    </div>

</section>

</main>

</div>

<!-- ==========================================================
     VARIABLES GLOBALES
========================================================== -->

<script>

    window.BASE_URL =
        "<?= BASE_URL ?>";

    window.RESET_PASSWORD =
        true;

</script>

<!-- ==========================================================
     AUTH MODULE
========================================================== -->

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

<script
    src="<?= BASE_URL ?>/assets/js/modulos/auth/reset-password.js">
</script>

<script src="<?= BASE_URL ?>/assets/js/modulos/auth/password-toggle.js"></script>



</body>

</html>