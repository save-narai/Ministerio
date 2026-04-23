<?php
require_once __DIR__ . '/../config/conexion.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Sistema</title>
<div class="top-actions flex justify-between items-center">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/theme.css">

<!-- Aplicar tema SIN parpadeo -->
<script>
if(localStorage.getItem("theme") === "dark"){
  document.documentElement.classList.add("dark");
}
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>

<body>

<!-- BOTÓN DARK MODE -->
<button id="themeToggle">🌙</button>

<!-- SIDEBAR -->
<div class="sidebar">
  <a href="<?= BASE_URL ?>/views/dashboard.php">🏠 <span>Dashboard</span></a>
  <a href="<?= BASE_URL ?>/views/jovenes/index.php">👤 <span>Jóvenes</span></a>
  <a href="<?= BASE_URL ?>/views/reuniones/index.php">📅 <span>Reuniones</span></a>
  <a href="<?= BASE_URL ?>/views/seguimientos/index.php">📝 <span>Seguimientos</span></a>
  <a href="<?= BASE_URL ?>/views/roles/index.php">⚙️ <span>Roles</span></a>
  <a href="<?= BASE_URL ?>/logout.php">🚪 <span>Salir</span></a>
</div>

<!-- CONTENIDO -->
<div class="main">
