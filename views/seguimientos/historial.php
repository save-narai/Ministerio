<?php

declare(strict_types=1);

require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/permiso.php";
require_once __DIR__ . "/../../config/conexion.php";
require_once __DIR__ . "/../../services/actividadService.php";
require_once __DIR__ . "/../../services/seguimientoService.php";
require_once __DIR__ . "/../../helpers/format.php";
require_once __DIR__ . "/../../helpers/fechas.php";

/* ==========================================================
   PERMISOS
========================================================== */

if (!tienePermiso('gestionar_seguimientos')) {

    header("Location: ../dashboard.php");
    exit;
}


/* ==========================================================
   ACTUALIZAR ACTIVIDAD
========================================================== */

actualizarEstadoActividad($pdo);


/* ==========================================================
   VALIDAR JOVEN
========================================================== */

$jovenId = (int)($_GET['joven_id'] ?? 0);

if ($jovenId <= 0) {

    $_SESSION["error"] = "Joven inválido.";

    header("Location: index.php");
    exit;
}


/* ==========================================================
   OBTENER JOVEN
========================================================== */

$stmt = $pdo->prepare("
    SELECT
        id,
        nombre_completo,
        telefono,
        genero,
        estado_espiritual,
        estado_actividad
    FROM jovenes
    WHERE id = :id
    LIMIT 1
");

$stmt->execute([
    ':id' => $jovenId
]);

$joven = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$joven) {

    $_SESSION["error"] = "Joven no encontrado.";

    header("Location: index.php");
    exit;
}


/* ==========================================================
   HISTORIAL
========================================================== */

$seguimientos = obtenerSeguimientosPorJoven(
    $pdo,
    $jovenId
);


/* ==========================================================
   ESTADÍSTICAS
========================================================== */

$totalSeguimientos = count($seguimientos);

$pendientes = 0;
$enProceso = 0;
$finalizados = 0;

foreach ($seguimientos as $seguimiento) {

    switch ($seguimiento['estado_proceso']) {

        case 'PENDIENTE':
            $pendientes++;
            break;

        case 'EN_PROCESO':
            $enProceso++;
            break;

        case 'FINALIZADO':
            $finalizados++;
            break;
    }
}


/* ==========================================================
   HEADER
========================================================== */

require_once __DIR__ . "/../../includes/header.php";

?>

