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

$stmt = $pdo->prepare("
    SELECT *
    FROM reuniones
    WHERE id = ?
");

$stmt->execute([$id]);

$reunion = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reunion) {
    die("Reunión no encontrada");
}

require_once __DIR__ . "/../../includes/header.php";
?>

<div class="form-card">

    <div class="form-header">

        <div class="form-header-icon">

            <i class="fa-solid fa-calendar-days"></i>

        </div>

        <div class="form-header-content">

            <h1 class="form-title">
                Editar reunión
            </h1>

            <p class="form-subtitle">
                Actualiza la información de esta reunión o evento.
            </p>

        </div>

    </div>

    <form
        class="form"
        method="POST"
        action="<?= BASE_URL ?>/controllers/reunionController.php"
    >

        <input
            type="hidden"
            name="id"
            value="<?= $reunion["id"] ?>"
        >

        <div class="form-grid">

            <!-- FECHA -->

            <div class="form-group">

                <label class="form-label">
                    Fecha
                </label>

                <input
                    class="form-input"
                    type="date"
                    name="fecha"
                    value="<?= htmlspecialchars($reunion["fecha"]) ?>"
                    required
                >

            </div>

            <!-- TIPO -->

            <div class="form-group">

                <label class="form-label">
                    Tipo de reunión
                </label>

                <select
                    class="form-select"
                    name="tipo"
                    required
                >

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
                name="actualizar"
                class="btn btn-primary"
            >
                Guardar cambios
            </button>

        </div>

    </form>

</div>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>