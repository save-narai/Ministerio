<?php
require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/permiso.php";

if (!tienePermiso('gestionar_jovenes')) {
    header("Location: ../dashboard.php");
    exit;
}
?>

<?php require_once __DIR__ . "/../../includes/header.php"; ?>

<div class="card">

    <h2>➕ Crear Joven</h2>

    <form action="<?= BASE_URL ?>/controllers/jovenController.php" method="POST">

        <label>Nombre Completo:</label>
        <input type="text" name="nombre_completo" required>

        <label>Fecha de Nacimiento:</label>
        <input type="date" name="fecha_nacimiento" required>

        <label>Teléfono:</label>
        <input type="text" name="telefono">

        <label>Fecha de Ingreso:</label>
        <input type="date"
               name="fecha_ingreso"
               value="<?= date('Y-m-d') ?>"
               required>

        <label>¿Es Servidor?</label>
        <select name="es_servidor">
            <option value="0">No</option>
            <option value="1">Sí</option>
        </select>

        <button type="submit" name="crear_joven">Guardar</button>

    </form>

    <br>
    <a href="<?= BASE_URL ?>/views/jovenes/index.php">⬅ Volver</a>

</div>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>