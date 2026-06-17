<?php
require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/permiso.php";
require_once __DIR__ . "/../../middleware/actividad.php";
require_once __DIR__ . "/../../config/conexion.php";

if (!tienePermiso('gestionar_jovenes')) {
    header("Location: ../dashboard.php");
    exit;
}

actualizarEstadoActividad();

/* =========================
   FILTROS
========================= */

$permitidos = [
    "todos",
    "activos",
    "inactivos",
    "eliminados",
    "riesgo2",
    "riesgo3"
];

$filtro = $_GET["filtro"] ?? "todos";

if (!in_array($filtro, $permitidos)) {
    $filtro = "todos";
}

/* =========================
   QUERY
========================= */

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
                AND MONTH(r.fecha) = MONTH(CURDATE())
                AND YEAR(r.fecha) = YEAR(CURDATE())
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
                AND MONTH(r.fecha) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
                AND YEAR(r.fecha) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
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
                AND MONTH(r.fecha) = MONTH(DATE_SUB(CURDATE(), INTERVAL 2 MONTH))
                AND YEAR(r.fecha) = YEAR(DATE_SUB(CURDATE(), INTERVAL 2 MONTH))
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
    $where[] = "j.estado_actividad = 'ACTIVO'";
}

if ($filtro === "inactivos") {
    $where[] = "j.estado_actividad = 'INACTIVO'";
}

if ($filtro === "eliminados") {
    $where = ["j.estado_actividad = 'ELIMINADO'"];
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



require_once __DIR__ . "/../../includes/header.php";
?>

<div class="page">

    <?php if(isset($_SESSION["success"])): ?>

    <script>
    document.addEventListener("DOMContentLoaded", () => {

        showToast(
            <?= json_encode($_SESSION["success"]); ?>,
            "success"
        );

    });
    </script>

    <?php unset($_SESSION["success"]); endif; ?>

<!-- HEADER -->

<div class="page-header">

    <div class="page-header-left">

        <h1 class="page-title">
            Gestión de Jóvenes
        </h1>

        <div class="page-subtitle">
            Administra registros, seguimiento y actividad juvenil
        </div>

    </div>

    <div class="page-header-right">

        <!-- NUEVO -->

        <a
            href="<?= BASE_URL ?>/views/jovenes/crear.php"
            class="btn btn-primary"
        >

            <i class="fa-solid fa-plus"></i>

            Nuevo

        </a>

        <!-- EXPORT -->

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

</div>

   <?php

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

    if (
        $mes1 <= 1 &&
        $mes2 <= 1
    ) {

        $totalAltoRiesgo++;

    } elseif (
        $mes0 <= 1 ||
        $faltas >= 3
    ) {

        $totalRiesgo++;
    }
}

?>

<!-- TOOLBAR -->

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
        >

    </div>

</div>

    <!-- TABLA -->

    <div class="page-section">

    <div class="table-container">

        <table
            id="tablaJovenes"
            class="table"
        >

            <thead>

                <tr>

                    <th>Nombre</th>
                    <th>Edad</th>
                    <th>Estado</th>
                    <th>Actividad</th>
                    <th>Conexión</th>
                    <th>Tiempo</th>
                    <th>Seguimiento</th>
                    <th>Acciones</th>

                </tr>

            </thead>

            <tbody>

            <?php foreach($jovenes as $j): ?>

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
                    <?= htmlspecialchars($j["nombre_completo"]) ?>
                </td>

                <td data-order="<?= $edad ?? 0 ?>">

                    <?= $edad ?? "—" ?>

                    <?= $edadAprox ? " (aprox)" : "" ?>

                </td>

                <td>

                    <?= ucfirst(strtolower(
                        htmlspecialchars($j["estado_espiritual"] ?? "-")
                    )) ?>

                </td>
<td
<?= $j["estado_actividad"] === "ACTIVO"
    ? 'data-order="1"'
    : 'data-order="2"' ?>
>

<?php

$estadoClase = match($j["estado_actividad"]) {
    "ACTIVO" => "estado--activo",
    "INACTIVO" => "estado--inactivo",
    default => "estado--eliminado"
};

?>

<span class="estado-label">

    <span class="estado <?= $estadoClase ?>"></span>

    <?= ucfirst(strtolower($j["estado_actividad"])) ?>

</span>

</td>

                <td
                    title="<?= htmlspecialchars($conexionReal) ?>"

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
                            href="<?= BASE_URL ?>/views/jovenes/ver.php?id=<?= $j["id"] ?>"
                            class="btn-icon btn-view"
                            data-tooltip="Ver detalles"
                        >

                            <i class="fa-solid fa-eye"></i>

                        </a>

                        <!-- EDITAR -->

                        <a
                            href="<?= BASE_URL ?>/views/jovenes/editar.php?id=<?= $j["id"] ?>"
                            class="btn-icon btn-edit"
                            data-tooltip="Editar"
                        >

                            <i class="fa-solid fa-pen"></i>

                        </a>

                        <?php if(tienePermiso('eliminar_jovenes')): ?>

                        <!-- ELIMINAR -->

                        <form
                            method="POST"
                            class="inline-form"
                            action="<?= BASE_URL ?>/controllers/jovenController.php"
                        >

                            <input
                                type="hidden"
                                name="id"
                                value="<?= $j["id"] ?>"
                            >

                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?= $_SESSION['csrf_token'] ?>"
                            >

                            <button
                                type="submit"
                                name="eliminar_joven"
                                class="btn-icon btn-delete"
                                data-tooltip="Eliminar"
                                onclick="return confirm('¿Seguro que deseas eliminar este joven?')"
                            >

                                <i class="fa-solid fa-trash"></i>

                            </button>

                        </form>

                        <?php endif; ?>

                    </div>

                </td>

            </tr>

            <?php endforeach; ?>

            </tbody>

        </table>

    </div>
    
    </div>

</div>

</div>

<script>

document.addEventListener('DOMContentLoaded', () => {

    const tabla =
    initDataTable('#tablaJovenes');

    if(tabla){

        initSearch(
            'buscador',
            tabla
        );

        initExportButtons(
            tabla
        );
    }

});

</script>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>