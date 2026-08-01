<?php

require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/permiso.php";
require_once __DIR__ . "/../../config/conexion.php";
require_once __DIR__ . "/../../services/reunionService.php";

if (!tienePermiso('gestionar_reuniones')) {

    header("Location: ../dashboard.php");
    exit;

}

if (!isset($_GET["id"])) {

    die("Reunión no encontrada");

}

$id = (int) $_GET["id"];

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

$esPersonalizado = !in_array(
    $reunion["tipo"],
    [
        "Reunión Jóvenes",
        "Grupo Conexión",
        "Discipulado",
        "Evento Especial"
    ],
    true
);

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
                Actualiza la información de esta reunión.
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
            name="action"
            value="actualizar_reunion"
        >

        <input
            type="hidden"
            name="id"
            value="<?= $reunion["id"] ?>"
        >

        <input
            type="hidden"
            name="csrf_token"
            value="<?= $_SESSION['csrf_token'] ?>"
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
                    id="tipo"
                    required
                >

                    <option
                        value="REUNION_JOVENES"
                        <?= $reunion["tipo"] === "Reunión Jóvenes" ? "selected" : "" ?>
                    >
                        Reunión Jóvenes
                    </option>

                    <option
                        value="GRUPO_CONEXION"
                        <?= $reunion["tipo"] === "Grupo Conexión" ? "selected" : "" ?>
                    >
                        Grupo Conexión
                    </option>

                    <option
                        value="DISCIPULADO"
                        <?= $reunion["tipo"] === "Discipulado" ? "selected" : "" ?>
                    >
                        Discipulado
                    </option>

                    <option
                        value="EVENTO_ESPECIAL"
                        <?= $reunion["tipo"] === "Evento Especial" ? "selected" : "" ?>
                    >
                        Evento Especial
                    </option>

                    <option
                        value="OTRO"
                        <?= $esPersonalizado ? "selected" : "" ?>
                    >
                        Otro...
                    </option>

                </select>

            </div>

            <!-- NOMBRE DEL EVENTO -->

            <div
                class="form-group"
                id="grupoTipoPersonalizado"
                style="<?= $esPersonalizado ? '' : 'display:none;' ?>"
            >

                <label class="form-label">
                    Nombre del evento
                </label>

                <input
                    class="form-input"
                    type="text"
                    name="tipo_personalizado"
                    id="tipoPersonalizado"
                    value="<?= $esPersonalizado ? htmlspecialchars($reunion["tipo"]) : "" ?>"
                    placeholder="Ej: Campamento Juvenil"
                >

            </div>

        </div>

                <div class="form-actions">

            <a
                href="index.php"
                class="btn btn-back"
            >

            
                Volver

            </a>

            <button
                type="submit"
                class="btn btn-primary"
            >

                Guardar cambios

            </button>

        </div>

    </form>

</div>

<script src="<?= BASE_URL ?>/assets/js/modulos/reuniones/editar.js"></script>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>