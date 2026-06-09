<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function generarCSRF() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}

function validarCSRF() {
    if (
        !isset($_POST['csrf_token']) ||
        !isset($_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        die("Acceso inválido (CSRF)");
    }

    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}