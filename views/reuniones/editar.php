<?php
require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/permiso.php";
require_once __DIR__ . "/../../config/conexion.php";

if (!tienePermiso('gestionar_reuniones')) {
    header("Location: ../dashboard.php");
    exit;
}

if (!isset($_GET["id"])) {
    die("Reunión no encontrada");
}

$id = (int)$_GET["id"];

$stmt = $pdo->prepare("SELECT * FROM reuniones WHERE id = ?");
$stmt->execute([$id]);

$reunion = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reunion) {
    die("Reunión no encontrada");
}

/* =========================
   CSS
========================= */

$extraCSS = '
<link rel="stylesheet" href="' . BASE_URL . '/assets/css/modules/reuniones/reuniones.css">
';

require_once __DIR__ . "/../../includes/header.php";
?>

<div class="reuniones">

    <div class="form-card">

        <h1 class="form-title">
            Editar reunión
        </h1>

        <form
            method="POST"
            action="<?= BASE_URL ?>/controllers/reunionController.php"
        >

            <input
                type="hidden"
                name="id"
                value="<?= $reunion["id"] ?>"
            >

            <!-- FECHA -->
            <div class="form-group">

                <label>Fecha</label>

                <input
                    type="date"
                    name="fecha"
                    value="<?= htmlspecialchars($reunion["fecha"]) ?>"
                    required
                >

            </div>

            <!-- TIPO -->
            <div class="form-group">

                <label>Tipo de reunión</label>

                <select name="tipo" required>

                    <option
                        value="REUNION_JOVENES"
                        <?= $reunion["tipo"] === "REUNION_JOVENES" ? "selected" : "" ?>
                    >
                        Reunión Jóvenes
                    </option>

                    <option
                        value="GRUPO_CONEXION"
                        <?= $reunion["tipo"] === "GRUPO_CONEXION" ? "selected" : "" ?>
                    >
                        Grupo Conexión
                    </option>

                    <option
                        value="DISCIPULADO"
                        <?= $reunion["tipo"] === "DISCIPULADO" ? "selected" : "" ?>
                    >
                        Discipulado
                    </option>

                    <option
                        value="EVENTO_ESPECIAL"
                        <?= $reunion["tipo"] === "EVENTO_ESPECIAL" ? "selected" : "" ?>
                    >
                        Evento Especial
                    </option>

                </select>

            </div>

            <!-- BOTONES -->
            <div class="form-actions">

                <button
                    type="submit"
                    name="actualizar"
                    class="btn-primary"
                >
                    Guardar cambios
                </button>

                <a href="index.php" class="btn-secondary">
                    Cancelar
                </a>

            </div>

        </form>

    </div>

</div>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>