<?php
$data = require __DIR__ . '/../controllers/dashboardController.php';
extract($data);

include __DIR__ . '/../includes/header.php';
?>

<h2>Bienvenido, <?= $_SESSION['usuario_nombre'] ?? 'Usuario' ?></h2>
<p class="text-center text-muted">
  Rol: <?= $_SESSION['rol'] ?? 'N/A' ?>
</p>

<hr>

<h3>🟩 Resumen</h3>

<div class="cards">

  <?php
  $cards = [
    ["Total", $totalJovenes],
    ["Activos", $activos],
    ["Inactivos", $inactivos],
    ["Servidores", $totalServidores],
    ["Reuniones", $totalReuniones],
    ["% Asistencia", $porcentajeGeneral . "%"]
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

  new Chart(document.getElementById('graficaMensual'), {
    type: 'bar',
    data: {
      labels: labelsMes,
      datasets: [{
        data: dataMes,
        backgroundColor: color
      }]
    }
  });

  new Chart(document.getElementById('graficaTipos'), {
    type: 'pie',
    data: {
      labels: labelsTipo,
      datasets: [{
        data: dataTipo
      }]
    }
  });

});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>