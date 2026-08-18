<?php

declare(strict_types=1);

require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/permiso.php";
require_once __DIR__ . "/../../config/conexion.php";
require_once __DIR__ . "/../../services/notificacionService.php";
require_once __DIR__ . "/../../helpers/format.php";


/* ==========================================================
   USUARIO ACTUAL
========================================================== */

$usuarioId = usuarioId();

if (
    $usuarioId === null ||
    $usuarioId <= 0
) {
    header("Location: ../dashboard.php");
    exit;
}

$usuarioId = (int)$usuarioId;


/* ==========================================================
   OBTENER NOTIFICACIONES
========================================================== */

$notificaciones = obtenerNotificacionesUsuario(
    $pdo,
    $usuarioId,
    100
);

$totalNoLeidas = contarNotificacionesNoLeidas(
    $pdo,
    $usuarioId
);

$totalNotificaciones = count(
    $notificaciones
);

$totalLeidas = max(
    0,
    $totalNotificaciones - $totalNoLeidas
);


/* ==========================================================
   HEADER
========================================================== */

require_once __DIR__ . "/../../includes/header.php";

?>


<div class="page notificaciones-page">


    <!-- ======================================================
         ENCABEZADO
    ======================================================= -->

    <div class="page-header">

        <div>

            <h1 class="page-title">
                Notificaciones
            </h1>

            <p class="page-subtitle">
                Historial de avisos y actividades de tu cuenta.
            </p>

        </div>

    </div>


    <!-- ======================================================
         ESTADÍSTICAS
    ======================================================= -->

    <section class="gx-stats">


        <!-- TOTAL -->

        <div class="stat-card info">

            <span class="stat-number">
                <?= $totalNotificaciones ?>
            </span>

            <span class="stat-label">
                Total
            </span>

        </div>


        <!-- SIN LEER -->

        <div class="stat-card warning">

            <span class="stat-number">
                <?= $totalNoLeidas ?>
            </span>

            <span class="stat-label">
                Sin leer
            </span>

        </div>


        <!-- LEÍDAS -->

        <div class="stat-card success">

            <span class="stat-number">
                <?= $totalLeidas ?>
            </span>

            <span class="stat-label">
                Leídas
            </span>

        </div>


    </section>


    <!-- ======================================================
         HISTORIAL
    ======================================================= -->

    <section class="page-section">


        <div class="section-header">


            <div>

                <h2 class="section-title">
                    Historial
                </h2>

                <p class="section-subtitle">
                    Gestiona tus notificaciones.
                </p>

            </div>


            <!-- ==================================================
                 ACCIONES GENERALES
            =================================================== -->

            <div class="notificaciones-toolbar__actions">


                <!-- MARCAR TODAS COMO LEÍDAS -->

                <form
                    method="POST"
                    action="<?= BASE_URL ?>/controllers/notificacionController.php"
                    style="display:inline;"
                >

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= htmlspecialchars(
                            $_SESSION['csrf_token'] ?? '',
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                    >

                    <input
                        type="hidden"
                        name="action"
                        value="marcar_todas_leidas"
                    >

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >

                        <i class="fa-solid fa-check-double"></i>

                        Marcar todas como leídas

                    </button>

                </form>


                <!-- MARCAR TODAS COMO NO LEÍDAS -->

                <form
                    method="POST"
                    action="<?= BASE_URL ?>/controllers/notificacionController.php"
                    style="display:inline;"
                >

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= htmlspecialchars(
                            $_SESSION['csrf_token'] ?? '',
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                    >

                    <input
                        type="hidden"
                        name="action"
                        value="marcar_todas_no_leidas"
                    >

                    <button
                        type="submit"
                        class="btn btn-secondary"
                    >

                        <i class="fa-solid fa-envelope"></i>

                        Marcar todas como no leídas

                    </button>

                </form>


                <!-- ELIMINAR LEÍDAS -->

                <form
                    method="POST"
                    action="<?= BASE_URL ?>/controllers/notificacionController.php"
                    style="display:inline;"
                >

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= htmlspecialchars(
                            $_SESSION['csrf_token'] ?? '',
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                    >

                    <input
                        type="hidden"
                        name="action"
                        value="eliminar_leidas"
                    >

                    <button
                        type="submit"
                        class="btn btn-warning"
                    >

                        <i class="fa-solid fa-trash"></i>

                        Eliminar leídas

                    </button>

                </form>


            </div>

        </div>


        <!-- ======================================================
             LISTA
        ======================================================= -->

        <?php if (!empty($notificaciones)): ?>


            <div class="gx-notifications-page">


                <?php foreach ($notificaciones as $notificacion): ?>


                    <?php


                    /* ==================================================
                       TIPO
                    ================================================== */

                    $tipo = strtoupper(
                        trim(
                            (string)(
                                $notificacion['tipo'] ?? ''
                            )
                        )
                    );


                    /* ==================================================
                       ICONO
                    ================================================== */

                    switch ($tipo) {

                        case 'NUEVA_ASIGNACION':

                            $icono = 'fa-user-plus';

                            break;


                        case 'ASIGNACION_EN_PROCESO':

                            $icono = 'fa-play';

                            break;


                        case 'ASIGNACION_COMPLETADA':

                            $icono = 'fa-circle-check';

                            break;


                        case 'ASIGNACION_CANCELADA':

                            $icono = 'fa-circle-xmark';

                            break;


                        case 'RECORDATORIO_SEGUIMIENTO':

                            $icono = 'fa-bell';

                            break;


                        case 'NOTIFICACION_LEIDA':

                            $icono = 'fa-eye';

                            break;


                        default:

                            $icono = 'fa-bell';

                            break;
                    }


                    /* ==================================================
                       CLASE VISUAL
                    ================================================== */

                    switch ($tipo) {

                        case 'NUEVA_ASIGNACION':

                            $claseTipo = 'nueva';

                            break;


                        case 'ASIGNACION_EN_PROCESO':

                            $claseTipo = 'proceso';

                            break;


                        case 'ASIGNACION_COMPLETADA':

                            $claseTipo = 'completada';

                            break;


                        case 'ASIGNACION_CANCELADA':

                            $claseTipo = 'cancelada';

                            break;


                        case 'NOTIFICACION_LEIDA':

                            $claseTipo = 'general';

                            break;


                        default:

                            $claseTipo = 'general';

                            break;
                    }


                    /* ==================================================
                       ESTADO
                    ================================================== */

                    $estaLeida =
                        (int)(
                            $notificacion['leida'] ?? 0
                        ) === 1;


                    $claseEstado =
                        $estaLeida
                            ? 'leida'
                            : 'no-leida';


                    /* ==================================================
                       FECHA
                    ================================================== */

                    $fecha =
                        (string)(
                            $notificacion['created_at']
                            ?? ''
                        );


                    ?>


                    <article
                        class="notificacion-card <?= e($claseEstado) ?>"
                    >


                        <!-- ==========================================
                             ICONO
                        =========================================== -->

                        <div
                            class="
                                notificacion-card__icon
                                notificacion-card__icon--<?= e($claseTipo) ?>
                            "
                        >

                            <i
                                class="fa-solid <?= e($icono) ?>"
                            ></i>

                        </div>


                        <!-- ==========================================
                             CONTENIDO
                        =========================================== -->

                        <div class="notificacion-card__content">


                            <div class="notificacion-card__top">


                                <h3>

                                    <?= e(
                                        $notificacion['titulo']
                                        ?? 'Notificación'
                                    ) ?>

                                </h3>


                                <?php if (!$estaLeida): ?>

                                    <span class="notificacion-card__badge">

                                        Sin leer

                                    </span>

                                <?php else: ?>

                                    <span class="notificacion-card__badge notificacion-card__badge--leida">

                                        Leída

                                    </span>

                                <?php endif; ?>


                            </div>


                            <p>

                                <?= e(
                                    $notificacion['mensaje']
                                    ?? ''
                                ) ?>

                            </p>


                            <?php if ($fecha !== ''): ?>

                                <time>

                                    <i class="fa-regular fa-clock"></i>

                                    <?= e($fecha) ?>

                                </time>

                            <?php endif; ?>


                        </div>


                        <!-- ==========================================
                             ACCIONES
                        =========================================== -->

                        <div class="notificacion-card__actions">


                            <?php if ($estaLeida): ?>


                                <!-- ==================================
                                     MARCAR COMO NO LEÍDA
                                =================================== -->

                                <form
                                    method="POST"
                                    action="<?= BASE_URL ?>/controllers/notificacionController.php"
                                >

                                    <input
                                        type="hidden"
                                        name="csrf_token"
                                        value="<?= htmlspecialchars(
                                            $_SESSION['csrf_token'] ?? '',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="action"
                                        value="marcar_no_leida"
                                    >

                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?= (int)(
                                            $notificacion['id'] ?? 0
                                        ) ?>"
                                    >

                                    <button
                                        type="submit"
                                        class="btn btn-sm btn-secondary btn-notificacion-no-leida"
                                        title="Marcar como no leída"
                                    >

                                        <i class="fa-solid fa-envelope"></i>

                                        No leída

                                    </button>

                                </form>


                            <?php else: ?>


                                <!-- ==================================
                                     MARCAR COMO LEÍDA
                                =================================== -->

                                <form
                                    method="POST"
                                    action="<?= BASE_URL ?>/controllers/notificacionController.php"
                                >

                                    <input
                                        type="hidden"
                                        name="csrf_token"
                                        value="<?= htmlspecialchars(
                                            $_SESSION['csrf_token'] ?? '',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="action"
                                        value="marcar_leida"
                                    >

                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?= (int)(
                                            $notificacion['id'] ?? 0
                                        ) ?>"
                                    >

                                    <button
                                        type="submit"
                                        class="btn btn-sm btn-primary btn-notificacion-leida"
                                        title="Marcar como leída"
                                    >

                                        <i class="fa-solid fa-check"></i>

                                        Leer

                                    </button>

                                </form>


                            <?php endif; ?>


                            <!-- ==================================
                                 ELIMINAR
                            =================================== -->

                            <form
                                method="POST"
                                action="<?= BASE_URL ?>/controllers/notificacionController.php"
                            >

                                <input
                                    type="hidden"
                                    name="csrf_token"
                                    value="<?= htmlspecialchars(
                                        $_SESSION['csrf_token'] ?? '',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                >

                                <input
                                    type="hidden"
                                    name="action"
                                    value="eliminar"
                                >

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= (int)(
                                        $notificacion['id'] ?? 0
                                    ) ?>"
                                >

                                <button
                                    type="submit"
                                    class="btn btn-sm btn-danger"
                                    aria-label="Eliminar notificación"
                                    title="Eliminar notificación"
                                >

                                    <i class="fa-solid fa-trash"></i>

                                </button>

                            </form>


                        </div>


                    </article>


                <?php endforeach; ?>


            </div>


        <?php else: ?>


            <!-- ==================================================
                 VACÍO
            =================================================== -->

            <div class="gx-empty">

                <i class="fa-regular fa-bell-slash"></i>

                <p>
                    No tienes notificaciones.
                </p>

            </div>


        <?php endif; ?>


    </section>


</div>


<?php

require_once __DIR__ . "/../../includes/footer.php";

?>