
<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('America/Bogota');

if (session_status() === PHP_SESSION_NONE) {

    session_start();
}

require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../controllers/dashboardController.php';

$config = require __DIR__ . '/../config/app.php';

/* =====================================================
   DATA
===================================================== */

$resumen = $data['resumen'] ?? [];

$graficas = $data['graficas'] ?? [
    'mensual' => [],
    'tipos'   => [],
    'estado'  => []
];

$alertas = $data['alertas'] ?? 0;

$riesgo = $data['riesgo'] ?? 0;

$alto = $data['alto'] ?? 0;

/* =====================================================
   USER
===================================================== */

$nombre =
    $_SESSION['usuario_nombre'] ?? 'Usuario';

$rol =
    $_SESSION['rol'] ?? 'USER';

/* =====================================================
   SALUDO
===================================================== */

$hora = (int) date('H');

$momento = match(true){

    $hora < 12 => 'Buenos días',

    $hora < 18 => 'Buenas tardes',

    default => 'Buenas noches'
};

$rolLabel = match($rol){

    'ADMIN'       => 'Administrador',

    'LIDER'       => 'Líder',

    'SUPERVISOR'  => 'Supervisor',

    default       => 'Usuario'
};

/* =====================================================
   CARDS
===================================================== */

$cards = [

    [
        "titulo" => "Total Jóvenes",
        "valor"  => $resumen['totalJovenes'] ?? 0,
        "icono"  => "fa-users",
        "color"  => "primary",
        "extra"  => "Registrados"
    ],

    [
        "titulo" => "Activos",
        "valor"  => $resumen['activos'] ?? 0,
        "icono"  => "fa-user-check",
        "color"  => "glass",
        "extra"  => "Actualmente"
    ],

    [
        "titulo" => "Inactivos",
        "valor"  => $resumen['inactivos'] ?? 0,
        "icono"  => "fa-user-xmark",
        "color"  => "danger",
        "extra"  => "Sin actividad"
    ],

    [
        "titulo" => "Servidores",
        "valor"  => $resumen['servidores'] ?? 0,
        "icono"  => "fa-hands-praying",
        "color"  => "primary",
        "extra"  => "Activos"
    ],

    [
        "titulo" => "Reuniones",
        "valor"  => $resumen['reuniones'] ?? 0,
        "icono"  => "fa-calendar",
        "color"  => "soft",
        "extra"  => "Realizadas"
    ],

    [
        "titulo" => "Asistencia",
        "valor"  => ($resumen['asistencia'] ?? 0) . "%",
        "icono"  => "fa-chart-line",
        "color"  => "glass",
        "extra"  => "Promedio"
    ],

    [
        "titulo" => "Nuevos",
        "valor"  => $resumen['nuevos'] ?? 0,
        "icono"  => "fa-user-plus",
        "color"  => "warning",
        "extra"  => "Este mes"
    ],

    [
        "titulo" => "Antiguos",
        "valor"  => $resumen['antiguos'] ?? 0,
        "icono"  => "fa-user-clock",
        "color"  => "soft",
        "extra"  => "Registrados"
    ]
];



require_once __DIR__ . '/../includes/header.php';

?>

<div class="page">
    <!-- =====================================================
       HEADER
    ===================================================== -->

    <div class="page-header">

        <div class="page-header-left">

            <h1 class="page-title">

                Panel Principal

            </h1>

            <div class="page-subtitle">

                <?= $momento ?>, <?= $rolLabel ?>

            </div>

        </div>

        <div class="page-header-right">

            <span class="topbar-user">

                <?= htmlspecialchars($nombre) ?>

            </span>

            <a
                href="<?= BASE_URL ?>/views/jovenes/crear.php"
                class="btn btn-primary"
            >

                <i class="fa-solid fa-plus"></i>

                <?= $config['textos']['nuevo_joven'] ?>

            </a>

        </div>

    </div>

    <!-- =====================================================
       CONTENT
    ===================================================== -->

    <div class="page-content">

        <!-- =====================================================
           ALERTA
        ===================================================== -->

        <?php if ($alertas > 0): ?>

        <div class="alerta-dashboard">

            <i class="fa-solid fa-triangle-exclamation"></i>

            <?= $riesgo ?> en riesgo •

            <?= $alto ?> en alto riesgo

        </div>

        <?php endif; ?>

        <!-- =====================================================
           SECTION
        ===================================================== -->

        <div class="page-section">

            <h2 class="page-section-title">

                Resumen General

            </h2>

            <!-- =====================================================
               CARDS
            ===================================================== -->

            <div class="dashboard__cards">

                <?php foreach($cards as $card): ?>

                <div class="dashboard__card">

                    <div class="dashboard__card-top">

                        <div class="dashboard__card-icon">

                            <i class="fa-solid <?= $card['icono'] ?>"></i>

                        </div>

                        <div class="dashboard__card-title">

                            <?= $card['titulo'] ?>

                        </div>

                    </div>

                    <div class="dashboard__card-body">

                        <div class="dashboard__card-value">

                            <?= $card['valor'] ?>

                        </div>

                        <div class="dashboard__card-extra">

                            <?= $card['extra'] ?>

                        </div>

                    </div>

                </div>

                <?php endforeach; ?>

            </div>

        </div>

    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>