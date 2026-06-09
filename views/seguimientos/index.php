
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
   CSS
========================= */

$extraCSS = '
<link rel="stylesheet" href="' . BASE_URL . '/assets/css/modules/seguimientos/seguimientos.css">
';

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

<div class="dashboard__section">

    <!-- =========================
         HEADER
    ========================== -->

    <div class="header-flex">

        <div>

            <h2>
                Consolidado de Seguimientos
            </h2>

            <div class="header-meta">

                <span class="badge badge-info">

                    <?= e($mesTexto) ?>

                </span>

                <span class="header-sub">

                    Vista mensual

                </span>

            </div>

        </div>

    </div>

    <!-- =========================
         KPI
    ========================== -->

    <div class="dashboard__cards">

        <div class="dashboard__card dashboard__card--blue">

            <div class="dashboard__card-title">
                Activos
            </div>

            <div class="dashboard__card-value">
                <?= $totalActivos ?>
            </div>

        </div>

        <div class="dashboard__card dashboard__card--green">

            <div class="dashboard__card-title">
                Con seguimiento
            </div>

            <div class="dashboard__card-value">
                <?= $totalConSeguimiento ?>
            </div>

        </div>

        <div class="dashboard__card dashboard__card--red">

            <div class="dashboard__card-title">
                Sin seguimiento
            </div>

            <div class="dashboard__card-value">
                <?= $totalSinSeguimiento ?>
            </div>

        </div>

        <div class="dashboard__card <?=

            $color === 'ok'

            ? 'dashboard__card--green'

            : (

                $color === 'warn'

                ? 'dashboard__card--orange'

                : 'dashboard__card--red'
            )

        ?>">

            <div class="dashboard__card-title">
                Cumplimiento
            </div>

            <div class="dashboard__card-value">
                <?= $porcentaje ?>%
            </div>

        </div>

    </div>

    <!-- =========================
         ALERTAS
    ========================== -->

    <?php if(count($alertas) > 0): ?>

    <div class="bloque-scroll">

        <div class="bloque-header">

            <div class="section-title">

                <h3>
                    Sin seguimiento
                </h3>

                <span class="section-sub">
                    Este mes
                </span>

            </div>

            <input
                type="text"
                class="buscador"
                placeholder="Buscar..."
            >

        </div>

        <div class="lista">

            <?php foreach($alertas as $j): ?>

            <div class="lista-item">

                <div class="lista-left">

                    <div class="avatar">

                        <?= strtoupper(
                            substr(
                                $j["nombre_completo"],
                                0,
                                1
                            )
                        ) ?>

                    </div>

                    <div>

                        <div class="nombre">

                            <?= e($j["nombre_completo"]) ?>

                        </div>

                        <div class="sub">

                            <?= e(
                                $j["telefono"]
                                ?: "Sin teléfono"
                            ) ?>

                        </div>

                    </div>

                </div>

                <div class="lista-right">

                    <span class="badge badge-danger">

                        Pendiente

                    </span>

                    <a
                        href="../jovenes/ver.php?id=<?= $j["id"] ?>"
                        class="btn-mini <?= ($j["genero"] ?? '') === 'F'
                            ? 'chica'
                            : 'chico' ?>">

                        Ver

                    </a>

                </div>

            </div>

            <?php endforeach; ?>

        </div>

    </div>

    <?php else: ?>

        <p class="ok">

            Todos tienen seguimiento este mes

        </p>

    <?php endif; ?>

    <!-- =========================
         DETALLE
    ========================== -->

    <div class="bloque-scroll">

        <div class="bloque-header">

            <div class="section-title">

                <h3>
                    Detalle
                </h3>

                <span class="section-sub">
                    Registros
                </span>

            </div>

            <input
                type="text"
                class="buscador"
                placeholder="Buscar..."
            >

        </div>

        <div class="bloque-body">

            <table class="tabla">

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

                            <?= ucfirst(strtolower(
                                e($s["modalidad_contacto"])
                            )) ?>

                        </td>

                        <td>

                            <span class="estado <?= strtolower(
                                str_replace(
                                    '_',
                                    '-',
                                    $s["estado_proceso"]
                                )
                            ) ?>">

                                <?= ucfirst(strtolower(
                                    str_replace(
                                        '_',
                                        ' ',
                                        e($s["estado_proceso"])
                                    )
                                )) ?>

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

    <!-- =========================
         BOTONES
    ========================== -->

    <div class="btn-group">

        <a
            href="../dashboard.php"
            class="btn btn-secondary">

            Volver

        </a>

        <a
            href="reporte_pdf.php"
            target="_blank"
            class="btn btn-primary">

            <i class="fa-solid fa-file-pdf"></i>

            PDF

        </a>

    </div>

</div>

<script>

document.querySelectorAll(".buscador")
.forEach(input => {

    input.addEventListener("keyup", function(){

        const bloque =
            this.closest(".bloque-scroll");

        const filtro =
            this.value.toLowerCase();

        bloque.querySelectorAll(
            ".lista-item, tbody tr"
        ).forEach(el => {

            el.style.display =
                el.innerText
                .toLowerCase()
                .includes(filtro)

                ? ""

                : "none";
        });
    });
});

</script>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>