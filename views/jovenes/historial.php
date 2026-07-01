<?php

require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/permiso.php";
require_once __DIR__ . "/../../config/conexion.php";
require_once __DIR__ . "/../../services/actividadService.php";

if (!tienePermiso('gestionar_jovenes')) {

    $_SESSION["error"] = "Acceso denegado";

    header("Location: ../dashboard.php");

    exit;
}

/* =========================
   ACTUALIZAR ACTIVIDAD
========================= */

actualizarEstadoActividad($pdo);

/* =========================
   ID
========================= */

$joven_id = (int)($_GET["id"] ?? 0);

if ($joven_id <= 0) {

    $_SESSION["error"] = "Joven inválido";

    header("Location:index.php");

    exit;
}

/* =========================
   JOVEN
========================= */

$stmt = $pdo->prepare("
    SELECT
        id,
        nombre_completo
    FROM jovenes
    WHERE id = ?
");

$stmt->execute([$joven_id]);

$joven = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$joven) {

    $_SESSION["error"] = "Joven no encontrado";

    header("Location:index.php");

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
ON r.id = a.reunion_id

WHERE a.joven_id = ?

ORDER BY r.fecha DESC
");

$stmt->execute([$joven_id]);

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

$porcentaje = $total
    ? round(($presentes / $total) * 100)
    : 0;

/* =========================
   CONEXIÓN
========================= */

$con = estadoConexionJoven(
    $pdo,
    $joven_id
);

$estadoConexion = $con["estado"];

$claseConexion = match($con["color"]) {

    "danger" => "conexion-danger",

    "warning" => "conexion-warning",

    default => "conexion-ok"
};

$faltasConsecutivas =
    faltasConsecutivasConexion(
        $pdo,
        $joven_id
    );

/* =========================
   CSS
========================= */

$extraCSS = '

<link rel="stylesheet"
href="' . BASE_URL . '/assets/css/modules/jovenes/historial.css">

';

require_once __DIR__ . "/../../includes/header.php";

?>

<div class="page">

    <!-- =====================================================
       HEADER
    ===================================================== -->

    <div class="page-header">

        <div class="page-header-left">

            <h1 class="page-title">

                Historial de Asistencia

            </h1>

            <div class="page-subtitle">

                <?= htmlspecialchars($joven["nombre_completo"]) ?>

            </div>

        </div>

        <div class="page-header-right">

            <span class="perfil-conexion <?= $claseConexion ?>">

                <i class="fa-solid fa-circle"></i>

                <?= $estadoConexion ?>

            </span>

            <a
                href="<?= BASE_URL ?>/views/jovenes/ver.php?id=<?= $joven_id ?>"
                class="btn btn-secondary"
            >

                <i class="fa-solid fa-arrow-left"></i>

                Volver al perfil

            </a>

        </div>

    </div>

    <!-- =====================================================
       STATS
    ===================================================== -->

    <div class="gx-stats">

        <div class="gx-stat-card">

            <span class="gx-stat-value">

                <?= $total ?>

            </span>

            <span class="gx-stat-label">

                Registros

            </span>

        </div>

        <div class="gx-stat-card">

            <span class="gx-stat-value">

                <?= $presentes ?>

            </span>

            <span class="gx-stat-label">

                Presentes

            </span>

        </div>

        <div class="gx-stat-card">

            <span class="gx-stat-value">

                <?= $ausencias ?>

            </span>

            <span class="gx-stat-label">

                Ausencias

            </span>

        </div>

        <div class="gx-stat-card">

            <span class="gx-stat-value">

                <?= $porcentaje ?>%

            </span>

            <span class="gx-stat-label">

                Asistencia

            </span>

        </div>

    </div>


    <!-- =====================================================
       ALERTA
    ===================================================== -->

    <?php if ($faltasConsecutivas >= 3): ?>

        <div class="page-section">

            <div class="alert alert-warning">

                <i class="fa-solid fa-triangle-exclamation"></i>

                <div>

                    <strong>
                        Riesgo de desconexión
                    </strong>

                    <p>

                        Este joven acumula

                        <strong>

                            <?= $faltasConsecutivas ?>

                        </strong>

                        faltas consecutivas. Se recomienda realizar un seguimiento.

                    </p>

                </div>

            </div>

        </div>

    <?php endif; ?>



    <!-- =====================================================
       HISTORIAL
    ===================================================== -->

    <div class="page-section">

   <div class="section-header">

    <h3>

        Historial de reuniones

    </h3>

    <input
        type="text"
        id="buscadorHistorial"
        class="search-input"
        placeholder="Buscar reunión..."
    >

</div>


        <?php if (!empty($historial)): ?>



            <div class="table-wrapper">

                <table
                    id="tablaHistorial"
                    class="table"
                >

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

                                    <?php

                                    $tipo = match($h["tipo"]) {

                                        "REUNION_JOVENES" => "Reunión",

                                        "GRUPO_CONEXION" => "Grupo Conexión",

                                        "DISCIPULADO" => "Discipulado",

                                        "EVENTO_ESPECIAL" => "Evento",

                                        default => ucfirst(
                                            strtolower(
                                                str_replace(
                                                    "_",
                                                    " ",
                                                    $h["tipo"]
                                                )
                                            )
                                        )

                                    };

                                    ?>

                                    <?= $tipo ?>

                                </td>

                                <td>

                                    <?= date(
                                        "d/m/Y",
                                        strtotime($h["fecha"])
                                    ) ?>

                                </td>

                                <td>

                                    <?php if ((int)$h["asistio"] === 1): ?>

                                        <span class="badge badge-success">

                                            <i class="fa-solid fa-check"></i>

                                            Presente

                                        </span>

                                    <?php else: ?>

                                        <span class="badge badge-danger">

                                            <i class="fa-solid fa-xmark"></i>

                                            Ausente

                                        </span>

                                    <?php endif; ?>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>



        <?php else: ?>



            <div class="empty-state">

                <i class="fa-solid fa-calendar-xmark"></i>

                <h3>

                    No existen registros

                </h3>

                <p>

                    Este joven aún no tiene asistencias registradas.

                </p>

            </div>

        <?php endif; ?>

    </div>

    <!-- =====================================================
       BOTONES
    ===================================================== -->

    <div class="btn-group">

        <a
            href="<?= BASE_URL ?>/views/jovenes/ver.php?id=<?= $joven_id ?>"
            class="btn btn-secondary"
        >

            <i class="fa-solid fa-arrow-left"></i>

            Volver al perfil

        </a>

        <a
            href="<?= BASE_URL ?>/views/jovenes/index.php"
            class="btn btn-primary"
        >

            <i class="fa-solid fa-users"></i>

            Todos los jóvenes

        </a>

    </div>

</div>

<!-- =====================================================
   JAVASCRIPT
===================================================== -->

<script
    src="<?= BASE_URL ?>/assets/js/modulos/jovenes/historial.js">
</script>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>