<?php

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../helpers/csrf.php';

$config = require __DIR__ . '/../config/app.php';

if (session_status() === PHP_SESSION_NONE) {

    session_start();
}

/* =====================================================
   CSRF
===================================================== */

generarCsrf();

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
       DATATABLES CSS
    ===================================================== -->

    <link
        rel="stylesheet"
        href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css"
    >

    <!-- =====================================================
       DATATABLE BUTTONS CSS
    ===================================================== -->

    <link
        rel="stylesheet"
        href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css"
    >

    <!-- =====================================================
       EXTRA CSS
    ===================================================== -->

    <?= $extraCSS ?? '' ?>

    <!-- =====================================================
       THEME INIT
    ===================================================== -->

    <script>

    (() => {

        const theme = localStorage.getItem('theme');

        if (theme === 'dark') {

            document.documentElement.classList.add('dark');
        }

    })();

    </script>

    <!-- =====================================================
       LIBRERÍAS
    ===================================================== -->

    <script
        defer
        src="https://code.jquery.com/jquery-3.6.0.min.js">
    </script>

    <script
        defer
        src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js">
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

    <script
        defer
        src="https://cdn.jsdelivr.net/npm/chart.js">
    </script>

    <!-- =====================================================
       COMPONENTES APP
    ===================================================== -->

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

</head>

<body>

<!-- =====================================================
   APP
===================================================== -->

<div class="app">

    <?php require_once __DIR__ . '/sidebar.php'; ?>

    <button id="themeToggle">

        <i class="fa-solid fa-moon"></i>

    </button>

    <main class="main">

        <div class="container">

    <!-- =====================================================
       SIDEBAR
    ===================================================== -->

    <?php require_once __DIR__ . '/sidebar.php'; ?>

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