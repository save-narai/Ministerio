<?php

require_once __DIR__ . '/../config/conexion.php';

$config = require __DIR__ . '/../config/app.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* =====================================================
   CSRF
===================================================== */

if (empty($_SESSION['csrf_token'])) {

    $_SESSION['csrf_token'] =
        bin2hex(random_bytes(32));
}

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <!-- =====================================================
       META
    ===================================================== -->

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <!-- =====================================================
       TITLE
    ===================================================== -->

    <title>

        <?= htmlspecialchars($config['nombre']) ?>

    </title>

    <!-- =====================================================
       APP CSS
    ===================================================== -->

    <link
        rel="stylesheet"
        href="<?= BASE_URL ?>/assets/css/app.css?v=<?= time() ?>"
    >

    <!-- =====================================================
       FONT AWESOME
    ===================================================== -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
    >

    <!-- =====================================================
       DATATABLES
    ===================================================== -->

    <link
        rel="stylesheet"
        href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css"
    >

    <!-- =====================================================
       EXTRA CSS
    ===================================================== -->

    <?php if(isset($extraCSS)) echo $extraCSS; ?>

    <!-- =====================================================
       THEME INIT
    ===================================================== -->

    <script>

    (function(){

        const theme =
            localStorage.getItem("theme");

        if(theme === "dark"){

            document.documentElement
                .classList.add("dark");
        }

    })();

    </script>

    <!-- =====================================================
       JQUERY
    ===================================================== -->

    <script
        defer
        src="https://code.jquery.com/jquery-3.6.0.min.js">
    </script>

    <!-- =====================================================
       DATATABLES
    ===================================================== -->

    <script
        defer
        src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js">
    </script>

    <!-- =====================================================
       CHART JS
    ===================================================== -->

    <script
        defer
        src="https://cdn.jsdelivr.net/npm/chart.js">
    </script>

    <!-- =====================================================
       THEME JS
    ===================================================== -->

    <script
        defer
        src="<?= BASE_URL ?>/assets/js/theme.js">
    </script>

</head>

<body>

<!-- =====================================================
   APP
===================================================== -->

<div class="app">

    <!-- =====================================================
       SIDEBAR
    ===================================================== -->

    <aside class="sidebar">

        <div class="sidebar-content">

            <!-- DASHBOARD -->

            <a href="<?= BASE_URL ?>/views/dashboard.php">

                <i class="fa-solid fa-house"></i>

                <span>Dashboard</span>

            </a>

            <!-- JOVENES -->

            <a href="<?= BASE_URL ?>/views/jovenes/index.php">

                <i class="fa-solid fa-users"></i>

                <span>Jóvenes</span>

            </a>

            <!-- REUNIONES -->

            <a href="<?= BASE_URL ?>/views/reuniones/index.php">

                <i class="fa-solid fa-calendar"></i>

                <span>Reuniones</span>

            </a>

            <!-- SEGUIMIENTOS -->

            <a href="<?= BASE_URL ?>/views/seguimientos/index.php">

                <i class="fa-solid fa-notes-medical"></i>

                <span>Seguimientos</span>

            </a>

            <!-- ROLES -->

            <a href="<?= BASE_URL ?>/views/roles/index.php">

                <i class="fa-solid fa-gear"></i>

                <span>Roles</span>

            </a>

            <!-- LOGOUT -->

            <a href="<?= BASE_URL ?>/logout.php">

                <i class="fa-solid fa-right-from-bracket"></i>

                <span>Salir</span>

            </a>

        </div>

    </aside>

    <!-- =====================================================
   THEME TOGGLE
===================================================== -->

<button id="themeToggle">

    <i class="fa-solid fa-moon"></i>

</button>

    <!-- =====================================================
       MAIN
    ===================================================== -->

    <main class="main">

        <!-- =====================================================
           CONTAINER
        ===================================================== -->

        <div class="container">