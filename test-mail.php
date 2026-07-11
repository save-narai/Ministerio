<?php

declare(strict_types=1);

require_once __DIR__ . '/services/MailService.php';

try {

    enviarCorreo(

        'jovenes.ica1@gmail.com',

        'Prueba',

        'Correo de prueba',

        '<h2>¡Hola!</h2><p>Si recibiste este correo, PHPMailer funciona correctamente.</p>'

    );

    echo 'Correo enviado correctamente.';

} catch (Exception $e) {

    echo 'Error: ' . $e->getMessage();

}