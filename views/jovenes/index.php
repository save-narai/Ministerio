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

$permitidos = ["todos", "activos", "inactivos", "riesgo2", "riesgo3"];

$filtro = $_GET["filtro"] ?? "todos";

if (!in_array($filtro, $permitidos)) {
    $filtro = "todos";
}

/* =========================
   QUERY OPTIMIZADA
========================= */

$query = "
SELECT
    j.id,
    j.nombre_completo,
    j.fecha_nacimiento,
    j.estado_espiritual,
    j.estado_actividad,
    j.fecha_ingreso,

    IFNULL(
        TIMESTAMPDIFF(YEAR, j.fecha_nacimiento, CURDATE()),
        '-'
    ) AS edad,

    COUNT(
        CASE
            WHEN a.asistio = 0 THEN 1
        END
    ) AS faltas

FROM jovenes j

LEFT JOIN asistencia a
    ON a.joven_id = j.id
";

/* =========================
   WHERE
========================= */

$where = [];

if ($filtro === "activos") {
    $where[] = "j.estado_actividad = 'ACTIVO'";
}

if ($filtro === "inactivos") {
    $where[] = "j.estado_actividad = 'INACTIVO'";
}

if (!empty($where)) {
    $query .= " WHERE " . implode(" AND ", $where);
}

/* =========================
   GROUP BY
========================= */

$query .= "
GROUP BY
    j.id,
    j.nombre_completo,
    j.fecha_nacimiento,
    j.estado_espiritual,
    j.estado_actividad,
    j.fecha_ingreso
";

/* =========================
   HAVING (para COUNT)
========================= */

if ($filtro === "riesgo2") {
    $query .= " HAVING faltas = 2";
}

if ($filtro === "riesgo3") {
    $query .= " HAVING faltas >= 3";
}

/* =========================
   ORDER
========================= */

$query .= " ORDER BY j.nombre_completo ASC";

/* =========================
   EJECUTAR
========================= */

$stmt = $pdo->prepare($query);
$stmt->execute();

$jovenes = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   CSS
========================= */

$extraCSS = '
<link rel="stylesheet" href="' . BASE_URL . '/assets/css/modules/jovenes/jovenes.css">

<style>

/* =========================
   TIEMPO
========================= */

.joven-tiempo-box{
    display:flex;
    flex-direction:column;
    gap:2px;
}

.joven-tiempo-main{
    font-weight:700;
    font-size:14px;
}

.joven-tiempo-sub{
    font-size:12px;
    color:#666;
}

.joven-badge-tiempo{
    padding:6px 10px;
    border-radius:10px;
    font-size:13px;
    font-weight:600;
}

.joven-tiempo-nuevo{
    background:#ffe5e5;
    color:#d60000;
}

/* =========================
   SEGUIMIENTO
========================= */

.joven-seg{
    font-size:13px;
    font-weight:600;
}

.joven-seg--nuevo{
    color:#d60000;
}

.joven-seg--proceso{
    color:#b38600;
}

.joven-seg--camino{
    color:#0066cc;
}

.joven-seg--fiel{
    color:#009933;
}

/* =========================
   RIESGO
========================= */

.joven-riesgo2{
    color:#d89b00;
    font-weight:700;
}

.joven-riesgo3{
    color:#d60000;
    font-weight:700;
}

/* =========================
   FILTROS
========================= */

.jovenes__filtros{
    display:flex;
    flex-wrap:wrap;
    gap:10px;
    margin:20px 0;
}

.jovenes__tag{
    text-decoration:none;
    padding:10px 14px;
    border-radius:12px;
    font-size:14px;
    font-weight:600;
    transition:0.2s ease;
}

.jovenes__tag:hover{
    transform:translateY(-2px);
}

.jovenes__tag--todos{
    background:#f0f0f0;
    color:#333;
}

.jovenes__tag--activos{
    background:#e7ffe7;
    color:#008a00;
}

.jovenes__tag--inactivos{
    background:#ffe7e7;
    color:#c20000;
}

.jovenes__tag--riesgo{
    background:#fff5d9;
    color:#a06a00;
}

