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

<!-- =========================================================
     CONTENEDOR PARTICIPANTES
========================================================= -->

<div class="attendance-section">

  <!-- =====================================================
     HEADER PARTICIPANTES
====================================================== -->

<div class="attendance-section-header">

    <h3>
        Participantes
    </h3>

    <div class="attendance-head-actions">

        <!-- ASISTENCIA -->
        <span title="Asistencia">
            ✓
        </span>

        <?php if ($reunion["tipo"] === "Discipulado"): ?>

            <!-- PRIMERA VEZ EN DISCIPULADO -->
            <span title="Primera vez en discipulado">
                1V
            </span>

        <?php endif; ?>

    </div>

</div>


<!-- =====================================================
     LISTA DE PARTICIPANTES
====================================================== -->

<div class="lista">

    <?php foreach ($jovenes as $j): ?>

        <?php

        $grupoEdad =
            (
                (int)($j["edad"] ?? 0) >= 15
                &&
                (int)($j["edad"] ?? 0) <= 17
            )
            ? "teen"
            : "remanente";

        $jovenId = (int)$j["id"];

        ?>

        <div
            class="attendance-card"
            data-edad="<?= htmlspecialchars($grupoEdad) ?>"
        >

            <!-- INFORMACIÓN DEL JOVEN -->

            <div class="info">

                <strong>

                    <?= htmlspecialchars(
                        $j["nombre_completo"] ?? ""
                    ) ?>

                </strong>

                <small>

                    <?= ucfirst($grupoEdad) ?>

                    ·

                    <?= ucfirst(
                        strtolower(
                            $j["estado_actividad"] ?? ""
                        )
                    ) ?>

                </small>

            </div>


            <!-- =================================================
                 CHECKS
            ================================================== -->

            <div class="checks-grid">

                <!-- ASISTENCIA -->

                <label
                    title="Asistencia"
                    class="attendance-check"
                >

                    <input
                        type="checkbox"
                        name="asistencia[<?= $jovenId ?>]"
                        value="1"
                    >

                    <span>
                        ✓
                    </span>

                </label>


                <?php if ($reunion["tipo"] === "Discipulado"): ?>

                    <!-- =========================================
                         PRIMERA VEZ EN DISCIPULADO
                    ========================================== -->

                    <label
                        title="Primera vez en discipulado"
                        class="attendance-check"
                    >

                        <input
                            type="checkbox"
                            name="primera_vez[<?= $jovenId ?>]"
                            value="1"
                        >

                        <span>
                            1V
                        </span>

                    </label>

                <?php endif; ?>

            </div>


            <!-- GRUPO DE EDAD -->

            <input
                type="hidden"
                name="grupo_edad[<?= $jovenId ?>]"
                value="<?= htmlspecialchars($grupoEdad) ?>"
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