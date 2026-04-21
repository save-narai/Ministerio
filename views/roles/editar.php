<?php
require_once "../../middleware/auth.php";
require_once "../../middleware/permiso.php";
require_once "../../config/conexion.php";

if (!tienePermiso('gestionar_roles')) {
    die("No tienes permiso.");
}

$rol_id = isset($_GET["id"]) ? (int)$_GET["id"] : null;

if (!$rol_id) {
    header("Location: index.php");
    exit();
}

// Obtener rol
$stmt = $pdo->prepare("SELECT id, nombre FROM roles WHERE id = :id");
$stmt->execute(["id" => $rol_id]);
$rol = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$rol) {
    header("Location: index.php");
    exit();
}

// Obtener todos los permisos
$permisos = $pdo->query("SELECT id, nombre FROM permisos")
                ->fetchAll(PDO::FETCH_ASSOC);

// Obtener permisos actuales del rol
$stmt = $pdo->prepare("
    SELECT permiso_id FROM rol_permiso WHERE rol_id = :rol_id
");
$stmt->execute(["rol_id" => $rol_id]);
$permisosRol = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<h2>Editar Permisos: <?= htmlspecialchars($rol["nombre"]) ?></h2>

<form action="../../controllers/rolController.php" method="POST">

    <input type="hidden" name="rol_id" value="<?= (int)$rol_id ?>">

    <?php foreach($permisos as $permiso): ?>
        <label>
            <input type="checkbox"
                   name="permisos[]"
                   value="<?= (int)$permiso["id"] ?>"
                   <?= in_array($permiso["id"], $permisosRol) ? "checked" : "" ?>>
            <?= htmlspecialchars($permiso["nombre"]) ?>
        </label>
        <br>
    <?php endforeach; ?>

    <br>
    <button type="submit" name="guardar_permisos">
        Guardar Cambios
    </button>

</form>

<br>
<a href="index.php">Volver</a>
