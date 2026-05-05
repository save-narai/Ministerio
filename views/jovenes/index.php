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

/* FILTRO */
$permitidos = ["todos", "activos", "inactivos", "riesgo2", "riesgo3"];
$filtro = $_GET["filtro"] ?? "todos";

if (!in_array($filtro, $permitidos)) {
    $filtro = "todos";
}

/* QUERY */
$query = "
    SELECT
        j.id,
        j.nombre_completo,
        j.fecha_nacimiento,
        j.estado_espiritual,
        j.estado_actividad,
        j.fecha_ingreso,
        TIMESTAMPDIFF(YEAR, j.fecha_nacimiento, CURDATE()) AS edad,
        COALESCE(SUM(CASE WHEN a.asistio = 0 THEN 1 ELSE 0 END),0) AS faltas
    FROM jovenes j
    LEFT JOIN asistencia a ON j.id = a.joven_id
    GROUP BY j.id
";

$having = [];

if ($filtro === "activos") $having[] = "j.estado_actividad = 'ACTIVO'";
if ($filtro === "inactivos") $having[] = "j.estado_actividad = 'INACTIVO'";
if ($filtro === "riesgo2") $having[] = "faltas = 2";
if ($filtro === "riesgo3") $having[] = "faltas >= 3";

if (!empty($having)) {
    $query .= " HAVING " . implode(" AND ", $having);
}

$query .= " ORDER BY j.nombre_completo ASC";

$stmt = $pdo->query($query);
$jovenes = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* CSS */
$extraCSS = '
<link rel="stylesheet" href="' . BASE_URL . '/assets/css/modules/jovenes/jovenes.css">
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
    <div class="jovenes__filters">

        <a href="?filtro=todos" class="jovenes__tag">
            👥 Todos
        </a>

        <a href="?filtro=activos" class="jovenes__tag">
            🟢 Activos
        </a>

        <a href="?filtro=inactivos" class="jovenes__tag">
            🔴 Inactivos
        </a>

        <a href="?filtro=riesgo2" class="jovenes__tag">
            🟡 Riesgo
        </a>

        <a href="?filtro=riesgo3" class="jovenes__tag">
            🚨 Alto
        </a>

    </div>

    <!-- BUSCADOR -->
    <input
        type="text"
        id="buscador"
        placeholder="🔍 Buscar joven..."
        class="buscador"
    >

    <br><br>

    <!-- TABLA -->
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
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>

            <?php foreach($jovenes as $j): ?>

            <?php

            $faltas = (int)$j["faltas"];

            $meses = 0;

            if (!empty($j["fecha_ingreso"])) {

                $inicio = new DateTime($j["fecha_ingreso"]);
                $hoy = new DateTime();

                if ($inicio <= $hoy) {

                    $diff = $hoy->diff($inicio);

                    $meses = ($diff->y * 12) + $diff->m;
                }
            }

            ?>

            <tr>

                <td><?= htmlspecialchars($j["nombre_completo"]) ?></td>

                <td><?= htmlspecialchars($j["edad"] ?? "-") ?></td>

                <td><?= htmlspecialchars($j["estado_espiritual"] ?? "-") ?></td>

                <!-- ACTIVIDAD -->
                <td>

                    <?php if($j["estado_actividad"] === "ACTIVO"): ?>

                        <span class="estado estado--activo"></span>

                    <?php else: ?>

                        <span class="estado estado--inactivo"></span>

                    <?php endif; ?>

                </td>

                <!-- CONEXION -->
                <td>

                    <?php
                    if ($faltas >= 3) {

                        echo "<span class='riesgo3'>🔴 Alto</span>";

                    } elseif ($faltas == 2) {

                        echo "<span class='riesgo2'>🟡 Riesgo</span>";

                    } else {

                        echo "✔️";
                    }
                    ?>

                </td>

                <!-- TIEMPO -->
                <td>
                    <?= $meses ?> meses
                </td>

              <!-- ACCIONES -->
<td class="acciones-cell">

    <div class="acciones">

        <!-- VER -->
        <a
            href="<?= BASE_URL ?>/views/jovenes/ver.php?id=<?= (int)$j["id"] ?>"
            class="btn-icon ver"
        >

            <svg viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 5c-7 0-10 7-10 7s3 7 10 7 10-7 10-7-3-7-10-7zm0 11a4 4 0 1 1 0-8 4 4 0 0 1 0 8"/>
            </svg>

        </a>

        <!-- EDITAR -->
        <a
            href="<?= BASE_URL ?>/views/jovenes/editar.php?id=<?= (int)$j["id"] ?>"
            class="btn-icon editar"
        >

            <svg viewBox="0 0 24 24" fill="currentColor">
                <path d="M3 17.25V21h3.75L17.8 9.94l-3.75-3.75L3 17.25zm14.7-9.04a1 1 0 0 0 0-1.41l-2.5-2.5a1 1 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.99-1.67z"/>
            </svg>

        </a>

        <?php if(tienePermiso('eliminar_jovenes')): ?>

        <!-- ELIMINAR -->
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

            <button
                type="submit"
                name="eliminar_joven"
                class="btn-icon eliminar"
            >

                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M6 7h12l-1 14H7L6 7zm3-3h6l1 2H8l1-2z"/>
                </svg>

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

<script>

document.addEventListener("DOMContentLoaded", () => {

    // VALIDAR LIBRERÍAS
    if (typeof $ === "undefined" || !$.fn.DataTable) {
        console.error("DataTables no está disponible");
        return;
    }

    // EVITAR DOBLE INICIALIZACIÓN
    if ($.fn.DataTable.isDataTable('#tablaJovenes')) {
        return;
    }

    // TABLA
    const tabla = $('#tablaJovenes').DataTable({

        pageLength: 8,

        language: {
            info: "Mostrando _START_ a _END_ de _TOTAL_ jóvenes",
            infoFiltered: "",
            paginate: {
                previous: "←",
                next: "→"
            }
        },

        dom: 't<"datatable-footer"ip>'
    });

    // BUSCADOR
    const buscador = document.getElementById("buscador");

    if (buscador) {
        buscador.addEventListener("keyup", function () {
            tabla.search(this.value).draw();
        });
    }

});

</script>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>