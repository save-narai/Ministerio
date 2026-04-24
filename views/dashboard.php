<?php
date_default_timezone_set('America/Bogota');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$data = require __DIR__ . '/../controllers/dashboardController.php';

$resumen = $data['resumen'] ?? [];
$graficas = $data['graficas'] ?? ['mensual'=>[], 'tipos'=>[]];

/* ✅ CSS DEL DASHBOARD */
$extraCSS = '<link rel="stylesheet" href="' . BASE_URL . '/assets/css/modules/dashboard.css">';
include __DIR__ . '/../includes/header.php';

/* =========================
   USUARIO / SALUDO
========================= */
$nombre = $_SESSION['usuario_nombre'] ?? 'Usuario';
$rol = $_SESSION['rol'] ?? null;

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
?>

<div class="main dashboard" id="mainContent">
<div class="container">

<!-- HEADER -->
<div class="dashboard__header">
  <h2 class="dashboard__title">
    <?= htmlspecialchars("$momento, $saludo") ?>
  </h2>

  <p class="dashboard__user">
    <?= htmlspecialchars($nombre) ?>
  </p>
</div>

<!-- RESUMEN -->
<h3 class="dashboard__section-title">Resumen</h3>

<div class="dashboard__cards">
<?php
$cards = [
  ["Total", $resumen['totalJovenes'] ?? 0],
  ["Activos", $resumen['activos'] ?? 0],
  ["Inactivos", $resumen['inactivos'] ?? 0],
  ["Servidores", $resumen['servidores'] ?? 0],
  ["Reuniones", $resumen['reuniones'] ?? 0],
  ["% Asistencia", ($resumen['asistencia'] ?? 0) . "%"]
];

foreach($cards as [$titulo, $valor]): ?>
  <div class="dashboard__card">
    <span class="dashboard__card-title"><?= htmlspecialchars($titulo) ?></span>
    <span class="dashboard__card-value"><?= htmlspecialchars($valor) ?></span>
  </div>
<?php endforeach; ?>
</div>

<!-- ESTADISTICAS -->
<h3 class="dashboard__section-title">Estadísticas</h3>

<div class="dashboard__grid">

  <div class="dashboard__chart">
    <h4>Asistencia mensual</h4>
    <canvas id="graficaMensual"></canvas>
  </div>

  <div class="dashboard__chart">
    <h4>Distribución por tipo</h4>
    <canvas id="graficaTipos"></canvas>
  </div>

</div>

<div class="dashboard__footer">
  <a href="<?= BASE_URL ?>/controllers/reportePDF.php" class="btn-primary">
    Descargar PDF
  </a>
</div>

</div>
</div>

<!-- =========================
     GRAFICAS (SOLO ESTO)
========================= -->
<script>
document.addEventListener("DOMContentLoaded", () => {

  const labelsMes = <?= json_encode(array_column($graficas['mensual'],'mes')) ?>;
  const dataMes   = <?= json_encode(array_column($graficas['mensual'],'presentes')) ?>;

  const labelsTipo = <?= json_encode(array_column($graficas['tipos'],'tipo')) ?>;
  const dataTipo   = <?= json_encode(array_column($graficas['tipos'],'total_presentes')) ?>;

  const isDark = document.documentElement.classList.contains("dark");

  const color = isDark ? "#17e1fc" : "#007bff";
  const textColor = isDark ? "#ffffff" : "#000000";

  if(labelsMes.length){
    new Chart(document.getElementById('graficaMensual'), {
      type: 'bar',
      data: {
        labels: labelsMes,
        datasets: [{
          data: dataMes,
          backgroundColor: color
        }]
      },
      options: {
        plugins:{
          legend:{ labels:{ color:textColor } }
        },
        scales:{
          x:{ ticks:{ color:textColor } },
          y:{ ticks:{ color:textColor } }
        }
      }
    });
  }

  if(labelsTipo.length){
    new Chart(document.getElementById('graficaTipos'), {
      type: 'pie',
      data: {
        labels: labelsTipo,
        datasets: [{
          data: dataTipo
        }]
      },
      options: {
        plugins:{
          legend:{ labels:{ color:textColor } }
        }
      }
    });
  }

});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>