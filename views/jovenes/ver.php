<?php

require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/permiso.php";
require_once __DIR__ . "/../../config/conexion.php";
require_once __DIR__ . "/../../middleware/actividad.php";

/* =========================
   ACTUALIZAR ACTIVIDAD
========================= */

actualizarEstadoActividad();

/* =========================
   PERMISOS
========================= */

if (!tienePermiso('gestionar_jovenes')) {

    $_SESSION["error"] = "No tienes permiso";

    header("Location: ../dashboard.php");

    exit;
}

/* =========================
   VALIDAR ID
========================= */

$id = isset($_GET["id"])
    ? (int) $_GET["id"]
    : 0;

if ($id <= 0) {

    $_SESSION["error"] = "ID inválido";

    header("Location: index.php");

    exit;
}

/* =========================
   CSS
========================= */

$extraCSS = '
<link rel="stylesheet" href="' . BASE_URL . '/assets/css/modules/jovenes/ver.css">
';

/* =========================
   DATOS JOVEN
========================= */

$stmt = $pdo->prepare("
    SELECT *
    FROM jovenes
    WHERE id = :id
");

$stmt->execute([
    "id" => $id
]);

$joven = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$joven) {

    $_SESSION["error"] = "Joven no encontrado";

    header("Location: index.php");

    exit;
}

/* =========================
   CLASE GÉNERO
========================= */

$claseGenero = ($joven["genero"] ?? '') === "F"
    ? "perfil-chica"
    : "perfil-chico";

/* =========================
   EDAD
========================= */

$edad = "—";

$edadAprox = false;

if (!empty($joven["fecha_nacimiento"])) {

    $edad = (new DateTime(
        $joven["fecha_nacimiento"]
    ))->diff(new DateTime())->y;

} elseif (!empty($joven["edad_manual"])) {

    $edad = (int)$joven["edad_manual"];

    $edadAprox = true;
}

/* =========================
   RESUMEN ASISTENCIA
========================= */

