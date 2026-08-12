<?php

declare(strict_types=1);

require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../config/conexion.php";
require_once __DIR__ . "/../../services/sessionService.php";
require_once __DIR__ . "/../../services/actividadService.php";
require_once __DIR__ . "/../../services/asignacionSeguimientoService.php";
require_once __DIR__ . "/../../helpers/csrf.php";
require_once __DIR__ . "/../../helpers/format.php";


/* ==========================================================
   CSRF
========================================================== */

generarCsrf();


/* ==========================================================
   USUARIO ACTUAL
========================================================== */

$usuarioActual = usuarioId();

if ($usuarioActual === null || $usuarioActual <= 0) {

    $_SESSION['error'] =
        'No se pudo identificar al usuario actual.';

    header(
        "Location: " . BASE_URL . "/views/dashboard.php"
    );

    exit;
}

$usuarioActual = (int)$usuarioActual;


/* ==========================================================
   ACTIVIDAD
========================================================== */

actualizarEstadoActividad($pdo);


/* ==========================================================
   PERÍODO
========================================================== */

$anioActual = (int)date('Y');
$mesActual  = (int)date('m');

$anio = (int)(
    $_GET['anio']
    ?? $anioActual
);

$mes = (int)(
    $_GET['mes']
    ?? $mesActual
);


/* ==========================================================
   VALIDAR PERÍODO
========================================================== */

if (
    $anio < 2000 ||
    $anio > 2100
) {

    $anio = $anioActual;

}

if (
    $mes < 1 ||
    $mes > 12
) {

    $mes = $mesActual;

}


/* ==========================================================
   MESES
========================================================== */