<div class="seguimientos-page">

    <!-- =====================================================
         HEADER
    ====================================================== -->

    <div class="page-header">

        <div class="page-header-left">

            <h1 class="page-title">
                Historial de seguimientos
            </h1>

            <p class="page-subtitle">

                Historial completo del acompañamiento realizado a
                <strong>
                    <?= e($joven['nombre_completo']) ?>
                </strong>

            </p>

        </div>


        <div class="page-header-right">

            <a
                href="<?= BASE_URL ?>/views/seguimientos/crear.php?id=<?= $jovenId ?>"
                class="btn btn-primary"
            >

                <i class="fa-solid fa-plus"></i>

                Nuevo seguimiento

            </a>

        </div>

    </div>


    <!-- =====================================================
         INFORMACIÓN DEL JOVEN
    ====================================================== -->

    <section class="gx-card gx-card--soft">

        <div class="gx-card__header">

            <div>

                <h2>
                    <?= e($joven['nombre_completo']) ?>
                </h2>

                <p>
                    Información del joven y resumen de seguimiento.
                </p>

            </div>


            <div>

                <span class="badge badge-info">

                    <?= e(
                        ucfirst(
                            strtolower(
                                $joven['estado_espiritual'] ?? 'Nuevo'
                            )
                        )
                    ) ?>

                </span>

            </div>

        </div>


        <div class="gx-card__body">

            <div class="gx-profile__rows">

                <article class="gx-profile__row">

                    <span class="gx-profile__label">
                        Teléfono
                    </span>

                    <span class="gx-profile__value">

                        <?= e(
                            $joven['telefono']
                            ?: 'No registrado'
                        ) ?>

                    </span>

                </article>


                <article class="gx-profile__row">

                    <span class="gx-profile__label">
                        Estado
                    </span>

                    <span class="gx-profile__value">

                        <?= e(
                            ucfirst(
                                strtolower(
                                    $joven['estado_actividad']
                                    ?? 'ACTIVO'
                                )
                            )
                        ) ?>

                    </span>

                </article>

            </div>

        </div>

    </section>


    <br>


    <!-- =====================================================
         ESTADÍSTICAS
    ====================================================== -->

    <section class="gx-stats">

        <div class="stat-card info">

            <span class="stat-number">
                <?= $totalSeguimientos ?>
            </span>

            <span class="stat-label">
                Total seguimientos
            </span>

        </div>


        <div class="stat-card danger">

            <span class="stat-number">
                <?= $pendientes ?>
            </span>

            <span class="stat-label">
                Pendientes
            </span>

        </div>


        <div class="stat-card warning">

            <span class="stat-number">
                <?= $enProceso ?>
            </span>

            <span class="stat-label">
                En proceso
            </span>

        </div>


        <div class="stat-card success">

            <span class="stat-number">
                <?= $finalizados ?>
            </span>

            <span class="stat-label">
                Finalizados
            </span>

        </div>

    </section>


    <br>


    <!-- =====================================================
         HISTORIAL
    ====================================================== -->

    <section class="page-section">

        <div class="section-header">

            <div>

                <h2 class="section-title">
                    Todos los seguimientos
                </h2>

                <p class="section-subtitle">
                    Registro cronológico del acompañamiento.
                </p>

            </div>

        </div>


        <?php if (!empty($seguimientos)): ?>

            <div class="gx-timeline">

                <?php foreach ($seguimientos as $s): ?>

                    <article class="gx-timeline__item">

                        <div class="gx-timeline__line"></div>

                        <div class="gx-timeline__dot"></div>


                        <div class="gx-timeline__content">

                            <!-- =========================
                                 CABECERA
                            ========================== -->

                            <div class="gx-timeline__top">

                                <div>

                                    <h4>

                                        <?= ucfirst(
                                            strtolower(
                                                e(
                                                    $s[
                                                        'modalidad_contacto'
                                                    ]
                                                )
                                            )
                                        ) ?>

                                    </h4>


                                    <small>

                                        <i class="fa-regular fa-calendar"></i>

                                        <?= formatearFecha(
                                            $s['fecha_contacto']
                                        ) ?>

                                    </small>

                                </div>


                                <span
                                    class="estado <?= strtolower(
                                        str_replace(
                                            "_",
                                            "-",
                                            e(
                                                $s[
                                                    'estado_proceso'
                                                ]
                                            )
                                        )
                                    ) ?>"
                                >

                                    <?= ucfirst(
                                        strtolower(
                                            str_replace(
                                                "_",
                                                " ",
                                                e(
                                                    $s[
                                                        'estado_proceso'
                                                    ]
                                                )
                                            )
                                        )
                                    ) ?>

                                </span>

                            </div>


                            <!-- =========================
                                 RESPONSABLE
                            ========================== -->

                            <div class="gx-timeline__meta">

                                <span>

                                    <i class="fa-solid fa-user"></i>

                                    <?= e(
                                        $s[
                                            'responsable_nombre'
                                        ]
                                        ?? 'Sin responsable'
                                    ) ?>

                                </span>

                            </div>


                            <!-- =========================
                                 OBSERVACIONES
                            ========================== -->

                            <p>

                                <?= nl2br(
                                    e(
                                        $s['observaciones']
                                        ?: 'Sin observaciones registradas.'
                                    )
                                ) ?>

                            </p>


                            <!-- =========================
                                 ACCIONES
                            ========================== -->

                            <div class="gx-timeline__actions">

                                <a
                                    href="<?= BASE_URL ?>/views/jovenes/ver.php?id=<?= $jovenId ?>"
                                    class="btn btn-sm btn-outline"
                                >

                                    Ver perfil

                                </a>


                              <form
    action="<?= BASE_URL ?>/controllers/seguimientoController.php"
    method="POST"
    class="form-eliminar-seguimiento"
>

    <input
        type="hidden"
        name="csrf_token"
        value="<?= htmlspecialchars(
            $_SESSION['csrf_token'],
            ENT_QUOTES,
            'UTF-8'
        ) ?>"
    >

    <input
        type="hidden"
        name="action"
        value="eliminar_seguimiento"
    >

    <input
        type="hidden"
        name="id"
        value="<?= (int)$seguimiento['id'] ?>"
    >

    <button
        type="submit"
        class="btn btn-danger"
    >
        <i class="fa-solid fa-trash"></i>
        Eliminar
    </button>

</form>

                            </div>

                        </div>

                    </article>

                <?php endforeach; ?>

            </div>


        <?php else: ?>


            <!-- =================================================
                 SIN HISTORIAL
            ================================================== -->

            <div class="gx-empty">

                <i class="fa-solid fa-notes-medical"></i>

                <h3>
                    Aún no hay seguimientos
                </h3>

                <p>

                    Este joven todavía no tiene registros
                    de seguimiento.

                </p>


                <a
                    href="<?= BASE_URL ?>/views/seguimientos/crear.php?id=<?= $jovenId ?>"
                    class="btn btn-primary"
                >

                    

                    Registrar primer seguimiento

                </a>

            </div>


        <?php endif; ?>

    </section>


    <!-- =====================================================
         ACCIONES FINALES
    ====================================================== -->

    <div class="gx-actions">

        <div class="gx-actions__secondary">

            <a
                href="<?= BASE_URL ?>/views/jovenes/ver.php?id=<?= $jovenId ?>"
                class="btn btn-primary"
            >


                Volver al perfil

            </a>


            <a
                href="<?= BASE_URL ?>/views/seguimientos/index.php"
                class="btn btn-primary"
            >

      

                Seguimientos

            </a>

        </div>

    </div>

</div>


<?php require_once __DIR__ . "/../../includes/footer.php"; ?>