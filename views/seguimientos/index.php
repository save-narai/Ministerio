<?php

require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/permiso.php";
require_once __DIR__ . "/../../services/actividadService.php";
require_once __DIR__ . "/../../config/conexion.php";

require_once __DIR__ . "/../../services/seguimientoService.php";
require_once __DIR__ . "/../../helpers/format.php";
require_once __DIR__ . "/../../helpers/fechas.php";

/* =========================
   PERMISOS
========================= */

if (!tienePermiso('gestionar_seguimientos')) {

    header("Location: ../dashboard.php");
    exit;
}

/* =========================
   ACTUALIZAR ACTIVIDAD
========================= */

actualizarEstadoActividad($pdo);

/* =========================
   RESUMEN
========================= */

$data = obtenerResumenSeguimientosMes($pdo);

$seguimientosMes     = $data['seguimientosMes'] ?? [];
$totalActivos        = $data['totalActivos'] ?? 0;
$totalConSeguimiento = $data['totalConSeguimiento'] ?? 0;
$totalSinSeguimiento = $data['totalSinSeguimiento'] ?? 0;
$porcentaje          = $data['porcentaje'] ?? 0;
$color               = $data['color'] ?? '';
$mesTexto            = $data['mesTexto'] ?? '';

/* =========================
   ALERTAS
========================= */

$stmt = $pdo->prepare("
    SELECT

        id,
        nombre_completo,
        telefono,
        genero

    FROM jovenes

    WHERE estado_actividad = 'ACTIVO'

    AND id NOT IN (

        SELECT joven_id

        FROM seguimientos

        WHERE DATE_FORMAT(
            fecha_contacto,
            '%Y-%m'
        ) = DATE_FORMAT(
            CURDATE(),
            '%Y-%m'
        )
    )

    ORDER BY nombre_completo ASC
");

$stmt->execute();

$alertas = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   HEADER
========================= */

require_once __DIR__ . "/../../includes/header.php";

?>

<div class="seguimientos-page">

    <!-- =========================
         HEADER
    ========================== -->

    <div class="page-header">

        <div class="page-header-left">

            <h1 class="page-title">
                Seguimientos
            </h1>

            <p class="page-subtitle">
                Consolidado mensual del acompañamiento ministerial.
            </p>

            <span class="badge badge-info">
                <?= e($mesTexto) ?>
            </span>

        </div>

        <div class="page-header-right">

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


    <!-- =========================
         ESTADÍSTICAS
    ========================== -->

    <section class="gx-stats">

        <div class="stat-card info">

            <span class="stat-number">
                <?= $totalActivos ?>
            </span>

            <span class="stat-label">
                Jóvenes activos
            </span>

        </div>


        <div class="stat-card success">

            <span class="stat-number">
                <?= $totalConSeguimiento ?>
            </span>

            <span class="stat-label">
                Con seguimiento
            </span>

        </div>


        <div class="stat-card danger">

            <span class="stat-number">
                <?= $totalSinSeguimiento ?>
            </span>

            <span class="stat-label">
                Sin seguimiento
            </span>

        </div>


        <div class="stat-card <?= $color ?>">

            <span class="stat-number">

                <?= $porcentaje ?>%

            </span>

            <span class="stat-label">

                Cumplimiento

            </span>

        </div>

    </section>

  <!-- =========================
     ALERTAS
========================== -->

<section class="page-section">

    <div class="section-header">

        <div>

            <h2 class="section-title">
                Jóvenes sin seguimiento
            </h2>

            <p class="section-subtitle">
                Pendientes durante este mes
            </p>

        </div>

    </div>

    <?php if (!empty($alertas)): ?>

        <div class="seguimientos-alertas">

            <?php foreach ($alertas as $j): ?>

                <div class="seguimiento-alerta-card">

                    <div class="seguimiento-alerta-left">

                        <div>

                            <h4>

                                <?= e($j["nombre_completo"]) ?>

                            </h4>

                            <span>

                                <?= e(
                                    $j["telefono"] ?: "Sin teléfono"
                                ) ?>

                            </span>

                        </div>

                    </div>

                    <div class="seguimiento-alerta-right">

                        <span class="badge badge-danger">

                            Pendiente

                        </span>

                       <a
    href="../jovenes/ver.php?id=<?= $j["id"] ?>"
    class="btn btn-sm btn-perfil <?= ($j["genero"] ?? "") === "F"
        ? "btn-perfil-chica"
        : "btn-perfil-chico" ?>"
>

    Ver perfil

</a>
                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    <?php else: ?>

        <div class="gx-empty">

            <i class="fa-solid fa-circle-check"></i>

            <p>

                Todos los jóvenes tienen seguimiento este mes.

            </p>

        </div>

    <?php endif; ?>

</section>

<br>
<br>

<!-- =====================================
     TABLA
====================================== -->

<section class="page-section">

    <div class="section-header">

        <div>

            <h2 class="section-title">

                Historial de seguimientos

            </h2>

            <p class="section-subtitle">

                Registros del mes actual

            </p>

        </div>

    </div>

    <div class="gx-toolbar">

        <div class="search-wrapper">

            <input
                id="buscador"
                type="text"
                class="search-input"
                placeholder="Buscar seguimiento..."
            >

        </div>

    </div>

    <div class="table-responsive">

        <table
            id="tablaSeguimientos"
            class="table gx-table"
        >

            <thead>

                <tr>

                    <th>Nombre</th>

                    <th>Modalidad</th>

                    <th>Estado</th>

                    <th>Responsable</th>

                    <th>Fecha</th>

                </tr>

            </thead>

            <tbody>

                <?php foreach ($seguimientosMes as $s): ?>

                    <tr>

                        <td>

                            <?= e($s["nombre_completo"]) ?>

                        </td>

                        <td>

                            <?= ucfirst(
                                strtolower(
                                    e($s["modalidad_contacto"])
                                )
                            ) ?>

                        </td>

                        <td>

                            <span class="estado <?= strtolower(str_replace('_', '-', e($s["estado_proceso"]))) ?>">

                                <?= ucfirst(
                                    strtolower(
                                        str_replace(
                                            "_",
                                            " ",
                                            e($s["estado_proceso"])
                                        )
                                    )
                                ) ?>

                            </span>

                        </td>

                        <td>

                            <?= e(
                                $s["responsable_nombre"] ?? "-"
                            ) ?>

                        </td>

                        <td>

                            <?= formatearFecha(
                                $s["fecha_contacto"]
                            ) ?>

                        </td>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    </div>

</section>



<script
    src="<?= BASE_URL ?>/assets/js/modulos/seguimientos/index.js">
</script>

<script

    defer

    src="<?= BASE_URL ?>/assets/js/components/gx-notifications.js">

</script>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>