$meses = [

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
   ASIGNACIONES DEL USUARIO
========================================================== */

$asignaciones = obtenerAsignacionesUsuario(

    $pdo,

    $usuarioActual,

    $anio,

    $mes

);


/* ==========================================================
   ESTADÍSTICAS
========================================================== */

$totalAsignaciones = count($asignaciones);

$totalPendientes = 0;
$totalEnProceso = 0;
$totalCompletados = 0;
$totalCancelados = 0;


foreach ($asignaciones as $asignacion) {

    switch (

        strtoupper(

            trim(

                $asignacion['estado']
                ?? ''

            )

        )

    ) {

        case 'PENDIENTE':

            $totalPendientes++;

            break;


        case 'EN_PROCESO':

            $totalEnProceso++;

            break;


        case 'COMPLETADO':

            $totalCompletados++;

            break;


        case 'CANCELADO':

            $totalCancelados++;

            break;

    }

}


/* ==========================================================
   DATOS DEL USUARIO
========================================================== */

$stmt = $pdo->prepare("

    SELECT

        u.id,
        u.nombre,
        u.usuario,
        r.nombre AS rol_nombre

    FROM usuarios u

    INNER JOIN roles r
        ON u.rol_id = r.id

    WHERE u.id = :id

    LIMIT 1

");

$stmt->execute([

    ':id' => $usuarioActual

]);

$usuario = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$usuario) {

    $_SESSION['error'] =
        'No se pudo obtener la información del usuario.';

    header("Location: ../dashboard.php");

    exit;

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

                Mis seguimientos

            </h1>


            <p class="page-subtitle">

                Jóvenes asignados a

                <strong>

                    <?= e(
                        $usuario['nombre']
                    ) ?>

                </strong>

            </p>


            <span class="badge badge-info">

                <?= e(

                    $meses[$mes]
                    . ' '
                    . $anio

                ) ?>

            </span>

        </div>


        <div class="page-header-right">

            <a
                href="<?= BASE_URL ?>/views/seguimientos/index.php"
                class="btn btn-back"
            >

                <i class="fa-solid fa-arrow-left"></i>

                Seguimientos

            </a>

        </div>

    </div>


    <!-- =====================================================
         ESTADÍSTICAS
    ====================================================== -->

    <section class="gx-stats">

        <div class="stat-card info">

            <span class="stat-number">

                <?= $totalAsignaciones ?>

            </span>

            <span class="stat-label">

                Total asignados

            </span>

        </div>


        <div class="stat-card danger">

            <span class="stat-number">

                <?= $totalPendientes ?>

            </span>

            <span class="stat-label">

                Pendientes

            </span>

        </div>


        <div class="stat-card warning">

            <span class="stat-number">

                <?= $totalEnProceso ?>

            </span>

            <span class="stat-label">

                En proceso

            </span>

        </div>


        <div class="stat-card success">

            <span class="stat-number">

                <?= $totalCompletados ?>

            </span>

            <span class="stat-label">

                Completados

            </span>

        </div>

    </section>


    <br>


    <!-- =====================================================
         FILTRO
    ====================================================== -->

    <section class="gx-card gx-card--soft">

        <div class="gx-card__header">

            <div>

                <h2>

                    Consultar período

                </h2>

                <p>

                    Revisa las asignaciones de otro mes.

                </p>

            </div>

        </div>


        <div class="gx-card__body">

            <form
                method="GET"
                action="<?= BASE_URL ?>/views/seguimientos/mis-seguimientos.php"
                class="form"
            >

                <div class="form-grid">


                    <div class="form-group">

                        <label
                            class="form-label"
                            for="mes"
                        >

                            <i class="fa-solid fa-calendar"></i>

                            Mes

                        </label>


                        <select
                            id="mes"
                            name="mes"
                            class="form-select"
                        >

                            <?php foreach (
                                $meses as $numero => $nombreMes
                            ): ?>

                                <option
                                    value="<?= $numero ?>"
                                    <?= $numero === $mes
                                        ? 'selected'
                                        : '' ?>
                                >

                                    <?= e(
                                        $nombreMes
                                    ) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <div class="form-group">

                        <label
                            class="form-label"
                            for="anio"
                        >

                            <i class="fa-solid fa-calendar-days"></i>

                            Año

                        </label>


                        <input
                            id="anio"
                            name="anio"
                            type="number"
                            class="form-input"
                            min="2000"
                            max="2100"
                            value="<?= $anio ?>"
                        >

                    </div>


                </div>


                <div class="form-actions">

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >

                        <i class="fa-solid fa-filter"></i>

                        Aplicar filtro

                    </button>

                </div>

            </form>

        </div>

    </section>


    <br>


    <!-- =====================================================
         JÓVENES ASIGNADOS
    ====================================================== -->

    <section class="gx-card gx-card--soft asignaciones-card">


        <!-- =================================================
             HEADER DE LA TARJETA
        ================================================== -->

        <div class="gx-card__header">

            <div>

                <h2 class="section-title">

                    Jóvenes asignados

                </h2>

                <p class="section-subtitle">

                    Gestiona los seguimientos que tienes pendientes.

                </p>

            </div>

        </div>


        <!-- =================================================
             CONTENIDO
        ================================================== -->

        <div class="gx-card__body">


            <?php if (!empty($asignaciones)): ?>


                <div class="table-responsive">

                    <table
                        class="table gx-table"
                        id="tablaMisSeguimientos"
                    >

                        <thead>

                            <tr>

                                <th>

                                    Joven

                                </th>


                                <th>

                                    Género

                                </th>


                                <th>

                                    Teléfono

                                </th>


                                <th>

                                    Estado

                                </th>


                                <th>

                                    Fecha asignación

                                </th>


                                <th>

                                    Acciones

                                </th>

                            </tr>

                        </thead>


                        <tbody>


                            <?php foreach (
                                $asignaciones
                                as $asignacion
                            ): ?>


                                <?php

                                $estado =

                                    strtoupper(

                                        trim(

                                            $asignacion['estado']
                                            ?? ''

                                        )

                                    );


                                $estadoClase =

                                    match ($estado) {

                                        'PENDIENTE' =>
                                            'danger',

                                        'EN_PROCESO' =>
                                            'warning',

                                        'COMPLETADO' =>
                                            'success',

                                        'CANCELADO' =>
                                            'secondary',

                                        default =>
                                            'info'

                                    };


                                $genero =

                                    strtoupper(

                                        trim(

                                            $asignacion[
                                                'joven_genero'
                                            ]
                                            ?? ''

                                        )

                                    );

                                ?>


                                <tr>


                                    <!-- =================================
                                         JOVEN
                                    ================================== -->

                                    <td>

                                        <span
                                            class="seguimiento-nombre"
                                        >

                                            <?= e(

                                                $asignacion[
                                                    'joven_nombre'
                                                ]

                                            ) ?>

                                        </span>

                                    </td>


                                    <!-- =================================
                                         GÉNERO
                                    ================================== -->

                                    <td>

                                        <?= $genero === 'F'

                                            ? 'Femenino'

                                            : (

                                                $genero === 'M'

                                                    ? 'Masculino'

                                                    : '—'

                                            )

                                        ?>

                                    </td>


                                    <!-- =================================
                                         TELÉFONO
                                    ================================== -->

                                    <td>

                                        <?= e(

                                            $asignacion[
                                                'joven_telefono'
                                            ]

                                            ?: 'Sin teléfono'

                                        ) ?>

                                    </td>


                                    <!-- =================================
                                         ESTADO
                                    ================================== -->

                                    <td>

                                        <span
                                            class="badge badge-<?= e(
                                                $estadoClase
                                            ) ?>"
                                        >

                                            <?= e(

                                                ucfirst(

                                                    strtolower(

                                                        str_replace(

                                                            '_',
                                                            ' ',
                                                            $estado

                                                        )

                                                    )

                                                )

                                            ) ?>

                                        </span>

                                    </td>


                                    <!-- =================================
                                         FECHA
                                    ================================== -->

                                    <td>

                                        <?= e(

                                            formatearFecha(

                                                $asignacion[
                                                    'fecha_asignacion'
                                                ]

                                            )

                                        ) ?>

                                    </td>


                                    <!-- =================================
                                         ACCIONES
                                    ================================== -->

                                    <td>

                                        <div
                                            class="seguimiento-acciones"
                                        >


                                            <!-- Ver perfil -->

                                            <a
                                                href="<?= BASE_URL ?>/views/jovenes/ver.php?id=<?= (int)$asignacion['joven_id'] ?>"
                                                class="btn btn-sm btn-perfil <?= $genero === 'F'
                                                    ? 'btn-perfil-chica'
                                                    : 'btn-perfil-chico' ?>"
                                            >

                                                <i class="fa-solid fa-user"></i>

                                                Ver perfil

                                            </a>


                                            <?php if (
                                                $estado === 'PENDIENTE'
                                            ): ?>


                                                <!-- Iniciar -->

                                                <form
                                                    action="<?= BASE_URL ?>/controllers/asignacionSeguimientoController.php"
                                                    method="POST"
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
                                                        value="iniciar_asignacion"
                                                    >


                                                    <input
                                                        type="hidden"
                                                        name="id"
                                                        value="<?= (int)$asignacion['id'] ?>"
                                                    >


                                                    <button
                                                        type="submit"
                                                        class="btn btn-sm btn-warning"
                                                    >

                                                        <i class="fa-solid fa-play"></i>

                                                        Iniciar

                                                    </button>

                                                </form>


                                            <?php endif; ?>


                                            <?php if (

                                                in_array(

                                                    $estado,

                                                    [
                                                        'PENDIENTE',
                                                        'EN_PROCESO'
                                                    ],

                                                    true

                                                )

                                            ): ?>


                                                <!-- Registrar seguimiento -->

                                                <a
                                                    href="<?= BASE_URL ?>/views/seguimientos/crear.php?id=<?= (int)$asignacion['joven_id'] ?>"
                                                    class="btn btn-sm btn-primary"
                                                >

                                                    <i class="fa-solid fa-handshake"></i>

                                                    Registrar seguimiento

                                                </a>


                                            <?php endif; ?>


                                        </div>

                                    </td>


                                </tr>


                            <?php endforeach; ?>


                        </tbody>

                    </table>

                </div>


            <?php else: ?>


                <div class="gx-empty">

                    <i class="fa-solid fa-circle-check"></i>

                    <h3>

                        No tienes seguimientos asignados

                    </h3>

                    <p>

                        No hay asignaciones para

                        <?= e(

                            $meses[$mes]
                            . ' '
                            . $anio

                        ) ?>.

                    </p>

                </div>


            <?php endif; ?>


        </div>

    </section>


    <br>


    <!-- =====================================================
         INFORMACIÓN
    ====================================================== -->

    <section class="gx-card gx-card--soft">

        <div class="gx-card__body">

            <div class="form-info">

                <i class="fa-solid fa-circle-info"></i>

                <span>

                    Cuando registres el seguimiento de un joven,
                    la asignación quedará lista para marcarse como
                    completada.

                </span>

            </div>

        </div>

    </section>


</div>


<?php

require_once __DIR__ . "/../../includes/footer.php";

?>