.jovenes__tag--alto{
    background:#ffe1e1;
    color:#d60000;
}

.jovenes__tag--active{
    outline:3px solid rgba(0,0,0,.1);
    transform:scale(1.03);
}

</style>
';

require_once __DIR__ . "/../../includes/header.php";
?>

<div class="jovenes">

    <!-- HEADER -->
    <div class="jovenes__header">

        <h1 class="jovenes__title">
            👤 Gestión de Jóvenes
        </h1>

        <div class="top-actions">

            <a href="<?= BASE_URL ?>/views/jovenes/crear.php"
               class="jovenes__btn">
                ➕ Nuevo
            </a>

            <a href="<?= BASE_URL ?>/views/jovenes/reporte_jovenes_pdf.php"
               target="_blank"
               class="jovenes__btn">
                📄 PDF
            </a>

        </div>

    </div>

    <!-- FILTROS -->
    <div class="jovenes__filtros">

        <a href="?filtro=todos"
           class="jovenes__tag jovenes__tag--todos <?= $filtro === 'todos' ? 'jovenes__tag--active' : '' ?>">
            👥 Todos
        </a>

        <a href="?filtro=activos"
           class="jovenes__tag jovenes__tag--activos <?= $filtro === 'activos' ? 'jovenes__tag--active' : '' ?>">
            🟢 Activos
        </a>

        <a href="?filtro=inactivos"
           class="jovenes__tag jovenes__tag--inactivos <?= $filtro === 'inactivos' ? 'jovenes__tag--active' : '' ?>">
            🔴 Inactivos
        </a>

        <a href="?filtro=riesgo2"
           class="jovenes__tag jovenes__tag--riesgo <?= $filtro === 'riesgo2' ? 'jovenes__tag--active' : '' ?>">
            🟡 Riesgo
        </a>

        <a href="?filtro=riesgo3"
           class="jovenes__tag jovenes__tag--alto <?= $filtro === 'riesgo3' ? 'jovenes__tag--active' : '' ?>">
            🚨 Alto
        </a>

    </div>

    <!-- BUSCADOR -->
    <input
        type="text"
        id="buscador"
        placeholder="Buscar joven..."
        class="buscador"
    >

    <br><br>

    <!-- TABLA -->

    <!-- TABLA -->
