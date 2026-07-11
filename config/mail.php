<?php

declare(strict_types=1);

/* ==========================================================
   CONFIGURACIÓN DE CORREO
========================================================== */

/*
|--------------------------------------------------------------------------
| SMTP
|--------------------------------------------------------------------------
|
| Configuración del servidor de correo.
| Modifica únicamente estos valores.
|
*/

return [

    'host' => 'smtp.gmail.com',

    'port' => 587,

    'username' => 'jovenes.ica1@gmail.com',

    'password' => 'b k r p a r n i j n b t l g z n
',

    'encryption' => PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS,

    'from_email' => 'jovenes.ica1@gmail.com',

    'from_name' => 'SIG Remanente',

];