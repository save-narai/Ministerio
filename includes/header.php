<?php require_once __DIR__ . '/../config/conexion.php'; ?>
<?php $config = require __DIR__ . '/../config/app.php'; ?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title><?= htmlspecialchars($config['nombre']) ?></title>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/theme.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<script>
(function() {
  const theme = localStorage.getItem("theme");
  if (theme === "dark") {
    document.documentElement.classList.add("dark");
  }
})();
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<?php if(isset($extraCSS)) echo $extraCSS; ?>

<script defer src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<link rel="stylesheet"
href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

<script defer
src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script defer src="<?= BASE_URL ?>/assets/js/theme.js"></script>
</head>

<body>

<button id="themeToggle">🌙</button>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">

  <a href="<?= BASE_URL ?>/views/dashboard.php">
    <i class="fa-solid fa-house"></i>
    <span><?= $config['textos']['dashboard'] ?></span>
  </a>

  <a href="<?= BASE_URL ?>/views/jovenes/index.php">
    <i class="fa-solid fa-users"></i>
    <span>Jóvenes</span>
  </a>

  <a href="<?= BASE_URL ?>/views/reuniones/index.php">
    <i class="fa-solid fa-calendar"></i>
    <span>Reuniones</span>
  </a>

  <a href="<?= BASE_URL ?>/views/seguimientos/index.php">
    <i class="fa-solid fa-notes-medical"></i>
    <span>Seguimientos</span>
  </a>

  <a href="<?= BASE_URL ?>/views/roles/index.php">
    <i class="fa-solid fa-gear"></i>
    <span>Roles</span>
  </a>

  <a href="<?= BASE_URL ?>/logout.php">
    <i class="fa-solid fa-right-from-bracket"></i>
    <span>Salir</span>
  </a>

</div>

<div class="main" id="mainContent">