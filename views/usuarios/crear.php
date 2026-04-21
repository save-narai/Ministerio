<?php
require_once "../../middleware/auth.php";
require_once "../../middleware/permiso.php";
require_once "../../config/conexion.php";

if (!tienePermiso('gestionar_usuarios')) {
    die("No tienes permiso.");
}

// Obtener roles
$stmt = $pdo->query("SELECT id, nombre FROM roles");
$roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include("../../includes/header.php"); ?>

<div class="card">

    <h2>➕ Crear Usuario</h2>

    <form action="../../controllers/usuarioController.php" method="POST" class="form-pro">

        <label>Nombre</label>
        <input type="text" name="nombre" required>

        <label>Usuario</label>
        <input type="text" name="usuario" required autocomplete="off">

        <label>Contraseña</label>
        <input type="password" name="password" required autocomplete="off">

        <label>Rol</label>
        <select name="rol_id" required>
            <?php foreach($roles as $rol): ?>
                <option value="<?= (int)$rol["id"] ?>">
                    <?= htmlspecialchars($rol["nombre"]) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit" name="crear_usuario" class="btn-primary">
            Guardar Usuario
        </button>

    </form>

    <br>
    <a href="index.php" class="btn-back">⬅ Volver</a>

</div>

<?php include("../../includes/footer.php"); ?>