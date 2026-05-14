<?php
require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/permiso.php";
require_once __DIR__ . "/../../middleware/actividad.php";
require_once __DIR__ . "/../../config/conexion.php";

/* SERVICES */
require_once __DIR__ . "/../../services/seguimientoService.php";

/* HELPERS */
require_once __DIR__ . "/../../helpers/format.php";
require_once __DIR__ . "/../../helpers/fechas.php";

/* CSS */
$extraCSS = '
<link rel="stylesheet" href="' . BASE_URL . '/assets/css/modules/seguimientos/seguimientos.css">
';

if (!tienePermiso('gestionar_seguimientos')) {
    header("Location: ../dashboard.php");
    exit;
}

/* =========================
   DATA
========================= */
$data = obtenerResumenSeguimientosMes();

$seguimientosMes = $data['seguimientosMes'] ?? [];
$totalActivos = $data['totalActivos'] ?? 0;
$totalConSeguimiento = $data['totalConSeguimiento'] ?? 0;
$totalSinSeguimiento = $data['totalSinSeguimiento'] ?? 0;
$porcentaje = $data['porcentaje'] ?? 0;
$color = $data['color'] ?? '';
$mesTexto = $data['mesTexto'] ?? '';

/* =========================
   ALERTAS (jóvenes sin seguimiento)
========================= */
$alertas = $pdo->query("
    SELECT j.id, j.nombre_completo, j.telefono, j.genero
    FROM jovenes j
    WHERE j.estado_actividad = 'ACTIVO'
    AND j.id NOT IN (
        SELECT joven_id
        FROM seguimientos
        WHERE DATE_FORMAT(fecha_contacto, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')
    )
")->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . "/../../includes/header.php";
?>

<div class="dashboard__section">

<h2>📋 Consolidado de Seguimientos</h2>
<p>Mes: <strong><?= e($mesTexto) ?></strong></p>

<!-- =========================
     KPI
========================= -->
<div class="dashboard__cards">

    <div class="dashboard__card dashboard__card--blue">
        <div class="dashboard__card-title">Total Activos</div>
        <div class="dashboard__card-value"><?= $totalActivos ?></div>
    </div>

    <div class="dashboard__card dashboard__card--green">
        <div class="dashboard__card-title">Con Seguimiento</div>
        <div class="dashboard__card-value"><?= $totalConSeguimiento ?></div>
    </div>

    <div class="dashboard__card dashboard__card--red">
        <div class="dashboard__card-title">Sin Seguimiento</div>
        <div class="dashboard__card-value"><?= $totalSinSeguimiento ?></div>
    </div>

    <div class="dashboard__card <?= $color === 'ok' ? 'dashboard__card--green' : ($color === 'warn' ? 'dashboard__card--orange' : 'dashboard__card--red') ?>">
        <div class="dashboard__card-title">Cumplimiento</div>
        <div class="dashboard__card-value"><?= $porcentaje ?>%</div>
    </div>

</div>

<!-- =========================
     JÓVENES SIN SEGUIMIENTO
========================= -->
<?php if (count($alertas) > 0): ?>

<div class="bloque-scroll">

    <div class="bloque-header">
        <h3>Jóvenes sin seguimiento</h3>
        <input type="text" class="buscador" placeholder="Buscar joven...">
    </div>

    <div class="bloque-body">

        <table class="tabla">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Teléfono</th>
                    <th>Acción</th>
                </tr>
            </thead>

            <tbody>

            <?php foreach($alertas as $j): ?>
                <tr>
                    <td><?= e($j["nombre_completo"]) ?></td>
                    <td><?= e($j["telefono"] ?? "-") ?></td>

                    <td>
                        <a href="../jovenes/ver.php?id=<?= $j["id"] ?>"
                           class="btn-mini <?= $j["genero"] === 'MASCULINO' ? 'chico' : 'chica' ?>">
                           Ver perfil
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>

            </tbody>
        </table>

    </div>
</div>

<?php else: ?>
    <p class="ok">✅ Todos tienen seguimiento este mes</p>
<?php endif; ?>

<!-- =========================
     DETALLE SEGUIMIENTOS
========================= -->
<div class="bloque-scroll">

    <div class="bloque-header">
        <h3>Detalle de Seguimientos</h3>
        <input type="text" class="buscador" placeholder="Buscar...">
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
                    <td><?= e($s["nombre_completo"]) ?></td>
                    <td><?= e($s["modalidad_contacto"]) ?></td>

                    <td>
                        <span class="estado <?= strtolower($s["estado_proceso"]) ?>">
                            <?= e($s["estado_proceso"]) ?>
                        </span>
                    </td>

                    <td><?= e($s["responsable_nombre"] ?? "-") ?></td>
                    <td><?= formatearFecha($s["fecha_contacto"]) ?></td>
                </tr>
            <?php endforeach; ?>

            </tbody>
        </table>

    </div>
</div>

<!-- =========================
     BOTONES
========================= -->
<div class="btn-group">

    <a href="../dashboard.php" class="btn btn-pdf">
         Volver
    </a>

    <a href="reporte_pdf.php" target="_blank" class="btn btn-pdf">
        📄 Descargar PDF
    </a>

</div>

</div>

<!-- =========================
     BUSCADOR JS
========================= -->
<script>
document.querySelectorAll(".buscador").forEach(input => {

    input.addEventListener("keyup", function(){

        let filtro = this.value.toLowerCase();
        let filas = this.closest(".bloque-scroll").querySelectorAll("tbody tr");

        filas.forEach(fila => {
            let texto = fila.innerText.toLowerCase();
            fila.style.display = texto.includes(filtro) ? "" : "none";
        });

    });

});
</script>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>