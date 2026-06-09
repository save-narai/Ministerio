<?php
return [
    'nombre' => $_ENV['APP_NAME'] ?? 'RUT',
    'logo' => $_ENV['APP_LOGO'] ?? 'logo.png',
    'ruta_logo' => '/assets/img/logo.png',
    'color_principal' => $_ENV['APP_COLOR'] ?? '#ff4b4b',

    'modo' => $_ENV['APP_ENV'] ?? 'production',
    'timezone' => $_ENV['APP_TIMEZONE'] ?? 'America/Bogota',

    'modulos' => [
        'jovenes' => true,
        'reuniones' => true,
        'seguimientos' => true,
    ],

    'textos' => [
        'dashboard' => 'Dashboard',
        'nuevo_joven' => 'Nuevo Registro',
    ]
];