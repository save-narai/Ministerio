<?php

require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/permiso.php";
require_once __DIR__ . "/../../services/actividadService.php";
require_once __DIR__ . "/../../config/conexion.php";
require_once __DIR__ . "/../../helpers/format.php";
require_once __DIR__ . "/../../helpers/fechas.php";

/* =========================
   ACTIVIDAD
========================= */

actualizarEstadoActividad($pdo);

/* =========================
   PERMISOS
========================= */

if (!tienePermiso('gestionar_jovenes')) {

    $_SESSION["error"] = "No tienes permiso.";

    header("Location: ../dashboard.php");

    exit;
}

/* =========================
   ID
========================= */

$id = (int) ($_GET["id"] ?? 0);

if ($id <= 0) {

    header("Location: index.php");

    exit;
}

/* =========================
   JOVEN
========================= */

$stmt = $pdo->prepare("
    SELECT
        id,
        nombre_completo,
        telefono,
        genero,
        fecha_nacimiento,
        edad_manual,
        fecha_actualizacion_edad,
        estado_espiritual,
        estado_actividad,
        observaciones,
        fecha_ingreso
    FROM jovenes
    WHERE id = :id
    LIMIT 1
");

$stmt->execute([
    ':id' => $id
]);

$joven = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$joven) {

    $_SESSION["error"] = "Joven no encontrado.";

    header("Location: index.php");

    exit;
}

/* =========================
   DATOS PREPARADOS
========================= */

$nombre = e($joven["nombre_completo"]);

$telefono = e(
    $joven["telefono"] ?: "—"
);

$observaciones = e(
    $joven["observaciones"]
    ?? "Sin observaciones"
);

$genero = match ($joven["genero"] ?? '') {

    "M" => "Masculino",

    "F" => "Femenino",

    default => "—"

};

$estadoEspiritual = ucfirst(
    strtolower(
        $joven["estado_espiritual"] ?? "—"
    )
);

/* =========================
   GÉNERO PERFIL
========================= */

$claseGenero =
    ($joven["genero"] ?? '') === "F"

    ? "perfil-chica"

    : "perfil-chico";

/* =========================
   EDAD
========================= */

$edad = "—";

$edadAprox = false;

if (!empty($joven["fecha_nacimiento"])) {

    $edad = (
        new DateTime($joven["fecha_nacimiento"])
    )->diff(new DateTime())->y;

} elseif (!empty($joven["edad_manual"])) {

    $edad = (int) $joven["edad_manual"];

    if (!empty($joven["fecha_actualizacion_edad"])) {

        $edad += (
            new DateTime($joven["fecha_actualizacion_edad"])
        )->diff(new DateTime())->y;
    }

    $edadAprox = true;
}                                                                                                                                                       /* =========================
   ASISTENCIA
========================= */