<div class="jovenes__table bloque-scroll">


    <div class="jovenes__table">

        <table id="tablaJovenes">

            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Edad</th>
                    <th>Estado Espiritual</th>
                    <th>Actividad</th>
                    <th>Conexión</th>
                    <th>Tiempo Iglesia</th>
                    <th>Seguimiento</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>

            <?php foreach($jovenes as $j): ?>

            <?php

            $faltas = (int)$j["faltas"];

            /* =========================
               TIEMPO
            ========================= */

            $mesesTotal = 0;
            $diasTotal = 0;

            if (!empty($j["fecha_ingreso"])) {

                try {

                    $fechaIngreso = new DateTime($j["fecha_ingreso"]);
                    $hoy = new DateTime();

                    $diff = $fechaIngreso->diff($hoy);

                    $mesesTotal = ($diff->y * 12) + $diff->m;
                    $diasTotal = $diff->days;

                } catch (Exception $e) {

                    $mesesTotal = 0;
                    $diasTotal = 0;
                }
            }

            $años = floor($mesesTotal / 12);
            $restoMeses = $mesesTotal % 12;

            ?>

            <tr>

                <!-- NOMBRE -->
                <td>
                    <?= htmlspecialchars($j["nombre_completo"]) ?>
                </td>

                <!-- EDAD -->
                <td>
                    <?= htmlspecialchars($j["edad"]) ?>
                </td>

                <!-- ESTADO ESPIRITUAL -->
                <td>
                    <?= htmlspecialchars($j["estado_espiritual"] ?? "-") ?>
                </td>

                <!-- ACTIVIDAD -->
                <td>

                    <?php if($j["estado_actividad"] === "ACTIVO"): ?>

                        <span class="estado estado--activo"></span>

                    <?php else: ?>

                        <span class="estado estado--inactivo"></span>

                    <?php endif; ?>

                </td>

                <!-- CONEXIÓN -->
                <td>

                    <?php

                    if ($faltas >= 3) {

                        echo "<span class='joven-riesgo3'>🔴 Alto</span>";

                    } elseif ($faltas == 2) {

                        echo "<span class='joven-riesgo2'>🟡 Riesgo</span>";

                    } else {

                        echo "✔️";
                    }

                    ?>

                </td>

                <!-- TIEMPO -->
                <td>

                    <div class="joven-tiempo-box">

                        <?php if ($diasTotal <= 30): ?>

                            <span class="joven-badge-tiempo joven-tiempo-nuevo">
                                🔴 Nuevo
                            </span>

                        <?php else: ?>

                            <?php if ($años > 0): ?>

                                <span class="joven-tiempo-main">
                                    <?= $años ?> año<?= $años > 1 ? 's' : '' ?>
                                </span>

                            <?php endif; ?>

                            <?php if ($restoMeses > 0): ?>

                                <span class="joven-tiempo-sub">
                                    <?= $restoMeses ?> mes<?= $restoMeses > 1 ? 'es' : '' ?>
                                </span>

                            <?php endif; ?>

                        <?php endif; ?>

                    </div>

                </td>

                <!-- SEGUIMIENTO -->
                <td>

                    <?php

                    if ($diasTotal <= 30) {

                        echo "<span class='joven-seg joven-seg--nuevo'>🔴 Nuevo</span>";

                    } elseif ($mesesTotal <= 3) {

                        echo "<span class='joven-seg joven-seg--proceso'>🟡 En proceso</span>";

                    } elseif ($mesesTotal <= 12) {

                        echo "<span class='joven-seg joven-seg--camino'>🔵 En camino</span>";

                    } else {

                        echo "<span class='joven-seg joven-seg--fiel'>🟢 Fiel</span>";
                    }

                    ?>

                </td>

                <!-- ACCIONES -->
                <td class="acciones-cell">

                    <div class="acciones">

                        <!-- VER -->
                        <a href="<?= BASE_URL ?>/views/jovenes/ver.php?id=<?= (int)$j["id"] ?>"
                           class="btn-icon ver">
                            👁️
                        </a>

                        <!-- EDITAR -->
                        <a href="<?= BASE_URL ?>/views/jovenes/editar.php?id=<?= (int)$j["id"] ?>"
                           class="btn-icon editar">
                            ✏️
                        </a>

                        <!-- ELIMINAR -->
                        <?php if(tienePermiso('eliminar_jovenes')): ?>

                        <form
                            action="<?= BASE_URL ?>/controllers/jovenController.php"
                            method="POST"
                            class="inline-form"
                            onsubmit="return confirm('¿Eliminar este joven?');"
                        >

                            <input
                                type="hidden"
                                name="id"
                                value="<?= (int)$j["id"] ?>"
                            >

                            <!-- CSRF -->
                            <?php if(isset($_SESSION['csrf_token'])): ?>

                                <input
                                    type="hidden"
                                    name="csrf_token"
                                    value="<?= $_SESSION['csrf_token'] ?>"
                                >

                            <?php endif; ?>

                            <button
                                type="submit"
                                name="eliminar_joven"
                                class="btn-icon eliminar"
                            >
                                🗑️
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

<!-- DATATABLE -->
<script>

document.addEventListener("DOMContentLoaded", () => {

    if (typeof $ === "undefined" || !$.fn.DataTable) {
        console.warn("DataTables no está cargado.");
        return;
    }

    const tabla = $('#tablaJovenes').DataTable({

        pageLength: 8,

        language: {

            info: "Mostrando _START_ a _END_ de _TOTAL_ jóvenes",

            paginate: {
                previous: "←",
                next: "→"
            }

        },

        dom: 't<"datatable-footer"ip>'
    });

    /* =========================
       BUSCADOR
    ========================= */

    const buscador = document.getElementById("buscador");

    if (buscador) {

        buscador.addEventListener("keyup", function () {

            tabla.search(this.value).draw();

        });

    }

});

</script>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>