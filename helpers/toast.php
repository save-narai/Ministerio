<?php

function setToast($mensaje) {
    $_SESSION["error"] = $mensaje;
}

function showToastJS() {
    if (isset($_SESSION["error"])) {
        echo "
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                showToast('" . addslashes($_SESSION["error"]) . "');
            });
        </script>
        ";
        unset($_SESSION["error"]);
    }
}