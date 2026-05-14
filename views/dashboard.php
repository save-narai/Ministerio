<?php

/* =========================
   CONFIG
========================= */

date_default_timezone_set('America/Bogota');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* =========================
   AUTH
========================= */

require_once __DIR__ . '/../middleware/auth.php';

/* =========================
   CONTROLLER
========================= */

$data = require __DIR__ . '/../controllers/dashboardController.php';

/* =========================
   DATA
========================= */

$resumen = $data['resumen'] ?? [];

$graficas = $data['graficas'] ?? [
    'mensual' => [],
    'tipos'   => [],
    'estado'  => []
];

$alertas = $data['alertas'] ?? 0;

/* =========================
   CSS
========================= */

$extraCSS = '
<link rel="stylesheet" href="' . BASE_URL . '/assets/css/modules/dashboard.css">
';

/* =========================
   HEADER
========================= */

require_once __DIR__ . '/../includes/header.php';

/* =========================
   USER
========================= */

$nombre = $_SESSION['usuario_nombre'] ?? 'Usuario';

$rol = $_SESSION['rol'] ?? null;

/* =========================
   SALUDO
========================= */

$saludo = match($rol){

    'ADMIN' => 'Administradora',

    'LIDER' => 'Líder',

    default => 'Usuario'
};

$hora = (int) date('H');

$momento = match(true){

    $hora < 12 => 'Buenos días',

    $hora < 18 => 'Buenas tardes',

    default => 'Buenas noches'
};

/* =========================
   CARDS
========================= */

$cards = [

    [
        "titulo" => "Total Jóvenes",
        "valor"  => $resumen['totalJovenes'] ?? 0,
        "icono"  => "👥",
        "color"  => "cyan",
        "extra"  => "Registrados"
    ],

    [
        "titulo" => "Activos",
        "valor"  => $resumen['activos'] ?? 0,
        "icono"  => "🟢",
        "color"  => "green",
        "extra"  => "Actualmente"
    ],

    [
        "titulo" => "Inactivos",
        "valor"  => $resumen['inactivos'] ?? 0,
        "icono"  => "🔴",
        "color"  => "red",
        "extra"  => "Sin actividad"
    ],

    [
        "titulo" => "Servidores",
        "valor"  => $resumen['servidores'] ?? 0,
        "icono"  => "🙏",
        "color"  => "purple",
        "extra"  => "Activos"
    ],

    [
        "titulo" => "Reuniones",
        "valor"  => $resumen['reuniones'] ?? 0,
        "icono"  => "📅",
        "color"  => "orange",
        "extra"  => "Realizadas"
    ],

    [
        "titulo" => "Asistencia",
        "valor"  => ($resumen['asistencia'] ?? 0) . "%",
        "icono"  => "📊",
        "color"  => "blue",
        "extra"  => "Promedio"
    ]
];

?>

<div class="main dashboard" id="mainContent">

<div class="container">

    <!-- =========================
         HEADER
    ========================= -->

    <div class="dashboard__header">

        <div>

            <h2 class="dashboard__title">
                <?= htmlspecialchars("$momento, $saludo") ?>
            </h2>

            <p class="dashboard__user">
                <?= htmlspecialchars($nombre) ?>
            </p>

        </div>

    </div>

    <!-- =========================
         RESUMEN
    ========================= -->

    <h3 class="dashboard__section-title">
        Resumen General
    </h3>

    <div class="dashboard__cards">

        <?php foreach($cards as $card): ?>

        <div class="dashboard__card dashboard__card--<?= htmlspecialchars($card['color']) ?>">

            <div class="dashboard__card-top">

                <span class="dashboard__card-icon">
                    <?= $card['icono'] ?>
                </span>

                <span class="dashboard__card-title">
                    <?= htmlspecialchars($card['titulo']) ?>
                </span>

            </div>

            <div class="dashboard__card-body">

                <span class="dashboard__card-value">
                    <?= htmlspecialchars($card['valor']) ?>
                </span>

                <span class="dashboard__card-extra">
                    <?= htmlspecialchars($card['extra']) ?>
                </span>

            </div>

        </div>

        <?php endforeach; ?>

    </div>

    <!-- =========================
         ALERTAS
    ========================= -->

    <?php if ($alertas > 0): ?>

    <div class="alerta-dashboard">

        🚨 <?= (int)$alertas ?>
        jóvenes necesitan seguimiento hoy

    </div>

    <?php endif; ?>

    <!-- =========================
         ESTADISTICAS
    ========================= -->

    <h3 class="dashboard__section-title">
        Estadísticas
    </h3>

    <div class="dashboard__grid">

        <!-- BARRAS -->
        <div class="dashboard__chart">

            <div class="dashboard__chart-header">

                <h4>📊 Asistencia mensual</h4>

            </div>

            <div class="dashboard__chart-body">
                <canvas id="graficaMensual"></canvas>
            </div>

        </div>

        <!-- PIE -->
        <div class="dashboard__chart">

            <div class="dashboard__chart-header">

                <h4>🥧 Estado espiritual</h4>

            </div>

            <div class="dashboard__chart-body">
                <canvas id="graficaTipos"></canvas>
            </div>

        </div>

        <!-- DONUT -->
        <div class="dashboard__chart">

            <div class="dashboard__chart-header">

                <h4>📈 Nuevos vs Antiguos</h4>

            </div>

            <div class="dashboard__chart-body">
                <canvas id="graficaEstado"></canvas>
            </div>

        </div>

    </div>

    <!-- =========================
         FOOTER
    ========================= -->

    <div class="dashboard__footer">

        <a href="<?= BASE_URL ?>/controllers/reportePDF.php"
           class="btn-primary">

            📄 Descargar PDF

        </a>

    </div>

