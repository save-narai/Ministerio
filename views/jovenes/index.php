<<<<<<< HEAD

=======
>>>>>>> 3e2d89c (Actualización del proyecto)
<?php

require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/permiso.php";
require_once __DIR__ . "/../../services/actividadService.php";
require_once __DIR__ . "/../../config/conexion.php";
require_once __DIR__ . "/../../helpers/csrf.php";
require_once __DIR__ . "/../../helpers/format.php";

/* =====================================
   CSRF
===================================== */

generarCsrf();

/* =====================================
   PERMISOS
===================================== */

<<<<<<< HEAD
if (!tienePermiso('gestionar_jovenes')) {
=======
if (!tienePermiso("gestionar_jovenes")) {
>>>>>>> 3e2d89c (Actualización del proyecto)

    header("Location: ../dashboard.php");
    exit;
}

/* =====================================
   ACTUALIZAR ACTIVIDAD
===================================== */

actualizarEstadoActividad($pdo);

/* =====================================
   FILTROS
===================================== */

$permitidos = [
    "todos",
    "activos",
    "inactivos",
    "eliminados",
    "riesgo2",
    "riesgo3"
];

$filtro = $_GET["filtro"] ?? "todos";

if (!in_array($filtro, $permitidos, true)) {
    $filtro = "todos";
}

/* =====================================
   CONSULTA
===================================== */

$query = "
SELECT
    j.id,
    j.nombre_completo,
    j.fecha_nacimiento,
    j.edad_manual,
    j.fecha_actualizacion_edad,
    j.estado_espiritual,
    j.estado_actividad,
    j.fecha_ingreso,

    COALESCE(
        SUM(
            CASE
                WHEN a.asistio = 0
                AND r.fecha >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                THEN 1
                ELSE 0
            END
        ),
        0
    ) AS faltas_recientes,

    MAX(
        CASE
            WHEN a.asistio = 1
            THEN r.fecha
        END
    ) AS ultima_asistencia,

    COALESCE(
        SUM(
            CASE
                WHEN a.asistio = 1
<<<<<<< HEAD
                AND MONTH(r.fecha) = MONTH(CURDATE())
                AND YEAR(r.fecha) = YEAR(CURDATE())
=======
                AND MONTH(r.fecha)=MONTH(CURDATE())
                AND YEAR(r.fecha)=YEAR(CURDATE())
>>>>>>> 3e2d89c (Actualización del proyecto)
                THEN 1
                ELSE 0
            END
        ),
        0
    ) AS asistencias_mes_actual,

    COALESCE(
        SUM(
            CASE
                WHEN a.asistio = 1
<<<<<<< HEAD
                AND MONTH(r.fecha) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
                AND YEAR(r.fecha) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
=======
                AND MONTH(r.fecha)=MONTH(DATE_SUB(CURDATE(),INTERVAL 1 MONTH))
                AND YEAR(r.fecha)=YEAR(DATE_SUB(CURDATE(),INTERVAL 1 MONTH))
>>>>>>> 3e2d89c (Actualización del proyecto)
                THEN 1
                ELSE 0
            END
        ),
        0
    ) AS asistencias_mes_1,

    COALESCE(
        SUM(
            CASE
                WHEN a.asistio = 1
<<<<<<< HEAD
                AND MONTH(r.fecha) = MONTH(DATE_SUB(CURDATE(), INTERVAL 2 MONTH))
                AND YEAR(r.fecha) = YEAR(DATE_SUB(CURDATE(), INTERVAL 2 MONTH))
=======
                AND MONTH(r.fecha)=MONTH(DATE_SUB(CURDATE(),INTERVAL 2 MONTH))
                AND YEAR(r.fecha)=YEAR(DATE_SUB(CURDATE(),INTERVAL 2 MONTH))
>>>>>>> 3e2d89c (Actualización del proyecto)
                THEN 1
                ELSE 0
            END
        ),
        0
    ) AS asistencias_mes_2

FROM jovenes j

LEFT JOIN asistencia a
    ON a.joven_id = j.id

LEFT JOIN reuniones r
    ON r.id = a.reunion_id
";

$where = [];

$where[] = "j.estado_actividad != 'ELIMINADO'";

if ($filtro === "activos") {
<<<<<<< HEAD
    $where[] = "j.estado_actividad = 'ACTIVO'";
}

if ($filtro === "inactivos") {
    $where[] = "j.estado_actividad = 'INACTIVO'";
}

if ($filtro === "eliminados") {
    $where = ["j.estado_actividad = 'ELIMINADO'"];
=======
    $where[] = "j.estado_actividad='ACTIVO'";
}

if ($filtro === "inactivos") {
    $where[] = "j.estado_actividad='INACTIVO'";
}

if ($filtro === "eliminados") {
    $where = ["j.estado_actividad='ELIMINADO'"];
>>>>>>> 3e2d89c (Actualización del proyecto)
}

if (!empty($where)) {
    $query .= " WHERE " . implode(" AND ", $where);
}

$query .= "
GROUP BY
    j.id,
    j.nombre_completo,
    j.fecha_nacimiento,
    j.edad_manual,
    j.fecha_actualizacion_edad,
    j.estado_espiritual,
    j.estado_actividad,
    j.fecha_ingreso
";

if ($filtro === "riesgo2") {

    $query .= "
    HAVING (
        asistencias_mes_actual <= 1
        OR faltas_recientes >= 3
    )
    AND NOT (
        asistencias_mes_1 <= 1
        AND asistencias_mes_2 <= 1
    )
    ";
}

if ($filtro === "riesgo3") {

    $query .= "
    HAVING (
        asistencias_mes_1 <= 1
        AND asistencias_mes_2 <= 1
    )
    ";
}

$query .= " ORDER BY j.nombre_completo ASC";

$stmt = $pdo->prepare($query);
$stmt->execute();

$jovenes = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =====================================
<<<<<<< HEAD
   HEADER
===================================== */

require_once __DIR__ . "/../../includes/header.php";

?>

<div class="page">

    <?php if (isset($_SESSION["success"])): ?>

        <script>
        document.addEventListener("DOMContentLoaded", () => {
            showToast(
                <?= json_encode($_SESSION["success"]); ?>,
                "success"
            );
        });
        </script>

        <?php unset($_SESSION["success"]); ?>

    <?php endif; ?>

    <?php if (isset($_SESSION["error"])): ?>

        <script>
        document.addEventListener("DOMContentLoaded", () => {
            showToast(
                <?= json_encode($_SESSION["error"]); ?>,
                "error"
            );
        });
        </script>

        <?php unset($_SESSION["error"]); ?>

    <?php endif; ?>

    <!-- =====================================
         PAGE HEADER
    ====================================== -->

    <div class="page-header">

        <div class="page-header-left">

            <h1 class="page-title">
                Gestión de Jóvenes
            </h1>

            <p class="page-subtitle">
                Administra registros, seguimiento y actividad juvenil.
            </p>

        </div>

        <div class="page-header-right">

            <a
                href="<?= BASE_URL ?>/views/jovenes/crear.php"
                class="btn btn-primary"
            >

                <i class="fa-solid fa-plus"></i>

                Nuevo

            </a>

            <div class="export-dropdown">

                <button
                    type="button"
                    class="export-dropdown__trigger"
                >

                    <i class="fa-solid fa-download"></i>

                    Exportar

                    <i class="fa-solid fa-chevron-down"></i>

                </button>

                <div class="export-dropdown__menu">

                    <button
                        type="button"
                        class="export-option"
                        id="exportPdf"
                    >

                        <i class="fa-solid fa-file-pdf"></i>

                        PDF

                    </button>

                    <button
                        type="button"
                        class="export-option"
                        id="exportExcel"
                    >

                        <i class="fa-solid fa-file-excel"></i>

                        Excel

                    </button>

                    <button
                        type="button"
                        class="export-option"
                        id="exportWord"
                    >

                        <i class="fa-solid fa-file-word"></i>

                        Word

                    </button>

                    <button
                        type="button"
                        class="export-option"
                        id="exportCsv"
                    >

                        <i class="fa-solid fa-file-csv"></i>

                        CSV

                    </button>

                    <button
                        type="button"
                        class="export-option"
                        id="exportPrint"
                    >

                        <i class="fa-solid fa-print"></i>

                        Imprimir

                    </button>

                </div>

            </div>

        </div>

    </div>                                                                                                                                          <?php

/* =====================================
=======
>>>>>>> 3e2d89c (Actualización del proyecto)
   ESTADÍSTICAS
===================================== */

$totalJovenes = count($jovenes);

$totalActivos = count(array_filter(
    $jovenes,
    fn($j) => $j["estado_actividad"] === "ACTIVO"
));

$totalInactivos = count(array_filter(
    $jovenes,
    fn($j) => $j["estado_actividad"] === "INACTIVO"
));

$totalRiesgo = 0;
$totalAltoRiesgo = 0;

foreach ($jovenes as $item) {

    $faltas = (int)$item["faltas_recientes"];

    $mes0 = (int)($item["asistencias_mes_actual"] ?? 0);
    $mes1 = (int)($item["asistencias_mes_1"] ?? 0);
    $mes2 = (int)($item["asistencias_mes_2"] ?? 0);

    if ($mes1 <= 1 && $mes2 <= 1) {

        $totalAltoRiesgo++;

    } elseif ($mes0 <= 1 || $faltas >= 3) {

        $totalRiesgo++;
    }
}

<<<<<<< HEAD
?>

<!-- =====================================
     TOOLBAR
===================================== -->

<div class="gx-toolbar">

    <div class="filters-bar">

        <a
            href="?filtro=todos"
            class="filter-chip filter-chip--default <?= $filtro === 'todos' ? 'filter-chip--active' : '' ?>"
        >
            Todos
        </a>

        <a
            href="?filtro=activos"
            class="filter-chip filter-chip--success <?= $filtro === 'activos' ? 'filter-chip--active' : '' ?>"
        >
            Activos
        </a>

        <a
            href="?filtro=inactivos"
            class="filter-chip filter-chip--danger <?= $filtro === 'inactivos' ? 'filter-chip--active' : '' ?>"
        >
            Inactivos
        </a>

        <a
            href="?filtro=eliminados"
            class="filter-chip filter-chip--danger <?= $filtro === 'eliminados' ? 'filter-chip--active' : '' ?>"
        >
            Eliminados
        </a>

        <a
            href="?filtro=riesgo2"
            class="filter-chip filter-chip--warning <?= $filtro === 'riesgo2' ? 'filter-chip--active' : '' ?>"
        >
            Riesgo
        </a>

        <a
            href="?filtro=riesgo3"
            class="filter-chip filter-chip--critical <?= $filtro === 'riesgo3' ? 'filter-chip--active' : '' ?>"
        >
            Alto Riesgo
        </a>

    </div>

    <div class="search-bar">

        <input
            type="text"
            id="buscador"
            class="search-input"
            placeholder="Buscar joven..."
            autocomplete="off"
        >
=======
require_once __DIR__ . "/../../includes/header.php";
?>

<div class="page">

<?php if (isset($_SESSION["success"])): ?>

<script>
document.addEventListener("DOMContentLoaded", () => {
    showToast(
        <?= json_encode($_SESSION["success"]); ?>,
        "success"
    );
});
</script>

<?php unset($_SESSION["success"]); ?>

<?php endif; ?>

<?php if (isset($_SESSION["error"])): ?>

<script>
document.addEventListener("DOMContentLoaded", () => {
    showToast(
        <?= json_encode($_SESSION["error"]); ?>,
        "error"
    );
});
</script>

<?php unset($_SESSION["error"]); ?>

<?php endif; ?>




<div class="page-header">

    <div class="page-header-left">

        <h1 class="page-title">
            Gestión de Jóvenes
        </h1>

        <p class="page-subtitle">
            Administra registros, seguimiento y actividad juvenil.
        </p>

    </div>

    <div class="page-header-right">

        <a
            href="<?= BASE_URL ?>/views/jovenes/crear.php"
            class="btn btn-primary"
        >
            <i class="fa-solid fa-plus"></i>
            Nuevo
        </a>

        <div class="export-dropdown">

            <button
                type="button"
                class="export-dropdown__trigger"
            >
                <i class="fa-solid fa-download"></i>
                Exportar
                <i class="fa-solid fa-chevron-down"></i>
            </button>

            <div class="export-dropdown__menu">

                <button id="exportPdf" class="export-option">
                    <i class="fa-solid fa-file-pdf"></i> PDF
                </button>

                <button id="exportExcel" class="export-option">
                    <i class="fa-solid fa-file-excel"></i> Excel
                </button>

                <button id="exportWord" class="export-option">
                    <i class="fa-solid fa-file-word"></i> Word
                </button>

                <button id="exportCsv" class="export-option">
                    <i class="fa-solid fa-file-csv"></i> CSV
                </button>

                <button id="exportPrint" class="export-option">
                    <i class="fa-solid fa-print"></i> Imprimir
                </button>

            </div>

        </div>
>>>>>>> 3e2d89c (Actualización del proyecto)

    </div>

</div>

<<<<<<< HEAD
=======


    <div class="gx-toolbar">

        <div class="filters-bar">

            <a href="?filtro=todos"
               class="filter-chip filter-chip--default <?= $filtro === "todos" ? "filter-chip--active" : "" ?>">
                Todos
            </a>

            <a href="?filtro=activos"
               class="filter-chip filter-chip--success <?= $filtro === "activos" ? "filter-chip--active" : "" ?>">
                Activos
            </a>

            <a href="?filtro=inactivos"
               class="filter-chip filter-chip--danger <?= $filtro === "inactivos" ? "filter-chip--active" : "" ?>">
                Inactivos
            </a>

            <a href="?filtro=eliminados"
               class="filter-chip filter-chip--danger <?= $filtro === "eliminados" ? "filter-chip--active" : "" ?>">
                Eliminados
            </a>

            <a href="?filtro=riesgo2"
               class="filter-chip filter-chip--warning <?= $filtro === "riesgo2" ? "filter-chip--active" : "" ?>">
                Riesgo
            </a>

            <a href="?filtro=riesgo3"
               class="filter-chip filter-chip--critical <?= $filtro === "riesgo3" ? "filter-chip--active" : "" ?>">
                Alto Riesgo
            </a>

        </div>

        <div class="search-bar">

            <input
                type="text"
                id="buscador"
                class="search-input"
                placeholder="Buscar joven..."
                autocomplete="off"
            >

        </div>

    </div>

</div>


>>>>>>> 3e2d89c (Actualización del proyecto)
<!-- =====================================
     TABLA
===================================== -->

<div class="page-section">

<<<<<<< HEAD
    <div class="table-container">
=======
    <div class="table-wrapper">
>>>>>>> 3e2d89c (Actualización del proyecto)

        <table
            id="tablaJovenes"
            class="table gx-table"
<<<<<<< HEAD
=======
            style="width:100%"
>>>>>>> 3e2d89c (Actualización del proyecto)
        >

            <thead>

                <tr>

                    <th>Nombre</th>
<<<<<<< HEAD
                    <th>Edad</th>
                    <th>Estado</th>
                    <th>Actividad</th>
                    <th>Conexión</th>
                    <th>Tiempo</th>
                    <th>Seguimiento</th>
=======

                    <th>Edad</th>

                    <th>Estado Espiritual</th>

                    <th>Actividad</th>

                    <th>Última Asistencia</th>

                    <th>Riesgo</th>

                    <th>Ingreso</th>

>>>>>>> 3e2d89c (Actualización del proyecto)
                    <th>Acciones</th>

                </tr>

            </thead>

            <tbody>

<<<<<<< HEAD
            <?php if (empty($jovenes)): ?>

                <tr>

                    <td colspan="8" class="text-center">

                        No existen jóvenes registrados.

                    </td>

                </tr>

            <?php else: ?>

            <?php foreach ($jovenes as $j): ?>

            <?php

            $edad = null;
            $edadAprox = false;

            if (!empty($j["fecha_nacimiento"])) {

                $edad = (
                    new DateTime($j["fecha_nacimiento"])
                )->diff(new DateTime())->y;

            } elseif (!empty($j["edad_manual"])) {

                $edad = (int)$j["edad_manual"];

                if (!empty($j["fecha_actualizacion_edad"])) {

                    $edad += (
                        new DateTime($j["fecha_actualizacion_edad"])
                    )->diff(new DateTime())->y;
                }

                $edadAprox = true;
            }

            $meses = 0;
            $dias = 0;

            if (!empty($j["fecha_ingreso"])) {

                $diff = (
                    new DateTime($j["fecha_ingreso"])
                )->diff(new DateTime());

                $meses = ($diff->y * 12) + $diff->m;

                $dias = $diff->days;
            }

            $años = floor($meses / 12);
            $restoMeses = $meses % 12;

            $faltas = (int)$j["faltas_recientes"];

            $mes0 = (int)($j["asistencias_mes_actual"] ?? 0);
            $mes1 = (int)($j["asistencias_mes_1"] ?? 0);
            $mes2 = (int)($j["asistencias_mes_2"] ?? 0);

            $conexionReal = "ACTIVO";

            if ($mes1 <= 1 && $mes2 <= 1) {

                $conexionReal = "ALTO RIESGO";

            } elseif ($mes0 <= 1 || $faltas >= 3) {

                $conexionReal = "RIESGO";
            }

            ?>

            <tr>

                <td>

                    <?= e($j["nombre_completo"]) ?>

                </td>

                <td data-order="<?= $edad ?? 0 ?>">

                    <?= $edad ?? "—" ?>

                    <?= $edadAprox ? " (aprox)" : "" ?>

                </td>

                <td>

                    <?= ucfirst(
                        strtolower(
                            e($j["estado_espiritual"] ?? "-")
                        )
                    ) ?>

                </td>

                <td
                    <?= $j["estado_actividad"] === "ACTIVO"
                        ? 'data-order="1"'
                        : 'data-order="2"' ?>
                >

<?php

$estadoClase = match ($j["estado_actividad"]) {

    "ACTIVO"   => "estado--activo",

    "INACTIVO" => "estado--inactivo",

    default    => "estado--eliminado"

};

?>

                    <span class="estado-label">

                        <span class="estado <?= $estadoClase ?>"></span>

                        <?= ucfirst(strtolower($j["estado_actividad"])) ?>

                    </span>

                </td>                                                                                                                                                        <td
                    title="<?= e($conexionReal) ?>"

                <?php

                if ($conexionReal === "ALTO RIESGO") {

                    echo 'data-order="3">';
                    echo '<span class="joven-riesgo3">Alto riesgo</span>';

                } elseif ($conexionReal === "RIESGO") {

                    echo 'data-order="2">';
                    echo '<span class="joven-riesgo2">Riesgo</span>';

                } else {

                    echo 'data-order="1">';
                    echo '<span class="joven-ok">Activo</span>';
                }

                ?>

                </td>

                <td data-order="<?= $dias ?>">

                    <?php if ($dias <= 7): ?>

                        <span class="joven-tiempo-main">

                            Muy nuevo

                        </span>

                    <?php elseif ($dias <= 30): ?>

                        <span class="joven-tiempo-main">

                            Nuevo

                        </span>

                    <?php else: ?>

                        <div class="joven-tiempo-main">

                            <?= $años > 0
                                ? $años . " año" . ($años > 1 ? "s" : "")
                                : "" ?>

                        </div>

                        <div class="joven-tiempo-sub">

                            <?= $restoMeses > 0
                                ? $restoMeses . " mes" . ($restoMeses > 1 ? "es" : "")
                                : "" ?>

                        </div>

                    <?php endif; ?>

                </td>

                <td

                <?php

                if ($dias <= 30) {

                    echo 'data-order="1">';
                    echo '<span class="joven-seg joven-seg--nuevo">Inicio</span>';

                } elseif ($meses <= 3) {

                    echo 'data-order="2">';
                    echo '<span class="joven-seg joven-seg--proceso">Consolidación</span>';

                } elseif ($meses <= 12) {

                    echo 'data-order="3">';
                    echo '<span class="joven-seg joven-seg--camino">Crecimiento</span>';

                } else {

                    echo 'data-order="4">';
                    echo '<span class="joven-seg joven-seg--fiel">Maduro</span>';
                }

                ?>

                </td>

                <td>

                    <div class="table-actions">

                        <!-- VER -->

                        <a
                            href="<?= BASE_URL ?>/views/jovenes/ver.php?id=<?= (int)$j["id"] ?>"
                            class="btn-icon btn-view"
                            data-tooltip="Ver detalles"
                        >

                            <i class="fa-solid fa-eye"></i>

                        </a>

                        <?php if ($filtro !== "eliminados"): ?>

                            <!-- EDITAR -->

                            <a
                                href="<?= BASE_URL ?>/views/jovenes/editar.php?id=<?= (int)$j["id"] ?>"
                                class="btn-icon btn-edit"
                                data-tooltip="Editar"
                            >

                                <i class="fa-solid fa-pen"></i>

                            </a>

                            <?php if (tienePermiso("eliminar_jovenes")): ?>

                                <!-- ELIMINAR -->

                                <form
                                    method="POST"
                                    class="inline-form"
                                    action="<?= BASE_URL ?>/controllers/jovenController.php"
                                >

                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?= (int)$j["id"] ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="csrf_token"
                                        value="<?= e($_SESSION["csrf_token"]) ?>"
                                    >

                                    <button
                                        type="submit"
                                        name="eliminar_joven"
                                        class="btn-icon btn-delete"
                                        data-confirm="¿Seguro que deseas eliminar este joven?"
                                    >

                                        <i class="fa-solid fa-trash"></i>

                                    </button>

                                </form>

                            <?php endif; ?>

                        <?php else: ?>

                            <!-- RECUPERAR -->

                            <form
                                method="POST"
                                class="inline-form"
                                action="<?= BASE_URL ?>/controllers/jovenController.php"
                            >

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= (int)$j["id"] ?>"
                                >

                                <input
                                    type="hidden"
                                    name="csrf_token"
                                    value="<?= e($_SESSION["csrf_token"]) ?>"
                                >

                                <button
                                    type="submit"
                                    name="recuperar_joven"
                                    class="btn-icon btn-success"
                                    data-tooltip="Recuperar"
                                    onclick="return confirm('¿Recuperar este joven?')"
                                >

                                    <i class="fa-solid fa-rotate-left"></i>

                                </button>

                            </form>

                            <!-- ELIMINAR DEFINITIVO -->

                            <form
                                method="POST"
                                class="inline-form"
                                action="<?= BASE_URL ?>/controllers/jovenController.php"
                            >

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= (int)$j["id"] ?>"
                                >

                                <input
                                    type="hidden"
                                    name="csrf_token"
                                    value="<?= e($_SESSION["csrf_token"]) ?>"
                                >

                                <button
                                    type="submit"
                                    name="eliminar_definitivo"
                                    class="btn-icon btn-delete"
                                    data-tooltip="Eliminar definitivamente"
                                    onclick="return confirm('Esta acción no se puede deshacer. ¿Continuar?')"
                                >

                                    <i class="fa-solid fa-trash-can"></i>

                                </button>

                            </form>

                        <?php endif; ?>

                    </div>

                </td>

            </tr>

            <?php endforeach; ?>
=======
            <?php if (!empty($jovenes)): ?>

                <?php foreach ($jovenes as $j): ?>

                    <?php

                    /* ===============================
                       EDAD
                    =============================== */

                    if (!empty($j["fecha_nacimiento"])) {

                        $edad = date_diff(
                            date_create($j["fecha_nacimiento"]),
                            date_create("today")
                        )->y;

                    } else {

                        $edad = $j["edad_manual"] ?? "-";

                    }

                    /* ===============================
                       ÚLTIMA ASISTENCIA
                    =============================== */

                    $ultimaAsistencia = !empty($j["ultima_asistencia"])
                        ? date(
                            "d/m/Y",
                            strtotime($j["ultima_asistencia"])
                        )
                        : "Sin registro";

                    /* ===============================
                       RIESGO
                    =============================== */

                    $faltas = (int)$j["faltas_recientes"];

                    $mes0 = (int)($j["asistencias_mes_actual"] ?? 0);
                    $mes1 = (int)($j["asistencias_mes_1"] ?? 0);
                    $mes2 = (int)($j["asistencias_mes_2"] ?? 0);

                    $riesgoTexto = "Normal";
                    $riesgoClase = "badge-success";

                    if ($mes1 <= 1 && $mes2 <= 1) {

                        $riesgoTexto = "Alto";
                        $riesgoClase = "badge-danger";

                    } elseif ($mes0 <= 1 || $faltas >= 3) {

                        $riesgoTexto = "Medio";
                        $riesgoClase = "badge-warning";

                    }

                    ?>

                    <tr>

                        <!-- NOMBRE -->

                        <td>

                            <strong>

                                <?= e($j["nombre_completo"]) ?>

                            </strong>

                        </td>

                        <!-- EDAD -->

                        <td>

                            <?= e($edad) ?>

                        </td>

                        <!-- ESTADO ESPIRITUAL -->

                        <td>

                            <?= e($j["estado_espiritual"] ?: "Sin registrar") ?>

                        </td>

                        <!-- ACTIVIDAD -->

                        <td>

                            <?php if ($j["estado_actividad"] === "ACTIVO"): ?>

                                <span class="badge badge-success">

                                    Activo

                                </span>

                            <?php elseif ($j["estado_actividad"] === "INACTIVO"): ?>

                                <span class="badge badge-warning">

                                    Inactivo

                                </span>

                            <?php else: ?>

                                <span class="badge badge-danger">

                                    Eliminado

                                </span>

                            <?php endif; ?>

                        </td>

                        <!-- ÚLTIMA ASISTENCIA -->

                        <td>

                            <?= e($ultimaAsistencia) ?>

                        </td>

                        <!-- RIESGO -->

                        <td>

                            <span class="badge <?= $riesgoClase ?>">

                                <?= $riesgoTexto ?>

                            </span>

                        </td>

                        <!-- INGRESO -->

                        <td>

                            <?= !empty($j["fecha_ingreso"])
                                ? date("d/m/Y", strtotime($j["fecha_ingreso"]))
                                : "-" ?>

                        </td>

                        <!-- ACCIONES -->

                        <td>

                            <div class="table-actions">

                                <!-- VER -->

                                <a
                                    href="<?= BASE_URL ?>/views/jovenes/ver.php?id=<?= (int)$j["id"] ?>"
                                    class="btn-icon btn-view"
                                    data-tooltip="Ver"
                                >

                                    <i class="fa-solid fa-eye"></i>

                                </a>

                                <?php if ($filtro !== "eliminados"): ?>

                                    <!-- EDITAR -->

                                    <a
                                        href="<?= BASE_URL ?>/views/jovenes/editar.php?id=<?= (int)$j["id"] ?>"
                                        class="btn-icon btn-edit"
                                        data-tooltip="Editar"
                                    >

                                        <i class="fa-solid fa-pen"></i>

                                    </a>

                                    <?php if (tienePermiso("eliminar_jovenes")): ?>

                                        <form
                                            method="POST"
                                            class="inline-form"
                                            action="<?= BASE_URL ?>/controllers/jovenController.php"
                                        >

                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?= (int)$j["id"] ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="action"
                                                value="eliminar_joven"
                                            >

                                            <input
                                                type="hidden"
                                                name="csrf_token"
                                                value="<?= e($_SESSION["csrf_token"]) ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="btn-icon btn-delete"
                                                data-tooltip="Eliminar"
                                                onclick="return confirm('¿Seguro que deseas eliminar este joven?')"
                                            >

                                                <i class="fa-solid fa-trash"></i>

                                            </button>

                                        </form>

                                    <?php endif; ?>

                                <?php else: ?>

    <!-- RECUPERAR -->

    <form
        method="POST"
        class="inline-form"
        action="<?= BASE_URL ?>/controllers/jovenController.php"
    >

        <input type="hidden" name="id" value="<?= (int)$j["id"] ?>">
        <input type="hidden" name="action" value="recuperar_joven">
        <input type="hidden" name="csrf_token" value="<?= e($_SESSION["csrf_token"]) ?>">

        <button
            type="submit"
            class="btn-icon btn-success"
            data-tooltip="Recuperar"
            onclick="return confirm('¿Recuperar este joven?')"
        >
            <i class="fa-solid fa-rotate-left"></i>
        </button>

    </form>

    <!-- ELIMINAR DEFINITIVAMENTE -->

    <form
        method="POST"
        class="inline-form"
        action="<?= BASE_URL ?>/controllers/jovenController.php"
    >

        <input type="hidden" name="id" value="<?= (int)$j["id"] ?>">
        <input type="hidden" name="action" value="eliminar_definitivo">
        <input type="hidden" name="csrf_token" value="<?= e($_SESSION["csrf_token"]) ?>">

        <button
            type="submit"
            class="btn-icon btn-delete"
            data-tooltip="Eliminar definitivamente"
            onclick="return confirm('Esta acción eliminará el joven para siempre. ¿Deseas continuar?')"
        >
            <i class="fa-solid fa-trash-can"></i>
        </button>

    </form>

<?php endif; ?>

                            </div>

                        </td>

                    </tr>

                <?php endforeach; ?>
>>>>>>> 3e2d89c (Actualización del proyecto)

            <?php endif; ?>

            </tbody>

        </table>

    </div>

<<<<<<< HEAD
</div>

</div>

<script>
document.addEventListener("DOMContentLoaded", () => {

    const tabla = initDataTable("#tablaJovenes");

    console.log(tabla);

});


/* ===============================
   BUSCADOR
=============================== */



const buscador = document.getElementById("buscador");

if (tabla && buscador) {

    buscador.addEventListener("input", function () {

        tabla.search(this.value).draw();

    });

}

</script>






<?php require_once __DIR__ . "/../../includes/footer.php"; ?>
=======
</div>                                                                                                                                       <script>
document.addEventListener("DOMContentLoaded", () => {

    /* =====================================
       DATATABLE
    ===================================== */

    const tabla = initDataTable("#tablaJovenes");

    if (!tabla) {
        return;
    }

    /* =====================================
       BUSCADOR
    ===================================== */

    const buscador = document.getElementById("buscador");

    if (buscador) {

        buscador.addEventListener("input", function () {

            tabla.search(this.value).draw();

        });

    }

    /* =====================================
       EXPORTACIONES
    ===================================== */

    const exportaciones = {

        exportPdf: "pdf",
        exportExcel: "excel",
        exportWord: "word",
        exportCsv: "csv",
        exportPrint: "print"

    };

    Object.entries(exportaciones).forEach(([id, tipo]) => {

        const boton = document.getElementById(id);

        if (!boton) {
            return;
        }

        boton.addEventListener("click", () => {

            const botones = tabla.buttons();

            if (!botones) {
                return;
            }

            switch (tipo) {

                case "pdf":
                    botones.container().find(".buttons-pdf").click();
                    break;

                case "excel":
                    botones.container().find(".buttons-excel").click();
                    break;

                case "word":
                    botones.container().find(".buttons-word").click();
                    break;

                case "csv":
                    botones.container().find(".buttons-csv").click();
                    break;

                case "print":
                    botones.container().find(".buttons-print").click();
                    break;

            }

        });

    });

});
</script>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>
>>>>>>> 3e2d89c (Actualización del proyecto)
