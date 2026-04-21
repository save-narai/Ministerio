<?php

date_default_timezone_set('America/Bogota');
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$data = require __DIR__ . '/../controllers/dashboardController.php';
extract($data);

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

$hora = date('H');

$momento = match(true){
  $hora < 12 => 'Buenos días',
  $hora < 18 => 'Buenas tardes',
  default => 'Buenas noches'
};
?>

<div class="header-dashboard">

<h2 class="titulo-dashboard"
style="
  font-size: 30px;
  color:white;
  -webkit-text-stroke: 1px black;
  text-shadow: 0 0 20px black;
"
    ">
  👋 <?= $momento ?><?= $saludo ? ", $saludo" : "" ?>
</h2>

  <p class="text-muted">
    Rol: <?= $rol ?: 'N/A' ?>
  </p>

</div>

<hr>
<br>
<br>

<h3>🟩 Resumen</h3>

<div class="cards">

  <?php
  $cards = [
    ["Total", $totalJovenes],
    ["Activos", $activos],
    ["Inactivos", $inactivos],
    ["Servidores", $totalServidores],
    ["Reuniones", $totalReuniones],
    ["Asistencia", $porcentajeGeneral . "%"]
  ];

  foreach($cards as [$titulo, $valor]): ?>
    <div class="card">
      <div class="text-muted"><?= $titulo ?></div>
      <div class="stat"><?= $valor ?></div>
    </div>
  <?php endforeach; ?>

</div>

<hr style="margin-top:40px;">


<h3>📈 Estadísticas</h3>

<br>
<br>

<div class="reporte-grid">
  <div class="reporte-card">
    <h4>Asistencia mensual</h4>
    <canvas id="graficaMensual"></canvas>
  </div>

  <div class="reporte-card">
    <h4>Distribución por tipo</h4>
    <canvas id="graficaTipos"></canvas>
  </div>
</div>

<div class="reporte-footer">
  <a href="<?= BASE_URL ?>/controllers/reportePDF.php" class="btn-primary">
    📄 Descargar PDF
  </a>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {

  const labelsMes = <?= json_encode(array_column($reporteMensual,'mes')) ?>;
  const dataMes   = <?= json_encode(array_column($reporteMensual,'presentes')) ?>;

  const labelsTipo = <?= json_encode(array_column($comparacion,'tipo')) ?>;
  const dataTipo   = <?= json_encode(array_column($comparacion,'total_presentes')) ?>;

  const isDark = document.documentElement.classList.contains("dark");

  const color = isDark ? "#17e1fc" : "#007bff";
  const textColor = isDark ? "#ffffff" : "#000000";

  /* ===== GRAFICA BARRAS ===== */
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

  /* ===== GRAFICA PIE ===== */
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

});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>