$stmt = $pdo->prepare("
    SELECT

        SUM(asistio = 1) AS presentes,

        SUM(asistio = 0) AS ausentes

    FROM asistencia

    WHERE joven_id = :joven_id
");

$stmt->execute([
    "joven_id" => $id
]);

$resumen = $stmt->fetch(PDO::FETCH_ASSOC);

$presentes = (int)($resumen["presentes"] ?? 0);

$ausentes = (int)($resumen["ausentes"] ?? 0);

$totalAsistencia = $presentes + $ausentes;

$porcentaje = $totalAsistencia > 0
    ? round(($presentes / $totalAsistencia) * 100)
    : 0;

/* =========================
   ESTADO CONEXIÓN REAL
========================= */

$con = estadoConexionJoven($id);

$estadoConexion = $con["estado"];

$claseConexion = match($con["color"]) {

    "danger" => "conexion-danger",

    "warning" => "conexion-warning",

    default => "conexion-ok"
};

$faltasConsecutivas =
    faltasConsecutivasConexion($id);

/* =========================
   TOTAL SEGUIMIENTOS
========================= */

$stmt = $pdo->prepare("
    SELECT COUNT(*)

    FROM seguimientos

    WHERE joven_id = :id
");

$stmt->execute([
    "id" => $id
]);

$totalSeguimientos =
    (int)$stmt->fetchColumn();

/* =========================
   ÚLTIMOS SEGUIMIENTOS
========================= */

$stmt = $pdo->prepare("
    SELECT

        s.*,

        u.nombre AS responsable_nombre

    FROM seguimientos s

    LEFT JOIN usuarios u
        ON s.responsable_id = u.id

    WHERE s.joven_id = :joven_id

    ORDER BY s.fecha_contacto DESC

    LIMIT 5
");

$stmt->execute([
    "joven_id" => $id
]);

$seguimientos =
    $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   ÚLTIMO SEGUIMIENTO
========================= */

$ultimoSeguimiento =
    $seguimientos[0] ?? null;

?>

<?php require_once __DIR__ . "/../../includes/header.php"; ?>

<!-- =========================
     PERFIL
========================= -->

<div class="perfil-card <?= $claseGenero ?>">

    <!-- HEADER -->

    <div class="perfil-header">

        <div>

            <h2>

                <?= htmlspecialchars(
                    $joven["nombre_completo"]
                ) ?>

            </h2>

            <span class="perfil-conexion <?= $claseConexion ?>">

                <?= $estadoConexion ?>

            </span>

        </div>

        <?php

        $estadoActividad =
            $joven["estado_actividad"] ?? '';

        ?>

        <?php if($estadoActividad === "ACTIVO"): ?>

            <span class="badge-activo">
                Activo
            </span>

        <?php elseif($estadoActividad === "INACTIVO"): ?>

            <span class="badge-inactivo">
                Inactivo
            </span>

        <?php else: ?>

            <span class="badge-eliminado">
                Eliminado
            </span>

        <?php endif; ?>

    </div>

    <!-- GRID -->

    <div class="perfil-grid">

        <div>

            <strong>Edad:</strong>

            <?= $edad ?>

            <?= $edadAprox ? '(aprox)' : '' ?>

        </div>

        <div>

            <strong>Género:</strong>

            <?= htmlspecialchars(
                $joven["genero"] ?? "—"
            ) ?>

        </div>

        <div>

            <strong>Teléfono:</strong>

            <?= htmlspecialchars(
                $joven["telefono"] ?? "—"
            ) ?>

        </div>

        <div>

            <strong>Estado Espiritual:</strong>

            <?= ucfirst(strtolower(
                htmlspecialchars(
                    $joven["estado_espiritual"] ?? "—"
                )
            )) ?>

        </div>

    </div>

    <!-- STATS -->

    <div class="perfil-stats">

        <div class="stat-card presente">

            <span class="stat-number">
                <?= $presentes ?>
            </span>

            <span class="stat-label">
                Presentes
            </span>

        </div>

        <div class="stat-card ausente">

            <span class="stat-number">
                <?= $ausentes ?>
            </span>

            <span class="stat-label">
                Ausencias
            </span>

        </div>

        <div class="stat-card porcentaje">

            <span class="stat-number">
                <?= $porcentaje ?>%
            </span>

            <span class="stat-label">
                Asistencia
            </span>

        </div>

        <div class="stat-card seguimiento">

            <span class="stat-number">
                <?= $totalSeguimientos ?>
            </span>

            <span class="stat-label">
                Seguimientos
            </span>

        </div>

    </div>

    <!-- =========================
         OBSERVACIONES GENERALES
    ========================= -->

    <div class="perfil-obs">

        <strong>
            Observaciones generales
        </strong>

        <p>

            <?= nl2br(
                htmlspecialchars(
                    $joven["observaciones"]
                    ?? "Sin observaciones"
                )
            ) ?>

        </p>

    </div>

    <!-- =========================
         ESTADO CONSOLIDACIÓN
    ========================= -->

    <?php if($ultimoSeguimiento): ?>

    <div class="perfil-obs">

        <strong>
            Estado de consolidación
        </strong>

        <p>

            <span class="estado <?= strtolower(
                str_replace(
                    '_',
                    '-',
                    $ultimoSeguimiento["estado_proceso"]
                )
            ) ?>">

                <?= ucfirst(strtolower(
                    str_replace(
                        '_',
                        ' ',
                        $ultimoSeguimiento["estado_proceso"]
                    )
                )) ?>

            </span>

        </p>

    </div>

    <?php endif; ?>

</div>

<!-- =========================
     ÚLTIMOS SEGUIMIENTOS
========================= -->

<div class="card">

    <div class="section-header">

        <h3>
            Últimos Seguimientos
        </h3>

        <a
            href="<?= BASE_URL ?>/views/seguimientos/index.php?joven_id=<?= $id ?>"
            class="btn-mini">

            Ver todos

        </a>

    </div>

    <?php if(count($seguimientos) > 0): ?>

    <div class="timeline">

        <?php foreach($seguimientos as $s): ?>

        <div class="timeline-item">

            <div class="timeline-dot"></div>

            <div class="timeline-content">

                <div class="timeline-header">

                    <strong>

                        <?= ucfirst(strtolower(
                            htmlspecialchars(
                                $s["modalidad_contacto"]
                            )
                        )) ?>

                    </strong>

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
                                htmlspecialchars(
                                    $s["estado_proceso"]
                                )
                            )
                        )) ?>

                    </span>

                </div>

                <div class="timeline-meta">

                    <span>

                        <i class="fa-solid fa-calendar"></i>

                        <?= htmlspecialchars(
                            $s["fecha_contacto"]
                        ) ?>

                    </span>

                    <span>

                        <i class="fa-solid fa-user"></i>

                        <?= htmlspecialchars(
                            $s["responsable_nombre"] ?? "—"
                        ) ?>

                    </span>

                </div>

                <div class="timeline-body">

                    <?= nl2br(
                        htmlspecialchars(
                            $s["observaciones"]
                            ?? "Sin observaciones"
                        )
                    ) ?>

                </div>

            </div>

        </div>

        <?php endforeach; ?>

    </div>

    <?php else: ?>

        <p class="text-center">

            No hay seguimientos registrados

        </p>

    <?php endif; ?>

</div>

<!-- =========================
     ACCIONES
========================= -->

<div class="btn-group">

    <a
        href="<?= BASE_URL ?>/views/jovenes/index.php"
        class="btn">

        Volver

    </a>

    <a
        href="<?= BASE_URL ?>/views/seguimientos/crear.php?id=<?= $joven['id'] ?>"
        class="btn btn-seguimiento">

        <i class="fa-solid fa-user-plus"></i>

        Registrar seguimiento

    </a>

    <a
        href="<?= BASE_URL ?>/views/jovenes/perfil_pdf.php?id=<?= $joven['id'] ?>"
        target="_blank"
        class="btn btn-pdf <?= ($joven['genero'] ?? '') === 'M' ? 'chico' : 'chica' ?>">

        <i class="fa-solid fa-file-pdf"></i>

        Descargar PDF

    </a>

</div>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>