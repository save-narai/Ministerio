<?php
date_default_timezone_set('America/Bogota');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../middleware/auth.php';
$config = require __DIR__ . '/../config/app.php';

$data = require __DIR__ . '/../controllers/dashboardController.php';

$resumen = $data['resumen'] ?? [];
$graficas = $data['graficas'] ?? [
    'mensual' => [],
    'tipos'   => [],
    'estado'  => []
];
$alertas = $data['alertas'] ?? 0;

$extraCSS = '
<link rel="stylesheet" href="' . BASE_URL . '/assets/css/modules/dashboard.css">
';

require_once __DIR__ . '/../includes/header.php';

/* USER */
$nombre = $_SESSION['usuario_nombre'] ?? 'Usuario';
$rol = $_SESSION['rol'] ?? 'USER';

/* SALUDO PRO */
$hora = (int) date('H');
$momento = match(true){
    $hora < 12 => 'Buenos días',
    $hora < 18 => 'Buenas tardes',
    default => 'Buenas noches'
};

$rolLabel = match($rol){
    'ADMIN' => 'Administradora',
    'LIDER' => 'Líder',
    'SUPERVISOR' => 'Supervisor',
    default => 'Usuario'
};

/* CARDS */
$cards = [
    ["titulo"=>"Total Jóvenes","valor"=>$resumen['totalJovenes']??0,"icono"=>"fa-users","color"=>"cyan","extra"=>"Registrados"],
    ["titulo"=>"Activos","valor"=>$resumen['activos']??0,"icono"=>"fa-user-check","color"=>"green","extra"=>"Actualmente"],
    ["titulo"=>"Inactivos","valor"=>$resumen['inactivos']??0,"icono"=>"fa-user-xmark","color"=>"red","extra"=>"Sin actividad"],
    ["titulo"=>"Servidores","valor"=>$resumen['servidores']??0,"icono"=>"fa-hands-praying","color"=>"purple","extra"=>"Activos"],
    ["titulo"=>"Reuniones","valor"=>$resumen['reuniones']??0,"icono"=>"fa-calendar","color"=>"orange","extra"=>"Realizadas"],
    ["titulo"=>"Asistencia","valor"=>($resumen['asistencia']??0)."%","icono"=>"fa-chart-line","color"=>"blue","extra"=>"Promedio"],

    // 🔥 NUEVAS
    ["titulo"=>"Nuevos","valor"=>$resumen['nuevos']??0,"icono"=>"fa-user-plus","color"=>"green","extra"=>"Este mes"],
    ["titulo"=>"Antiguos","valor"=>$resumen['antiguos']??0,"icono"=>"fa-user-clock","color"=>"purple","extra"=>"Registrados"]
];
?>

<div class="container">

<!-- TOPBAR PRO -->
<div class="topbar">
    <div class="topbar-left">
        <div class="logo-mini">
            <i class="fa-solid fa-layer-group"></i>
        </div>
        <div>
            <div class="topbar-title">
                Panel
            </div>
            <div class="topbar-sub">
                <?= $momento ?>, <?= $rolLabel ?>
            </div>
        </div>
    </div>

    <div class="topbar-right">
        <span class="topbar-user">
            <?= htmlspecialchars($nombre) ?>
        </span>

        <a href="<?= BASE_URL ?>/views/jovenes/crear.php" class="btn-primary">
            <i class="fa-solid fa-plus"></i>
            <?= $config['textos']['nuevo_joven'] ?>
        </a>
    </div>
</div>

<h3 class="dashboard__section-title">Resumen General</h3>

<div class="dashboard__cards">
<?php foreach($cards as $card): ?>
<div class="dashboard__card dashboard__card--<?= $card['color'] ?>">

    <div class="dashboard__card-top">
        <span class="dashboard__card-icon">
            <i class="fa-solid <?= $card['icono'] ?>"></i>
        </span>
        <span class="dashboard__card-title">
            <?= $card['titulo'] ?>
        </span>
    </div>

    <div class="dashboard__card-body">
        <span class="dashboard__card-value">
            <?= $card['valor'] ?>
        </span>
        <span class="dashboard__card-extra">
            <?= $card['extra'] ?>
        </span>
    </div>

</div>
<?php endforeach; ?>
</div>

<?php if ($alertas > 0): ?>
<div class="alerta-dashboard">
    <i class="fa-solid fa-triangle-exclamation"></i>
    <?= (int)$alertas ?> requieren seguimiento
</div>
<?php endif; ?>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>