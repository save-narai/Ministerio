<?php

declare(strict_types=1);

/* ==========================================================
   BOOTSTRAP
========================================================== */

require_once __DIR__ . '/../config/bootstrap.php';

/* ==========================================================
   CONFIGURACIÓN GLOBAL
========================================================== */

$config = $GLOBALS['config'];

$tituloPagina ??= $config['nombre'] ?? 'Ministerio';

$extraCSS ??= '';

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <!-- ======================================================
       META
    ======================================================= -->

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="description"
        content="Sistema de Seguimiento Ministerial"
    >

    <meta
        name="author"
        content="Ministerio Remanente"
    >

    <!-- ======================================================
       TITLE
    ======================================================= -->

    <title>

        <?= htmlspecialchars($tituloPagina) ?>

    </title>

    <!-- ======================================================
       FAVICON
    ======================================================= -->

    <link
        rel="icon"
        type="image/png"
        href="<?= BASE_URL ?>/assets/img/favicon.png"
    >

    <!-- ======================================================
       APP CSS
    ======================================================= -->

    <link
        rel="stylesheet"
        href="<?= BASE_URL ?>/assets/css/app.css?v=<?= filemtime(__DIR__ . '/../assets/css/app.css') ?>"
    >

    <!-- ======================================================
       FONT AWESOME
    ======================================================= -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    >

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
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <!-- ======================================================
       DATATABLES CSS
    ======================================================= -->

    <link
        rel="stylesheet"
        href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css"
    >

    <link
        rel="stylesheet"
        href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css"
    >

    <!-- ======================================================
       CSS ADICIONAL DEL MÓDULO
    ======================================================= -->

    <?= $extraCSS ?>

    <!-- ======================================================
       TEMA (ANTES DE CARGAR LA PÁGINA)
    ======================================================= -->

    <script>

        (() => {

            const theme = localStorage.getItem('theme');

            if (theme === 'dark') {

                document.documentElement.classList.add('dark');

            }

        })();

    </script>

    <!-- ======================================================
       JQUERY
    ======================================================= -->

    <script
        defer
        src="https://code.jquery.com/jquery-3.7.1.min.js">
    </script>

    <!-- ======================================================
       DATATABLES
    ======================================================= -->

    <script
        defer
        src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js">
    </script>

    <script
        defer
        src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js">
    </script>

    <script
        defer
        src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js">
    </script>

    <script
        defer
        src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js">
    </script>

    <script
        defer
        src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js">
    </script>

    <script
        defer
        src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js">
    </script>

    <script
        defer
        src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js">
    </script>

    <!-- ======================================================
       CHART.JS
    ======================================================= -->

    <script
        defer
        src="https://cdn.jsdelivr.net/npm/chart.js">
    </script>

    <!-- ======================================================
       COMPONENTES GLOBALES
    ======================================================= -->

    <script
        defer
        src="<?= BASE_URL ?>/assets/js/theme.js">
    </script>

    <script
        defer
        src="<?= BASE_URL ?>/assets/js/components/datatable.js">
    </script>

    <script
        defer
        src="<?= BASE_URL ?>/assets/js/components/datatable-export.js">
    </script>

    <script
        defer
        src="<?= BASE_URL ?>/assets/js/components/search.js">
    </script>

    <script
        defer
        src="<?= BASE_URL ?>/assets/js/components/filters.js">
    </script>

    <script
        defer
        src="<?= BASE_URL ?>/assets/js/components/phone-validation.js">
    </script>

    <!-- ======================================================
       JAVASCRIPT ADICIONAL DEL MÓDULO
    ======================================================= -->

    <?= $extraJS ?? '' ?>

</head>

<body>

<!-- ======================================================
   APP
====================================================== -->

<div class="app">

    <!-- ==================================================
       SIDEBAR
    =================================================== -->

    <?php require_once __DIR__ . '/sidebar.php'; ?>

    <!-- ==================================================
       CONTENIDO PRINCIPAL
    =================================================== -->

    <main class="main">

        <!-- ==============================================
           TOPBAR
        =============================================== -->

        <header class="topbar">

            <div class="topbar-left">

                <button
                    type="button"
                    id="sidebarToggle"
                    class="topbar-toggle"
                    aria-label="Mostrar menú"
                >

                    <i class="fa-solid fa-bars"></i>

                </button>

                <div class="topbar-title">

                    <h1>

                        <?= htmlspecialchars($tituloPagina) ?>

                    </h1>

                </div>

            </div>

            <div class="topbar-right">

                <!-- ======================================
                   CAMBIO DE TEMA
                ======================================= -->

                <button
                    type="button"
                    id="themeToggle"
                    class="theme-toggle"
                    aria-label="Cambiar tema"
                >

                    <i class="fa-solid fa-moon"></i>

                </button>

            </div>

        </header>

        <!-- ==============================================
           CONTENEDOR
        =============================================== -->

        <section class="container">

            <?php

            if ($mensaje = getFlash('success')):

            ?>

                <div class="alert alert-success">

                    <i class="fa-solid fa-circle-check"></i>

                    <?= htmlspecialchars($mensaje) ?>

                </div>

            <?php endif; ?>

            <?php

            if ($mensaje = getFlash('error')):

            ?>

                <div class="alert alert-danger">

                    <i class="fa-solid fa-circle-exclamation"></i>

                    <?= htmlspecialchars($mensaje) ?>

                </div>

            <?php endif; ?>

            <?php

            if ($mensaje = getFlash('warning')):

            ?>

                <div class="alert alert-warning">

                    <i class="fa-solid fa-triangle-exclamation"></i>

                    <?= htmlspecialchars($mensaje) ?>

                </div>

            <?php endif; ?>

            <?php

            if ($mensaje = getFlash('info')):

            ?>

                <div class="alert alert-info">

                    <i class="fa-solid fa-circle-info"></i>

                    <?= htmlspecialchars($mensaje) ?>

                </div>

            <?php endif; ?>

            <!-- ==============================================
               INICIO DEL CONTENIDO DE LA VISTA
            =============================================== -->

            <?php

            /*
            |--------------------------------------------------
            | A partir de este punto comienza el contenido
            | específico de cada módulo.
            |
            | Dashboard
            | Usuarios
            | Roles
            | Jóvenes
            | Reuniones
            | Seguimientos
            | Configuración
            |
            | El cierre de:
            |
            | </section>
            | </main>
            | </div>
            | </body>
            | </html>
            |
            | se encuentra en:
            |
            | includes/footer.php
            |--------------------------------------------------
            */

            ?>