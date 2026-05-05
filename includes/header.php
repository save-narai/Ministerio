<?php require_once __DIR__ . '/../config/conexion.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Sistema</title>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/theme.css">

<script>
(function() {
  const theme = localStorage.getItem("theme");

  if (theme === "dark") {
    document.documentElement.classList.add("dark");
  }
})();
</script>

<!-- CHART -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<?php
if(isset($extraCSS)) echo $extraCSS;
?>

<!-- jQuery -->
<script defer src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- DataTables -->
<link rel="stylesheet"
href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

<script defer
src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<!-- THEME -->
<script defer src="<?= BASE_URL ?>/assets/js/theme.js"></script>

</head>

<body>

<button id="themeToggle">🌙</button>

<div class="sidebar" id="sidebar">
  <a href="<?= BASE_URL ?>/views/dashboard.php">🏠 <span>Dashboard</span></a>
  <a href="<?= BASE_URL ?>/views/jovenes/index.php">👤 <span>Jóvenes</span></a>
  <a href="<?= BASE_URL ?>/views/reuniones/index.php">📅 <span>Reuniones</span></a>
  <a href="<?= BASE_URL ?>/views/seguimientos/index.php">📝 <span>Seguimientos</span></a>
  <a href="<?= BASE_URL ?>/views/roles/index.php">⚙️ <span>Roles</span></a>
  <a href="<?= BASE_URL ?>/logout.php">🚪 <span>Salir</span></a>
</div>

<div class="main" id="mainContent">