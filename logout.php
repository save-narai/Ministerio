<?php

require_once "middleware/auth.php";

cerrarSesion();

header("Location: index.php");
exit();