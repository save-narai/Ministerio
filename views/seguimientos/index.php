
<?php

require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/permiso.php";
require_once __DIR__ . "/../../middleware/actividad.php";
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

actualizarEstadoActividad();



/* =========================
   RESUMEN
========================= */

$data = obtenerResumenSeguimientosMes();

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
<div class="page-header">

    <div class="page-header-left">

        <h1 class="page-title">
            Seguimientos
        </h1>

        <div class="page-subtitle">
            Consolidado mensual del acompañamiento Ministerial.
        </div>

    </div>

    <div class="page-header-right">

        <span class="badge badge-info">
            <?= e($mesTexto) ?>
        </span>

        <a
            href="reporte_pdf.php"
            target="_blank"
            class="btn btn-primary"
        >
            <i class="fa-solid fa-download"></i>
            Exportar
        </a>

    </div>

</div>

<br>
<br>

<!-- =====================================
     ESTADÍSTICAS
===================================== -->

<div class="stats-grid gx-stats">

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

    <div class="stat-card <?= e($color ?: 'orange') ?>">

        <span class="stat-number">
            <?= $porcentaje ?>%
        </span>

        <span class="stat-label">
            Cumplimiento
        </span>

    </div>

</div>

<!-- =====================================
     ALERTAS
===================================== -->

<?php if(count($alertas) > 0): ?>

<div class="page-section">

  <div class="section-header">

  <h2 class="section-title">

    Jóvenes sin seguimiento

    <span class="section-counter">

        <?= count($alertas) ?>

    </span>

</h2>

</div>

    <div class="seguimientos-alertas">

        <?php foreach($alertas as $j): ?>

        <div class="seguimiento-alerta-card">

            <div class="seguimiento-alerta-left">

                <div class="avatar">

                    <?= mb_strtoupper(
                        mb_substr(
                            $j["nombre_completo"],
                            0,
                            1
                        )
                    ) ?>

                </div>

                <div>

                    <h4>

                        <?= e($j["nombre_completo"]) ?>

                    </h4>

                    <span>

                        <?= e(
                            $j["telefono"]
                            ?: "Sin teléfono"
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
                    class="btn-mini <?= ($j["genero"] ?? '') === 'F'
                        ? 'chica'
                        : 'chico' ?>"
                >
                    Ver perfil
                </a>

            </div>

        </div>

        <?php endforeach; ?>

    </div>

</div>

<div class="section-divider"></div>

<?php endif; ?>

    <!-- =====================================
         TABLA
    ====================================== -->

    <div class="page-section">

        <div class="section-header historial-header">

    <h2 class="section-title">
        Historial de seguimientos
    </h2>

    <div class="search-wrapper historial-search">

        <input
            type="text"
            id="buscador"
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

                    <?php foreach($seguimientosMes as $s): ?>

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

                            <span class="estado <?= strtolower(
                                str_replace(
                                    '_',
                                    '-',
                                    $s["estado_proceso"]
                                )
                            ) ?>">

                                <?= ucfirst(
                                    strtolower(
                                        str_replace(
                                            '_',
                                            ' ',
                                            e($s["estado_proceso"])
                                        )
                                    )
                                ) ?>

                            </span>

                        </td>

                        <td>

                            <?= e(
                                $s["responsable_nombre"]
                                ?? "-"
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

    </div>

    <!-- =====================================
         BOTONES
    ====================================== -->

    <div class="form-actions">

        <a
            href="../dashboard.php"
            class="btn btn-back"
        >

            <i class="fa-solid fa-arrow-left"></i>

            Volver

        </a>

    </div>

</div>

<script>

document.addEventListener('DOMContentLoaded', ()=>{

    const tabla =
        initDataTable(
            '#tablaSeguimientos'
        );

    if(tabla){

        initExportButtons(tabla);

    }

});

</script>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>