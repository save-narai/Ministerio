<?php

require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/permiso.php";
require_once __DIR__ . "/../../config/conexion.php";

if (!tienePermiso('gestionar_reuniones')) {
    header("Location: ../dashboard.php");
    exit;
}

$reunion_id = $_GET["reunion_id"] ?? null;

if (!$reunion_id) {
    die("Reunión inválida");
}

/* =========================================================
   REUNIÓN
========================================================= */

$stmt = $pdo->prepare("
    SELECT *
    FROM reuniones
    WHERE id = ?
");

$stmt->execute([$reunion_id]);

$reunion = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reunion) {
    die("No existe");
}

/* =========================================================
   TIPO BONITO
========================================================= */

$tipoBonito = match ($reunion["tipo"]) {

    "REUNION_JOVENES" => "Reunión Jóvenes",
    "GRUPO_CONEXION"  => "Grupo Conexión",
    "DISCIPULADO"     => "Discipulado",
    "EVENTO_ESPECIAL" => "Evento Especial",

    default => $reunion["tipo"]
};

/* =========================================================
   JÓVENES
========================================================= */

$jovenes = $pdo->query("
    SELECT
        id,
        nombre_completo,
        TIMESTAMPDIFF(
            YEAR,
            fecha_nacimiento,
            CURDATE()
        ) AS edad,
        estado_actividad
    FROM jovenes
    WHERE estado_actividad IN (
        'ACTIVO',
        'INACTIVO'
    )
    ORDER BY nombre_completo ASC
")->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . "/../../includes/header.php";

?>

<div class="marcar">

    <!-- HEADER -->

    <div class="page-header">

        <div class="page-header-left">

            <h1 class="page-title">
                Marcar Asistencia
            </h1>

            <div class="page-subtitle">

                <?= $tipoBonito ?>

                ·

                <?= date(
                    "d/m/Y",
                    strtotime($reunion["fecha"])
                ) ?>

            </div>

        </div>

        <div class="page-header-right">

            <div class="attendance-total">

                <i class="fa-solid fa-users"></i>

                <span>
                    <?= count($jovenes) ?> participantes
                </span>

            </div>

        </div>

    </div>

 <form
    method="POST"
    action="<?= BASE_URL ?>/controllers/asistenciaController.php"
>

    <input
        type="hidden"
        name="action"
        value="guardar_asistencia"
    >

    <input
        type="hidden"
        name="reunion_id"
        value="<?= $reunion_id ?>"
    >

    <input
        type="hidden"
        name="csrf_token"
        value="<?= $_SESSION['csrf_token'] ?>"
    >

    <!-- HERRAMIENTAS -->

    <div class="marcar-toolbar">

        <!-- FILTROS -->

        <div class="filters-bar marcar-filters">

            <button
                type="button"
                class="filter-chip filter-chip--active"
                data-filter="todos"
            >
                Todos
            </button>

            <button
                type="button"
                class="filter-chip"
                data-filter="teen"
            >
                Teenagers
            </button>

            <button
                type="button"
                class="filter-chip"
                data-filter="remanente"
            >
                Remanente
            </button>

        </div>

        <!-- BUSCADOR -->

        <div class="search-bar marcar-search">

            <input
                type="text"
                id="buscadorJovenes"
                class="search-input"
                placeholder="Buscar joven..."
            >

        </div>

        <!-- BOTONES -->

        <div class="btn-group marcar-buttons">

            <button
                type="button"
                id="checkAll"
                class="btn btn-success"
            >
                Marcar todos
            </button>

            <button
                type="button"
                id="uncheckAll"
                class="btn btn-secondary"
            >
                Limpiar selección
            </button>

        </div>

    </div>

      <!-- PARTICIPANTES -->

<div class="attendance-section">

    <div class="attendance-section-header">

        <h3>
            Participantes
        </h3>

        <div class="attendance-head-actions">

            <span title="Asistencia">
                ✓
            </span>

            <span title="Conexión">
                Cx
            </span>

            <span title="Discipulado">
                Dp
            </span>

            <span title="Primera vez">
                1V
            </span>

        </div>

    </div>

    <div class="lista">

        <?php foreach ($jovenes as $j):

            $grupoEdad =
                ($j["edad"] >= 15 && $j["edad"] <= 17)
                ? "teen"
                : "remanente";

        ?>

            <div
                class="attendance-card"
                data-edad="<?= $grupoEdad ?>"
            >

                <div class="info">

                    <strong>
                        <?= htmlspecialchars($j["nombre_completo"]) ?>
                    </strong>

                    <small>

                        <?= ucfirst($grupoEdad) ?>

                        ·

                        <?= ucfirst(
                            strtolower(
                                $j["estado_actividad"]
                            )
                        ) ?>

                    </small>

                </div>

                <div class="checks-grid">

                    <label title="Asistencia">

                        <input
                            type="checkbox"
                            name="asistencia[]"
                            value="<?= $j["id"] ?>"
                        >

                        <span>✓</span>

                    </label>

                    <label title="Conexión">

                        <input
                            type="checkbox"
                            name="conexion[]"
                            value="<?= $j["id"] ?>"
                        >

                        <span>Cx</span>

                    </label>

                    <label title="Discipulado">

                        <input
                            type="checkbox"
                            name="discipulado[]"
                            value="<?= $j["id"] ?>"
                        >

                        <span>Dp</span>

                    </label>

                    <label title="Primera vez">

                        <input
                            type="checkbox"
                            name="primera_vez[]"
                            value="<?= $j["id"] ?>"
                        >

                        <span>1V</span>

                    </label>

                </div>

                <input
                    type="hidden"
                    name="grupo_edad[<?= $j["id"] ?>]"
                    value="<?= $grupoEdad ?>"
                >

            </div>

        <?php endforeach; ?>

            </div>

        </div>

     <!-- BOTONES -->

<div class="form-actions">

    <button
        type="submit"
        class="btn btn-primary"
    >
        Guardar asistencia
    </button>

    <a
        href="index.php"
        class="btn btn-secondary"
    >
        Cancelar
    </a>

</div>

</form>

</div>

<script
    src="<?= BASE_URL ?>/assets/js/modulos/reuniones/marcar.js">
</script>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>