$stmt = $pdo->prepare("
    SELECT

        SUM(asistio = 1) AS presentes,

        SUM(asistio = 0) AS ausentes

    FROM asistencia

    WHERE joven_id = :id
");

$stmt->execute([
    ':id' => $id
]);

$asistencia = $stmt->fetch(PDO::FETCH_ASSOC);

$presentes = (int) ($asistencia["presentes"] ?? 0);

$ausentes = (int) ($asistencia["ausentes"] ?? 0);

$total = $presentes + $ausentes;

$porcentaje = $total > 0

    ? round(($presentes / $total) * 100)

    : 0;

/* =========================
   CONEXIÓN
========================= */

$con = estadoConexionJoven(
    $pdo,
    $id
);

$estadoConexion = $con["estado"];

$claseConexion = match ($con["color"]) {

    "danger"  => "conexion-danger",

    "warning" => "conexion-warning",

    default   => "conexion-ok"
};

/* =========================
   SEGUIMIENTOS
========================= */

$stmt = $pdo->prepare("
    SELECT

        s.id,
        s.fecha_contacto,
        s.modalidad_contacto,
        s.estado_proceso,
        s.observaciones,

        u.nombre AS responsable_nombre

    FROM seguimientos s

    LEFT JOIN usuarios u
        ON s.responsable_id = u.id

    WHERE s.joven_id = :id

    ORDER BY s.fecha_contacto DESC

    LIMIT 5
");

$stmt->execute([
    ':id' => $id
]);

$seguimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalSeguimientos = count($seguimientos);

$ultimoSeguimiento = $seguimientos[0] ?? null;

/* =========================
   PERFIL
========================= */

$fechaIngreso = "—";

if (!empty($joven["fecha_ingreso"])) {

    $fechaIngreso = formatearFecha(
        $joven["fecha_ingreso"]
    );
}

$datosPerfil = [

    "Edad" =>
        $edad . ($edadAprox ? " (aprox.)" : ""),

    "Género" =>
        $genero,

    "Teléfono" =>
        $telefono,

    "Estado espiritual" =>
        $estadoEspiritual,

    "Fecha de ingreso" =>
        $fechaIngreso

];

/* =========================
   HEADER
========================= */

require_once __DIR__ . "/../../includes/header.php";

?>

<div class="ver-wrapper">

<<<<<<< HEAD
    <div class="perfil-card <?= $claseGenero ?>">

        <header class="perfil-header">

            <div class="perfil-header-info">

                <h2>

                    <?= $nombre ?>

                </h2>

                <span class="perfil-conexion <?= $claseConexion ?>">

                    <i class="fa-solid fa-circle"></i>

                    <?= e($estadoConexion) ?>

                </span>

            </div>

            <?php

            $badgeClase = match ($joven["estado_actividad"]) {

                "ACTIVO"    => "badge-activo",

                "INACTIVO"  => "badge-inactivo",

                "ELIMINADO" => "badge-eliminado",

                default     => "badge-inactivo"
            };

            ?>

            <span class="badge <?= $badgeClase ?>">

                <?= ucfirst(
                    strtolower(
                        e($joven["estado_actividad"])
                    )
                ) ?>

            </span>

        </header>

        <div class="perfil-grid">

            <?php foreach ($datosPerfil as $titulo => $valor): ?>

                <div>

                    <strong>

                        <?= e($titulo) ?>

                    </strong>

                    <?= $valor ?>

                </div>

            <?php endforeach; ?>

        </div>                                                                                                                                           <div class="perfil-stats">

            <div class="perfil-stat-card presente">

                <b><?= $presentes ?></b>

                <span>Presentes</span>

            </div>

            <div class="perfil-stat-card ausente">

                <b><?= $ausentes ?></b>

                <span>Ausencias</span>

            </div>

            <div class="perfil-stat-card porcentaje">

                <b><?= $porcentaje ?>%</b>

                <span>Asistencia</span>

            </div>

            <div class="perfil-stat-card seguimiento">

                <b><?= $totalSeguimientos ?></b>

                <span>Seguimientos</span>

            </div>

        </div>

        <div class="perfil-obs">

            <strong>

                Observaciones generales

            </strong>

            <p>

                <?= nl2br($observaciones) ?>
=======
    <!-- =====================================================
         PERFIL
    ====================================================== -->

    <section class="perfil-card <?= $claseGenero ?>">

        <div class="gx-profile">

            <!-- =============================================
                 CABECERA
            ============================================== -->

            <header class="gx-profile__header">

                <div class="gx-profile__identity">

                    <h1 class="gx-profile__name">

                        <?= $nombre ?>

                    </h1>

                    <div class="gx-profile__status">

                        <span class="perfil-conexion <?= $claseConexion ?>">

                            <i class="fa-solid fa-circle"></i>

                            <?= e($estadoConexion) ?>

                        </span>

                        <?php

                        $badgeClase = match ($joven["estado_actividad"]) {

                            "ACTIVO"    => "badge-activo",

                            "INACTIVO"  => "badge-inactivo",

                            "ELIMINADO" => "badge-eliminado",

                            default     => "badge-inactivo"

                        };

                        ?>

                        <span class="badge <?= $badgeClase ?>">

                            <?= ucfirst(
                                strtolower(
                                    e($joven["estado_actividad"])
                                )
                            ) ?>

                        </span>

                    </div>

                </div>

            </header>

            <!-- =============================================
                 INFORMACIÓN PERSONAL
            ============================================== -->

            <section class="gx-profile__section">

                <h2 class="gx-profile__section-title">

                    Información personal

                </h2>

                <div class="gx-profile__rows">

                    <article class="gx-profile__row">

                        <span class="gx-profile__label">

                            Edad

                        </span>

                        <strong class="gx-profile__value">

                            <?= $edad ?>

                            <?= $edadAprox ? "(aprox.)" : "" ?>

                        </strong>

                    </article>

                    <article class="gx-profile__row">

                        <span class="gx-profile__label">

                            Género

                        </span>

                        <strong class="gx-profile__value">

                            <?= $genero ?>

                        </strong>

                    </article>

                    <article class="gx-profile__row">

                        <span class="gx-profile__label">

                            Teléfono

                        </span>

                        <strong class="gx-profile__value">

                            <?= $telefono ?>

                        </strong>

                    </article>

                    <article class="gx-profile__row">

                        <span class="gx-profile__label">

                            Estado espiritual

                        </span>

                        <span class="gx-profile__badge">

                            <?= $estadoEspiritual ?>

                        </span>

                    </article>

                    <article class="gx-profile__row">

                        <span class="gx-profile__label">

                            Fecha de ingreso

                        </span>

                        <strong class="gx-profile__value">

                            <?= $fechaIngreso ?>

                        </strong>

                    </article>

                </div>

            </section>

        </div>

    </section>    
    
    <!-- =====================================================
     OBSERVACIONES
====================================================== -->

<section class="ui-card">

    <div class="ui-card-header">

        <div>

            <h2 class="page-section-title">

                Observaciones

            </h2>

            <p class="page-subtitle">

                Información adicional registrada sobre este joven.
>>>>>>> 3e2d89c (Actualización del proyecto)

            </p>

        </div>

<<<<<<< HEAD
        <?php if ($ultimoSeguimiento): ?>

            <div class="perfil-obs">

                <strong>

                    Estado de consolidación

                </strong>
=======
    </div>

    <div class="ui-card-body">

        <?php if (!empty(trim($observaciones)) && $observaciones !== "Sin observaciones"): ?>

            <p class="gx-note">

                <?= nl2br($observaciones) ?>

            </p>

        <?php else: ?>

            <div class="gx-empty gx-empty--small">

                <i class="fa-solid fa-note-sticky"></i>

                <p>

                    No existen observaciones registradas.

                </p>

            </div>

        <?php endif; ?>

        <?php if ($ultimoSeguimiento): ?>

            <div class="gx-profile-status">

                <span class="title-sm">

                    Estado actual

                </span>
>>>>>>> 3e2d89c (Actualización del proyecto)

                <span class="estado <?= claseEstado($ultimoSeguimiento["estado_proceso"]) ?>">

                    <?= ucfirst(

                        strtolower(

                            str_replace(

                                "_",

                                " ",

                                e($ultimoSeguimiento["estado_proceso"])

                            )

                        )

                    ) ?>

                </span>

            </div>

        <?php endif; ?>

    </div>

<<<<<<< HEAD
    <!-- =====================================
         ÚLTIMOS SEGUIMIENTOS
    ====================================== -->

    <div class="card">

        <div class="section-header">

            <h3>

                Últimos Seguimientos

            </h3>

            <a

                href="<?= BASE_URL ?>/views/seguimientos/index.php?joven_id=<?= $id ?>"

                class="btn-mini"

            >

                <i class="fa-solid fa-list"></i>

                Ver todos

            </a>

        </div>

        <?php if ($seguimientos): ?>

            <div class="timeline">

                <?php foreach ($seguimientos as $s): ?>

                    <div class="timeline-item">

                        <div class="timeline-dot"></div>

                        <div class="timeline-content">

                            <div class="timeline-header">

                                <strong>
=======
</section>

<!-- =====================================================
     ÚLTIMOS SEGUIMIENTOS
====================================================== -->

<section class="ui-card">

    <div class="ui-card-header">

        <div>

            <h2 class="page-section-title">

                Últimos seguimientos

            </h2>

            <p class="page-subtitle">

                Historial reciente de acompañamiento.

            </p>

        </div>

        <a
            href="<?= BASE_URL ?>/views/seguimientos/index.php?joven_id=<?= $id ?>"
            class="btn btn-secondary"
        >

            <i class="fa-solid fa-list"></i>

            Ver todos

        </a>

    </div>

    <?php if ($seguimientos): ?>

        <div class="gx-timeline">

            <?php foreach ($seguimientos as $s): ?>

                <article class="gx-timeline__item">

                    <div class="gx-timeline__dot"></div>

                    <div class="gx-timeline__content">

                        <header class="gx-timeline__top">

                            <div>

                                <h4>
>>>>>>> 3e2d89c (Actualización del proyecto)

                                    <?= ucfirst(

                                        strtolower(

                                            e($s["modalidad_contacto"])

                                        )

                                    ) ?>

<<<<<<< HEAD
                                </strong>

                                <span class="estado <?= claseEstado($s["estado_proceso"]) ?>">

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

                            </div>

                            <div class="timeline-meta">

                                <span>

                                    <i class="fa-solid fa-calendar"></i>

                                    <?= formatearFecha($s["fecha_contacto"]) ?>

                                </span>

                                <span>

                                    <i class="fa-solid fa-user"></i>

                                    <?= e($s["responsable_nombre"] ?? "—") ?>

                                </span>

                            </div>

                            <p>

                                <?= nl2br(

                                    e(

                                        $s["observaciones"]

                                        ?? "Sin observaciones"
=======
                                </h4>

                                <small>

                                    <?= formatearFecha($s["fecha_contacto"]) ?>

                                </small>

                            </div>

                            <span class="estado <?= claseEstado($s["estado_proceso"]) ?>">

                                <?= ucfirst(

                                    strtolower(

                                        str_replace(

                                            "_",

                                            " ",

                                            e($s["estado_proceso"])

                                        )
>>>>>>> 3e2d89c (Actualización del proyecto)

                                    )

                                ) ?>

<<<<<<< HEAD
                            </p>

                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php else: ?>

            <div class="empty-state">

                <i class="fa-solid fa-clipboard-list"></i>

                <h4>

                    No hay seguimientos registrados

                </h4>

                <p>

                    Este joven aún no tiene seguimientos asociados.

                </p>

            </div>

        <?php endif; ?>

    </div>                                                                                                                                                    <!-- =====================================
         BOTONES
    ====================================== -->

    <div class="btn-group">

        <a
            href="<?= BASE_URL ?>/views/jovenes/index.php"
            class="btn btn-secondary"
        >

            <i class="fa-solid fa-arrow-left"></i>

            Volver

        </a>

        <a
            href="<?= BASE_URL ?>/views/jovenes/editar.php?id=<?= $id ?>"
            class="btn btn-warning"
        >

            <i class="fa-solid fa-pen"></i>

            Editar joven

        </a>

        <a
            href="<?= BASE_URL ?>/views/seguimientos/crear.php?id=<?= $id ?>"
            class="btn btn-primary btn-seguimiento"
=======
                            </span>

                        </header>

                        <div class="gx-timeline__meta">

                            <i class="fa-solid fa-user"></i>

                            <?= e($s["responsable_nombre"] ?? "Sin responsable") ?>

                        </div>

                        <p>

                            <?= nl2br(

                                e(

                                    $s["observaciones"]

                                    ?? "Sin observaciones"

                                )

                            ) ?>

                        </p>

                    </div>

                </article>

            <?php endforeach; ?>

        </div>

    <?php else: ?>

        <div class="gx-empty">

            <i class="fa-solid fa-clipboard-list"></i>

            <h3>

                Sin seguimientos registrados

            </h3>

            <p>

                Todavía no existen registros de seguimiento para este joven.

            </p>

        </div>

    <?php endif; ?>

</section>   


<!-- =====================================================
     ACCIONES
====================================================== -->

<section class="gx-actions">

    <div class="gx-actions__left">

        <a
            href="<?= BASE_URL ?>/views/seguimientos/crear.php?id=<?= $id ?>"
            class="btn btn-primary"
>>>>>>> 3e2d89c (Actualización del proyecto)
        >

            <i class="fa-solid fa-user-plus"></i>

            Registrar seguimiento

        </a>

<<<<<<< HEAD
        <a
            href="<?= BASE_URL ?>/views/jovenes/historial.php?id=<?= $id ?>"
            class="btn btn-info"
        >

            <i class="fa-solid fa-clock-rotate-left"></i>
=======
    </div>

    <div class="gx-actions__right">

        <a
            href="<?= BASE_URL ?>/views/jovenes/editar.php?id=<?= $id ?>"
            class="btn btn-primary"
        >

            

            Editar

        </a>

        <a
            href="<?= BASE_URL ?>/views/jovenes/historial.php?id=<?= $id ?>"
            class="btn btn-primary"
        >

            
>>>>>>> 3e2d89c (Actualización del proyecto)

            Historial

        </a>

        <a
<<<<<<< HEAD
            href="<?= BASE_URL ?>/views/jovenes/perfil_pdf.php?id=<?= $id ?>"
            target="_blank"
            class="btn btn-pdf <?= $claseGenero ?>"
        >

            <i class="fa-solid fa-file-pdf"></i>

            PDF
=======
    href="<?= BASE_URL ?>/views/jovenes/perfil_pdf.php?id=<?= $id ?>"
    target="_blank"
    class="btn btn-pdf <?= $claseGenero ?>"
>

            

            Descargar PDF

        </a>

        <a
            href="<?= BASE_URL ?>/views/jovenes/index.php"
            class="btn btn-primary"
        >

            

            Volver al listado
>>>>>>> 3e2d89c (Actualización del proyecto)

        </a>

    </div>

<<<<<<< HEAD
</div>
=======
</section>
>>>>>>> 3e2d89c (Actualización del proyecto)

<script
    src="<?= BASE_URL ?>/assets/js/modulos/jovenes/ver.js">
</script>

<?php

require_once __DIR__ . "/../../includes/footer.php";

?>