</div>
</div>

<!-- =========================
     CHARTS
========================= -->

<script>

document.addEventListener("DOMContentLoaded", () => {

    /* =========================
       DATA
    ========================= */

    const labelsMes = <?= json_encode(array_column($graficas['mensual'], 'mes')) ?>;

    const dataMes = <?= json_encode(array_column($graficas['mensual'], 'presentes')) ?>;

    const labelsTipo = <?= json_encode(array_column($graficas['tipos'], 'tipo')) ?>;

    const dataTipo = <?= json_encode(array_column($graficas['tipos'], 'total')) ?>;

    const labelsEstado = <?= json_encode(array_column($graficas['estado'], 'tipo')) ?>;

    const dataEstado = <?= json_encode(array_column($graficas['estado'], 'total')) ?>;

    /* =========================
       DARK MODE
    ========================= */

    const isDark = document.documentElement.classList.contains("dark");

    const textColor = isDark
        ? "#ffffff"
        : "#1f2937";

    const barColor = isDark
        ? "rgba(23,225,252,.75)"
        : "rgba(0,123,255,.75)";

    const donutColors = isDark
        ? ["#f59e0b", "#6366f1"]
        : ["#d97706", "#4f46e5"];

    /* =========================
       CHART DEFAULTS
    ========================= */

    Chart.defaults.color = textColor;

    Chart.defaults.font.family = "'Poppins', sans-serif";

    /* =========================
       BARRAS
    ========================= */

    if (labelsMes.length) {

        new Chart(document.getElementById('graficaMensual'), {

            type: 'bar',

            data: {

                labels: labelsMes,

                datasets: [{

                    label: 'Asistencia',

                    data: dataMes,

                    backgroundColor: barColor,

                    borderRadius: 10,

                    borderSkipped: false

                }]
            },

            options: {

                responsive: true,

                maintainAspectRatio: false,

                plugins: {

                    legend: {

                        labels: {
                            color: textColor
                        }
                    }
                },

                scales: {

                    x: {

                        ticks: {
                            color: textColor
                        },

                        grid: {
                            display: false
                        }
                    },

                    y: {

                        ticks: {
                            color: textColor
                        },

                        grid: {
                            color: isDark
                                ? "rgba(255,255,255,.08)"
                                : "rgba(0,0,0,.06)"
                        }
                    }
                }
            }
        });
    }

    /* =========================
       PIE
    ========================= */

    if (labelsTipo.length) {

        new Chart(document.getElementById('graficaTipos'), {

            type: 'pie',

            data: {

                labels: labelsTipo,

                datasets: [{

                    data: dataTipo,

                    backgroundColor: [
                        "#6366f1",
                        "#22c55e",
                        "#f59e0b",
                        "#ef4444",
                        "#8b5cf6"
                    ]
                }]
            },

            options: {

                responsive: true,

                maintainAspectRatio: false,

                plugins: {

                    legend: {

                        labels: {
                            color: textColor
                        }
                    }
                }
            }
        });
    }

    /* =========================
       DONUT
    ========================= */

    if (labelsEstado.length) {

        new Chart(document.getElementById('graficaEstado'), {

            type: 'doughnut',

            data: {

                labels: labelsEstado,

                datasets: [{

                    data: dataEstado,

                    backgroundColor: donutColors,

                    borderWidth: 0
                }]
            },

            options: {

                responsive: true,

                maintainAspectRatio: false,

                cutout: '65%',

                plugins: {

                    legend: {

                        labels: {
                            color: textColor
                        }
                    }
                }
            }
        });
    }

});

</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>