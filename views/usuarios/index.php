<?php
require_once "../../middleware/auth.php";
require_once "../../middleware/permiso.php";
require_once "../../config/conexion.php";

if (!tienePermiso('gestionar_usuarios')) {
    header("Location: ../dashboard.php");
    exit;
}

$stmt = $pdo->query("
    SELECT u.*, r.nombre as rol
    FROM usuarios u
    LEFT JOIN roles r ON u.rol_id = r.id
");

$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include("../../includes/header.php"); ?>

<div class="card">

    <h2>👥 Gestión de Usuarios</h2>

    <div style="margin-bottom:15px;">
        <a href="../dashboard.php" class="btn-back">⬅ Volver</a>
        <a href="crear.php" class="btn-primary">➕ Crear Usuario</a>
    </div>

    <table class="tabla-pro">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Usuario</th>
                <th>Rol</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>

        <tbody>
        <?php foreach($usuarios as $u): ?>
        <tr>
            <td><?= (int)$u["id"] ?></td>
            <td><?= htmlspecialchars($u["nombre"]) ?></td>
            <td><?= htmlspecialchars($u["usuario"]) ?></td>
            <td><?= htmlspecialchars($u["rol"] ?? "Sin rol") ?></td>

            <td>
                <?php if($u["activo"]): ?>
                    <span class="badge activo">🟢 Activo</span>
                <?php else: ?>
                    <span class="badge inactivo">🔴 Inactivo</span>
                <?php endif; ?>
            </td>

            <td>
                <a href="editar.php?id=<?= (int)$u["id"] ?>" class="btn-edit">✏️</a>

                <?php if ($_SESSION["user_id"] != $u["id"]): ?>
                    <a href="../../controllers/toggleUsuario.php?id=<?= (int)$u["id"] ?>" class="btn-toggle">
                        <?= $u["activo"] ? "Off" : "On" ?>
                    </a>
                <?php else: ?>
                    <span class="lock">🔒</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

</div>

<?php include("../../includes/footer.php"); ?>