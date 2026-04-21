<?php
require_once "../../middleware/auth.php";
require_once "../../middleware/permiso.php";
require_once "../../config/conexion.php";

if (!tienePermiso('gestionar_roles')) {
    die("No tienes permiso.");
}

$roles = $pdo->query("SELECT id, nombre FROM roles")->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Gestión de Roles</h2>

<table border="1" cellpadding="8">
    <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Acción</th>
    </tr>

    <?php foreach($roles as $rol): ?>
        <tr>
            <td><?= (int)$rol["id"] ?></td>
            <td><?= htmlspecialchars($rol["nombre"]) ?></td>
            <td>
                <a href="editar.php?id=<?= (int)$rol["id"] ?>">
                    Editar Permisos
                </a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<br>
<a href="../dashboard.php">Volver</a>
