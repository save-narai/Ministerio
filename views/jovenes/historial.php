<?php

require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/permiso.php";
require_once __DIR__ . "/../../config/conexion.php";
require_once __DIR__ . "/../../middleware/actividad.php";


if (!tienePermiso('gestionar_jovenes')) {

    $_SESSION["error"] = "Acceso denegado";

    header("Location: ../dashboard.php");

    exit;
}

/* =========================
   ID
========================= */

$joven_id = isset($_GET["id"])
    ? (int) $_GET["id"]
    : 0;

if ($joven_id <= 0) {

    $_SESSION["error"] = "Joven inválido";

    header("Location: index.php");

    exit;
}

/* =========================
   DATOS JOVEN
========================= */

$stmt = $pdo->prepare("
    SELECT
        nombre_completo
    FROM jovenes
    WHERE id = :id
");

$stmt->execute([
    "id" => $joven_id
]);

$joven = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$joven) {

    $_SESSION["error"] = "Joven no encontrado";

    header("Location: index.php");

    exit;
}

/* =========================
   HISTORIAL
========================= */

$stmt = $pdo->prepare("
    SELECT

        r.tipo,

        r.fecha,

        a.asistio

    FROM asistencia a

    INNER JOIN reuniones r
        ON a.reunion_id = r.id

    WHERE a.joven_id = :id

    ORDER BY r.fecha DESC
");

$stmt->execute([
    "id" => $joven_id
]);

$historial = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   RESUMEN
========================= */

$total = count($historial);

$presentes = count(
    array_filter(
        $historial,
        fn($h) => (int)$h["asistio"] === 1
    )
);

$ausencias = $total - $presentes;

$porcentaje = $total > 0
    ? round(($presentes / $total) * 100)
    : 0;


/* =========================
   ESTADO CONEXION REAL
========================= */

$con = estadoConexionJoven($joven_id);

$estadoConexion =
    $con["icono"] . " " .
    $con["estado"];

$claseRiesgo = match($con["color"]) {

    "danger" => "alto",

    "warning" => "riesgo",

    default => "ok"
};

$faltasConsecutivas =
    faltasConsecutivasConexion($joven_id);


/* =========================
   CSS
========================= */

$extraCSS = '
<link rel="stylesheet" href="' . BASE_URL . '/assets/css/modules/jovenes/historial.css">
';

require_once __DIR__ . "/../../includes/header.php";

?>

<div class="historial-container">

    <!-- HEADER -->
    <div class="historial-header">

        <div>

            <h2>
                📊 Historial de
                <?= htmlspecialchars($joven["nombre_completo"]) ?>
            </h2>

            <span class="estado-conexion">
                <?= $estadoConexion ?>
            </span>

        </div>

        <a href="<?= BASE_URL ?>/views/jovenes/index.php"
           class="btn-volver">

            ⬅ Volver

        </a>

    </div>

    <!-- RESUMEN -->
    <div class="historial-resumen">

        <div class="resumen-card resumen-total">

            <span class="resumen-numero">
                <?= $total ?>
            </span>

            <span class="resumen-label">
                Registros
            </span>

        </div>

        <div class="resumen-card resumen-ok">

            <span class="resumen-numero">
                <?= $presentes ?>
            </span>

            <span class="resumen-label">
                Presentes
            </span>

        </div>

        <div class="resumen-card resumen-no">

            <span class="resumen-numero">
                <?= $ausencias ?>
            </span>

            <span class="resumen-label">
                Ausencias
            </span>

        </div>

        <div class="resumen-card resumen-porcentaje">

            <span class="resumen-numero">
                <?= $porcentaje ?>%
            </span>

            <span class="resumen-label">
                Asistencia
            </span>

        </div>

    </div>

    <!-- ALERTA -->
   <?php if ($faltasConsecutivas >= 3): ?>

    <div class="alerta-riesgo">

        🚨 Este joven tiene
<?= $faltasConsecutivas ?>
faltas consecutivas y necesita seguimiento

    </div>

    <?php endif; ?>

    <!-- TABLA -->
    <div class="card historial-card">

        <?php if (count($historial) > 0): ?>

        <table id="tablaHistorial" class="tabla display">

            <thead>

            <tr>

                <th>Tipo</th>

                <th>Fecha</th>

                <th>Estado</th>

            </tr>

            </thead>

            <tbody>

            <?php foreach ($historial as $h): ?>

            <tr>

                <td>
                    <?= htmlspecialchars($h["tipo"]) ?>
                </td>

                <td>
                    <?= date(
                        'd/m/Y',
                        strtotime($h["fecha"])
                    ) ?>
                </td>

                <td>

                    <?php if($h["asistio"]): ?>

                        <span class="badge-ok">
                            ✅ Presente
                        </span>

                    <?php else: ?>

                        <span class="badge-no">
                            ❌ Ausente
                        </span>

                    <?php endif; ?>

                </td>

            </tr>

            <?php endforeach; ?>

            </tbody>

        </table>

        <?php else: ?>

        <div class="empty-state">

            <div class="empty-icon">
                📭
            </div>

            <h3>
                No hay asistencias registradas
            </h3>

            <p>
                Este joven aún no tiene historial.
            </p>

        </div>

        <?php endif; ?>

    </div>

</div>

<script>

document.addEventListener("DOMContentLoaded", () => {

    if (
        typeof $ !== "undefined"
        &&
        $.fn.DataTable
    ) {

        $('#tablaHistorial').DataTable({

            pageLength: 8,

            order: [[1, 'desc']],

            language: {

                info: "Mostrando _START_ a _END_ de _TOTAL_ registros",

                paginate: {
                    previous: "←",
                    next: "→"
                }
            }
        });
    }

});

</script>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>