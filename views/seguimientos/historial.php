<?php

declare(strict_types=1);

require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/permiso.php";
require_once __DIR__ . "/../../config/conexion.php";
require_once __DIR__ . "/../../services/actividadService.php";
require_once __DIR__ . "/../../services/seguimientoService.php";
require_once __DIR__ . "/../../services/excepcionSeguimientoService.php";
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

$jovenId = (int)(
    $_GET['joven_id'] ?? 0
);

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
   HISTORIAL COMPLETO
   SEGUIMIENTOS + EXCEPCIONES
========================================================== */

$historial = obtenerHistorialCompletoPorJoven(
    $pdo,
    $jovenId
);


/* ==========================================================
   ESTADÍSTICAS
========================================================== */

$totalRegistros = count($historial);

$pendientes = 0;
$enProceso = 0;
$finalizados = 0;

foreach ($historial as $registro) {

    $tipoRegistro =
        strtoupper(
            trim(
                $registro['tipo_registro'] ?? ''
            )
        );

    /*
    | Solo los seguimientos normales tienen
    | estado_proceso.
    */

    if ($tipoRegistro !== 'SEGUIMIENTO') {
        continue;
    }

    switch (
        strtoupper(
            trim(
                $registro['estado_proceso'] ?? ''
            )
        )
    ) {

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
   MESES
========================================================== */

$mesesEspanol = [

    1  => 'Enero',
    2  => 'Febrero',
    3  => 'Marzo',
    4  => 'Abril',
    5  => 'Mayo',
    6  => 'Junio',
    7  => 'Julio',
    8  => 'Agosto',
    9  => 'Septiembre',
    10 => 'Octubre',
    11 => 'Noviembre',
    12 => 'Diciembre'

];


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

    <br>
    
    <br>

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
                                $joven['estado_espiritual']
                                ?? 'Nuevo'
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
                <?= $totalRegistros ?>
            </span>

            <span class="stat-label">
                Total registros
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
         HISTORIAL COMPLETO
    ====================================================== -->

    <section class="page-section">

        <div class="section-header">

            <div>

                <h2 class="section-title">
                    Historial completo
                </h2>

                <p class="section-subtitle">
                    Registro cronológico del acompañamiento y sus excepciones.
                </p>

            </div>

        </div>


        <?php if (!empty($historial)): ?>

            <div class="gx-timeline">

                <?php foreach ($historial as $registro): ?>

                    <?php

                    $tipoRegistro =
                        strtoupper(
                            trim(
                                $registro['tipo_registro']
                                ?? ''
                            )
                        );

                    $esExcepcion =
                        $tipoRegistro === 'EXCEPCION';

                    ?>

                    <article
                        class="gx-timeline__item <?= $esExcepcion
                            ? 'gx-timeline__item--excepcion'
                            : '' ?>"
                    >

                        <div class="gx-timeline__line"></div>

                        <div class="gx-timeline__dot"></div>


                        <div class="gx-timeline__content">
<?php if ($esExcepcion): ?>

    <!-- =================================
         EXCEPCIÓN
    ================================== -->

    <div class="gx-timeline__top">

        <div>

            <h4>
                Excepción de seguimiento
            </h4>

            <small>

                <i class="fa-regular fa-calendar"></i>

                <?= e(
                    formatearFecha(
                        $registro['fecha_registro']
                    )
                ) ?>

            </small>

        </div>

        <span class="estado excepcion">

            <i class="fa-solid fa-triangle-exclamation"></i>

            Excepción

        </span>

    </div>


    <div class="gx-timeline__meta">

        <span>

            <i class="fa-solid fa-circle-info"></i>

            Motivo:

            <?= e(
                nombreMotivoExcepcionSeguimiento(
                    $registro['excepcion_motivo'] ?? ''
                )
            ) ?>

        </span>

    </div>


    <p>

        <?= nl2br(
            e(
                $registro['observaciones']
                ?: 'Sin observaciones registradas.'
            )
        ) ?>

    </p>


    <div class="gx-timeline__meta">

        <span>

            <i class="fa-solid fa-user"></i>

            <?= e(
                $registro['responsable_nombre']
                ?? 'Sin responsable'
            ) ?>

        </span>

    </div>


<?php else: ?>

    <!-- =================================
         SEGUIMIENTO NORMAL
    ================================== -->

    <?php

    $modalidad =
        strtoupper(
            trim(
                $registro['modalidad_contacto']
                ?? ''
            )
        );

    $estadoProceso =
        strtoupper(
            trim(
                $registro['estado_proceso']
                ?? ''
            )
        );

    $estadoClase =
        strtolower(
            str_replace(
                '_',
                '-',
                $estadoProceso
            )
        );

    $modalidadNombre =
        match ($modalidad) {

            'WHATSAPP' =>
                'WhatsApp',

            'LLAMADA' =>
                'Llamada',

            'VISITA' =>
                'Visita',

            'MENSAJE' =>
                'Mensaje',

            default =>
                'Sin modalidad'

        };

    ?>

    <div class="gx-timeline__top">

        <div>

            <h4>
                <?= e($modalidadNombre) ?>
            </h4>

            <small>

                <i class="fa-regular fa-calendar"></i>

                <?= e(
                    formatearFecha(
                        $registro['fecha_registro']
                    )
                ) ?>

            </small>

        </div>

        <span
            class="estado <?= e($estadoClase) ?>"
        >

            <?= e(
                ucfirst(
                    strtolower(
                        str_replace(
                            '_',
                            ' ',
                            $estadoProceso
                        )
                    )
                )
            ) ?>

        </span>

    </div>


    <div class="gx-timeline__meta">

        <span>

            <i class="fa-solid fa-user"></i>

            <?= e(
                $registro['responsable_nombre']
                ?? 'Sin responsable'
            ) ?>

        </span>

    </div>


    <p>

        <?= nl2br(
            e(
                $registro['observaciones']
                ?: 'Sin observaciones registradas.'
            )
        ) ?>

    </p>


<div class="gx-timeline__actions">

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
            value="<?= (int)$registro['registro_id'] ?>"
        >

        <button
            type="submit"
            class="btn btn-danger btn-eliminar-seguimiento"
        >

            <i class="fa-solid fa-trash"></i>

            Eliminar

        </button>

    </form>

</div>

<?php endif; ?>

                            

                               

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
                    Aún no hay registros
                </h3>

                <p>

                    Este joven todavía no tiene seguimientos
                    ni excepciones registradas.

                </p>


                <a
                    href="<?= BASE_URL ?>/views/seguimientos/crear.php?id=<?= $jovenId ?>"
                    class="btn btn-primary"
                >

                    <i class="fa-solid fa-plus"></i>

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


<script
    src="<?= BASE_URL ?>/assets/js/modulos/seguimientos/historial.js"
></script>


<?php require_once __DIR__ . "/../../includes/footer.php"; ?>