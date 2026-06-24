<?php

require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/permiso.php";
require_once __DIR__ . "/../../config/conexion.php";

if (!tienePermiso('gestionar_usuarios')) {

    header("Location: ../dashboard.php");
    exit;
}

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT
        id,
        nombre
    FROM usuarios
    WHERE id = ?
");

$stmt->execute([$id]);

$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {

    header("Location: index.php");
    exit;
}

require_once __DIR__ . "/../../includes/header.php";

?>

<div class="form-card">

    <div class="form-header">

        <div class="form-header-icon">

            <i class="fa-solid fa-key"></i>

        </div>

        <div class="form-header-content">

            <h1 class="form-title">
                Cambiar Contraseña
            </h1>

            <p class="form-subtitle">

                Actualiza la contraseña del usuario
                <strong>
                    <?= htmlspecialchars($usuario['nombre']) ?>
                </strong>

            </p>

        </div>

    </div>

    <form
        class="form"
        action="<?= BASE_URL ?>/controllers/usuarioController.php"
        method="POST"
    >

        <input
            type="hidden"
            name="id"
            value="<?= $usuario['id'] ?>"
        >

        <div class="form-group">

            <label class="form-label">

                Nueva Contraseña

            </label>

            <input
                class="form-input"
                type="password"
                name="password"
                minlength="6"
                required
            >

        </div>

        <div class="form-group">

            <label class="form-label">

                Confirmar Contraseña

            </label>

            <input
                class="form-input"
                type="password"
                name="confirmar_password"
                minlength="6"
                required
            >

        </div>

        <div class="form-actions">

            <a
                href="index.php"
                class="btn btn-back"
            >

                <i class="fa-solid fa-arrow-left"></i>

                Volver

            </a>

            <button
                type="submit"
                name="cambiar_password"
                class="btn btn-primary"
            >

                Guardar Contraseña

            </button>

        </div>

    </form>

</div>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>