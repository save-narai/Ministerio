<?php

require_once __DIR__ . "/services/sessionService.php";

cerrarSesion();

header("Location: index.php");
exit;