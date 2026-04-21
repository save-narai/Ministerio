<?php
require_once "../../middleware/auth.php";
require_once "../../middleware/permiso.php";
require_once "../../config/conexion.php";

if (!tienePermiso('gestionar_usuarios')) {
    die("No tienes permiso.");
}

$id = isset($_GET["id"]) ? (int)$_GET["id"] : null;

if (!$id) {
    header("Location: index.php");
    exit();
}

// Obtener usuario
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id");
$stmt->execute(["id" => $id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    header("Location: index.php");
    exit();
}

// Obtener roles
$roles = $pdo->query("SELECT id, nombre FROM roles")->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include("../../includes/header.php"); ?>

<div class="card">

    <h2>✏️ Editar Usuario</h2>

    <form action="../../controllers/usuarioController.php" method="POST" class="form-pro">

        <input type="hidden" name="id" value="<?= (int)$usuario["id"] ?>">

        <label>Nombre</label>
        <input type="text"
               name="nombre"
               value="<?= htmlspecialchars($usuario["nombre"]) ?>"
               required>

        <label>Usuario</label>
        <input type="text"
               name="usuario"
               value="<?= htmlspecialchars($usuario["usuario"]) ?>"
               required>

        <label>Rol</label>
        <select name="rol_id" required>
            <?php foreach($roles as $rol): ?>
                <option value="<?= (int)$rol["id"] ?>"
                    <?= $rol["id"] == $usuario["rol_id"] ? "selected" : "" ?>>
                    <?= htmlspecialchars($rol["nombre"]) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit" name="editar_usuario" class="btn-primary">
            Guardar Cambios
        </button>

    </form>

    <br>
    <a href="index.php" class="btn-back">⬅ Volver</a>

</div>

<?php include("../../includes/footer.php"); ?>