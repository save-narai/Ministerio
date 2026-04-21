<?php
require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/permiso.php";
require_once __DIR__ . "/../../config/conexion.php";

// Solo ADMIN puede entrar
if ($_SESSION["rol"] !== "ADMIN") {
    header("Location: ../dashboard.php");
    exit();
}
?>

<?php include("../../includes/header.php"); ?>

<div class="main">

<h2>🔐 Panel ADMIN</h2>

<div class="grid">

    <div class="card">
        <h3>Usuarios</h3>
        <p>Administrar accesos al sistema</p>
        <a href="../usuarios/index.php" class="btn-report">Entrar</a>
    </div>

    <div class="card">
        <h3>Roles</h3>
        <p>Configurar permisos</p>
        <a href="../roles/index.php" class="btn-report">Entrar</a>
    </div>

    <div class="card">
        <h3>Dashboard</h3>
        <p>Volver al panel principal</p>
        <a href="../dashboard.php" class="btn-report">Volver</a>
    </div>

</div>

</div>

<?php include("../../includes/footer.php"); ?>