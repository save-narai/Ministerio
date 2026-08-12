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

$stmt->execute([
    $reunion_id
]);

$reunion = $stmt->fetch(
    PDO::FETCH_ASSOC
);


if (!$reunion) {

    die("No existe");

}


/* =========================================================
   TIPO BONITO
========================================================= */

$tipoBonito = match ($reunion["tipo"]) {

    "REUNION_JOVENES" =>
        "Reunión Jóvenes",

    "GRUPO_CONEXION" =>
        "Grupo Conexión",

    "DISCIPULADO" =>
        "Discipulado",

    "EVENTO_ESPECIAL" =>
        "Evento Especial",

    default =>
        $reunion["tipo"]

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


/* =========================================================
   CARGAR ASISTENCIA EXISTENTE DE ESTA REUNIÓN
========================================================= */

$stmt = $pdo->prepare("

    SELECT

        joven_id,

        asistio,

        primera_vez

    FROM asistencia

    WHERE reunion_id = :reunion_id

");

$stmt->execute([

    ':reunion_id' =>
        $reunion_id

]);


$asistenciaExistente = [];


foreach (
    $stmt->fetchAll(PDO::FETCH_ASSOC)
    as $registro
) {

    $jovenId =
        (int)$registro['joven_id'];


    $asistenciaExistente[$jovenId] = [

        'asistio' =>
            (int)(
                $registro['asistio']
                ?? 0
            ),

        'primera_vez' =>
            (int)(
                $registro['primera_vez']
                ?? 0
            )

    ];

}


/* =========================================================
   INYECTAR DATOS EXISTENTES EN CADA JOVEN
========================================================= */

foreach ($jovenes as &$j) {

    $jovenId =
        (int)$j['id'];


    $j['asistencia_actual'] =

        $asistenciaExistente[$jovenId]['asistio']
        ?? 0;


    $j['primera_vez_actual'] =

        $asistenciaExistente[$jovenId]['primera_vez']
        ?? 0;

}


unset($j);


/* =========================================================
   HEADER
========================================================= */

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

        <!-- PRIMERA VEZ EN ESTA REUNIÓN -->
        <span title="Primera vez en esta reunión">
            1V
        </span>

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
        <?= (
            (int)($j["asistencia_actual"] ?? 0) === 1
        ) ? 'checked' : '' ?>
    >

    <span>
        ✓
    </span>

</label>


<!-- PRIMERA VEZ -->

<label
    title="Primera vez en esta reunión"
    class="attendance-check"
>

    <input
        type="checkbox"
        name="primera_vez[<?= $jovenId ?>]"
        value="1"
        <?= (
            (int)($j["primera_vez_actual"] ?? 0) === 1
        ) ? 'checked' : '' ?>
    >

    <span>
        1V
    </span>

</label>

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