<?php

declare(strict_types=1);

require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/permiso.php";
require_once __DIR__ . "/../../config/conexion.php";
require_once __DIR__ . "/../../services/actividadService.php";
require_once __DIR__ . "/../../services/asignacionSeguimientoService.php";
require_once __DIR__ . "/../../helpers/csrf.php";
require_once __DIR__ . "/../../helpers/format.php";


/* ==========================================================
   CSRF
========================================================== */

generarCsrf();


/* ==========================================================
   PERMISO
========================================================== */

if (!tienePermiso('asignar_seguimientos')) {

    header(
        "Location: " . BASE_URL . "/views/dashboard.php"
    );

    exit;
}


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
   VALIDAR AÑO
========================================================== */

if (
    $anio < 2000 ||
    $anio > 2100
) {

    $anio = $anioActual;
}


/* ==========================================================
   VALIDAR MES
========================================================== */

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
   JÓVENES PENDIENTES SIN ASIGNAR
========================================================== */

$jovenesPendientes =
    obtenerJovenesPendientesSinAsignar(
        $pdo,
        $anio,
        $mes
    );


/* ==========================================================
   ASIGNACIONES DEL PERÍODO
========================================================== */

$asignaciones =
    obtenerAsignacionesSeguimientoMes(
        $pdo,
        $anio,
        $mes
    );


/* ==========================================================
   USUARIOS DISPONIBLES
========================================================== */

$stmt = $pdo->prepare("
    SELECT

        u.id,
        u.nombre,
        u.usuario,
        u.rol_id,
        r.nombre AS rol_nombre

    FROM usuarios u

    INNER JOIN roles r
        ON r.id = u.rol_id

    WHERE u.activo = 1

    AND r.nombre = 'USUARIO'

    ORDER BY
        u.nombre ASC
");

$stmt->execute();

$usuariosDisponibles =
    $stmt->fetchAll(
        PDO::FETCH_ASSOC
    );


/* ==========================================================
   ESTADÍSTICAS
========================================================== */

$totalPendientes =
    count($jovenesPendientes);

$totalAsignados =
    count($asignaciones);

$totalPendientesAsignados = 0;

$totalEnProceso = 0;

$totalCompletados = 0;

$totalCancelados = 0;


foreach ($asignaciones as $asignacion) {

    $estado =
        strtoupper(
            trim(
                (string)(
                    $asignacion['estado']
                    ?? ''
                )
            )
        );


    switch ($estado) {

        case 'PENDIENTE':

            $totalPendientesAsignados++;

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
                Asignaciones de seguimiento
            </h1>

            <p class="page-subtitle">
                Distribuye los jóvenes pendientes entre los
                usuarios responsables.
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

                Volver a seguimientos

            </a>

        </div>

    </div>


    <!-- =====================================================
         ESTADÍSTICAS
    ====================================================== -->

    <section class="gx-stats">


        <div class="stat-card danger">

            <span class="stat-number">
                <?= $totalPendientes ?>
            </span>

            <span class="stat-label">
                Pendientes sin asignar
            </span>

        </div>


        <div class="stat-card info">

            <span class="stat-number">
                <?= $totalAsignados ?>
            </span>

            <span class="stat-label">
                Asignaciones
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


    <!-- =====================================================
         FILTRO DE PERÍODO
    ====================================================== -->

    <section class="gx-card gx-card--soft asignaciones-card">

        <div class="gx-card__header">

            <div>

                <h2>
                    Filtrar período
                </h2>

                <p>
                    Selecciona el mes y año que deseas gestionar.
                </p>

            </div>

        </div>


        <div class="gx-card__body">

            <form
                method="GET"
                action="<?= BASE_URL ?>/views/seguimientos/asignaciones.php"
                class="form"
            >

                <div class="form-grid">


                    <!-- MES -->

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

                                    <?= e($nombreMes) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <!-- AÑO -->

                    <div class="form-group">

                        <label
                            class="form-label"
                            for="anio"
                        >

                            <i class="fa-solid fa-calendar-days"></i>

                            Año

                        </label>


                        <input
                            type="number"
                            id="anio"
                            name="anio"
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


    <!-- =====================================================
         JÓVENES PENDIENTES
    ====================================================== -->

    <section class="gx-card gx-card--soft asignaciones-card">

        <div class="gx-card__header">

            <div>

                <h2>
                    Jóvenes pendientes sin asignar
                </h2>

                <p>
                    Selecciona uno o varios jóvenes para
                    asignarlos a un usuario.
                </p>

            </div>

        </div>


        <div class="gx-card__body">


            <?php if (!empty($jovenesPendientes)): ?>


                <form
                    action="<?= BASE_URL ?>/controllers/asignacionSeguimientoController.php"
                    method="POST"
                    id="formAsignarJovenes"
                    class="form"
                >


                    <!-- =========================================
                         CAMPOS OCULTOS
                    ========================================== -->

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
                        value="asignar_jovenes"
                    >


                    <input
                        type="hidden"
                        name="anio"
                        value="<?= $anio ?>"
                    >


                    <input
                        type="hidden"
                        name="mes"
                        value="<?= $mes ?>"
                    >


                    <!-- =========================================
                         CONTROLES
                    ========================================== -->

                    <div class="asignaciones-controles">

                        <div class="form-grid">


                            <!-- USUARIO -->

                            <div class="form-group">

                                <label
                                    for="usuario_id"
                                    class="form-label"
                                >

                                    <i class="fa-solid fa-user-check"></i>

                                    Asignar a

                                </label>


                                <select
                                    id="usuario_id"
                                    name="usuario_id"
                                    class="form-select"
                                    required
                                >

                                    <option value="">

                                        Seleccionar usuario

                                    </option>


                                    <?php foreach (
                                        $usuariosDisponibles
                                        as $usuario
                                    ): ?>

                                        <option
                                            value="<?= (int)$usuario['id'] ?>"
                                        >

                                            <?= e(
                                                $usuario['nombre']
                                            ) ?>

                                            <?php if (
                                                !empty(
                                                    $usuario['usuario']
                                                )
                                            ): ?>

                                                ·

                                                <?= e(
                                                    $usuario['usuario']
                                                ) ?>

                                            <?php endif; ?>

                                        </option>

                                    <?php endforeach; ?>

                                </select>

                            </div>


                            <!-- SELECCIONAR TODOS -->

                            <div class="form-group">

                                <label class="form-label">

                                    <i class="fa-solid fa-list-check"></i>

                                    Selección

                                </label>


                                <button
                                    type="button"
                                    id="selectTodos"
                                    class="btn btn-outline"
                                >

                                    <i class="fa-solid fa-check-double"></i>

                                    Seleccionar todos

                                </button>

                            </div>

                        </div>


                        <!-- OBSERVACIONES -->

                        <div class="form-group">

                            <label
                                for="observaciones"
                                class="form-label"
                            >

                                <i class="fa-solid fa-comment-dots"></i>

                                Observaciones

                            </label>


                            <textarea
                                id="observaciones"
                                name="observaciones"
                                class="form-textarea"
                                rows="3"
                                maxlength="2000"
                                placeholder="Ejemplo: Contactar durante esta semana."
                            ></textarea>

                        </div>

                    </div>


                    <div class="asignaciones-separador"></div>


                    <!-- =========================================
                         TABLA DE JÓVENES
                    ========================================== -->

                    <div class="asignaciones-tabla">

                        <div class="table-responsive">

                            <table
                                id="tablaAsignaciones"
                                class="table gx-table"
                            >

                                <thead>

                                    <tr>

                                        <th
                                            class="table-check-column"
                                        >

                                            <input
                                                type="checkbox"
                                                id="checkTodos"
                                                class="table-check"
                                                aria-label="Seleccionar todos los jóvenes"
                                            >

                                        </th>


                                        <th>
                                            Nombre
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

                                    </tr>

                                </thead>


                                <tbody>


                                    <?php foreach (
                                        $jovenesPendientes
                                        as $joven
                                    ): ?>


                                        <?php

                                        $genero =
                                            strtoupper(
                                                trim(
                                                    (string)(
                                                        $joven['genero']
                                                        ?? ''
                                                    )
                                                )
                                            );


                                        $estadoEspiritual =
                                            strtoupper(
                                                trim(
                                                    (string)(
                                                        $joven[
                                                            'estado_espiritual'
                                                        ]
                                                        ?? 'NUEVO'
                                                    )
                                                )
                                            );

                                        ?>


                                        <tr>


                                            <!-- CHECK -->

                                            <td
                                                class="table-check-column"
                                            >

                                                <input
                                                    type="checkbox"
                                                    class="table-check check-joven"
                                                    value="<?= (int)$joven['id'] ?>"
                                                    aria-label="Seleccionar <?= e(
                                                        $joven[
                                                            'nombre_completo'
                                                        ]
                                                    ) ?>"
                                                >

                                            </td>


                                            <!-- NOMBRE -->

                                            <td>

                                                <span
                                                    class="seguimiento-nombre"
                                                >

                                                    <?= e(
                                                        $joven[
                                                            'nombre_completo'
                                                        ]
                                                    ) ?>

                                                </span>

                                            </td>


                                            <!-- GÉNERO -->

                                            <td>

                                                <?=
                                                    $genero === 'F'
                                                        ? 'Femenino'
                                                        : (
                                                            $genero === 'M'
                                                                ? 'Masculino'
                                                                : '—'
                                                        )
                                                ?>

                                            </td>


                                            <!-- TELÉFONO -->

                                            <td>

                                                <?= e(
                                                    $joven['telefono']
                                                    ?: 'Sin teléfono'
                                                ) ?>

                                            </td>


                                            <!-- ESTADO -->

                                            <td>

                                                <span
                                                    class="badge badge-warning"
                                                >

                                                    <?= e(
                                                        ucfirst(
                                                            strtolower(
                                                                $estadoEspiritual
                                                            )
                                                        )
                                                    ) ?>

                                                </span>

                                            </td>


                                        </tr>


                                    <?php endforeach; ?>


                                </tbody>

                            </table>

                        </div>

                    </div>


                    <!-- =========================================
                         ACCIÓN
                    ========================================== -->

                    <div class="form-actions">

                        <button
                            type="submit"
                            class="btn btn-primary"
                            id="btnAsignarSeleccionados"
                        >

                            <i class="fa-solid fa-user-plus"></i>

                            Asignar seleccionados

                        </button>

                    </div>


                </form>


            <?php else: ?>


                <div class="gx-empty">

                    <i class="fa-solid fa-circle-check"></i>

                    <h3>
                        No hay jóvenes pendientes
                    </h3>

                    <p>

                        Todos los jóvenes del período
                        <?= e(
                            $meses[$mes] . ' ' . $anio
                        ) ?>
                        ya tienen una asignación,
                        seguimiento o excepción.

                    </p>

                </div>


            <?php endif; ?>


        </div>

    </section>


    <!-- =====================================================
         ASIGNACIONES DEL PERÍODO
    ====================================================== -->

    <section
        class="gx-card gx-card--soft asignaciones-card asignaciones-card--historial"
    >

        <div class="gx-card__header">

            <div>

                <h2>
                    Asignaciones del período
                </h2>

                <p>
                    Consulta y gestiona las asignaciones realizadas.
                </p>

            </div>

        </div>


        <div class="gx-card__body">


            <?php if (!empty($asignaciones)): ?>


                <!-- =========================================
                     TOOLBAR
                ========================================== -->

                <div class="asignaciones-toolbar">

                    <div>

                        <span class="asignaciones-seleccion-info">

                            Selecciona asignaciones pendientes
                            o en proceso para cancelarlas.

                        </span>

                    </div>


                    <form
                        action="<?= BASE_URL ?>/controllers/asignacionSeguimientoController.php"
                        method="POST"
                        id="formCancelarAsignaciones"
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
                            value="cancelar_asignaciones"
                        >


                        <div id="idsCancelar"></div>


                        <button
                            type="submit"
                            class="btn btn-danger"
                            id="btnCancelarSeleccionados"
                            disabled
                        >

                            <i class="fa-solid fa-xmark"></i>

                            Cancelar seleccionados

                        </button>

                    </form>

                </div>


                <div class="asignaciones-separador"></div>


                <!-- =========================================
                     TABLA
                ========================================== -->

                <div class="asignaciones-tabla">

                    <div class="table-responsive">

                        <table
                            id="tablaAsignacionesActuales"
                            class="table gx-table"
                        >

                            <thead>

                                <tr>

                                    <th
                                        class="table-check-column"
                                    >

                                        <input
                                            type="checkbox"
                                            id="checkAsignaciones"
                                            class="table-check"
                                            aria-label="Seleccionar asignaciones cancelables"
                                        >

                                    </th>


                                    <th>
                                        Joven
                                    </th>


                                    <th>
                                        Responsable
                                    </th>


                                    <th>
                                        Estado
                                    </th>


                                    <th>
                                        Asignado por
                                    </th>


                                    <th>
                                        Fecha
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
                                                (string)(
                                                    $asignacion['estado']
                                                    ?? ''
                                                )
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


                                    $puedeCancelar =
                                        in_array(
                                            $estado,
                                            [
                                                'PENDIENTE',
                                                'EN_PROCESO'
                                            ],
                                            true
                                        );

                                    ?>


                                    <tr
                                        class="<?= $estado === 'CANCELADO'
                                            ? 'asignacion-cancelada'
                                            : '' ?>"
                                    >


                                        <!-- CHECK -->

                                        <td
                                            class="table-check-column"
                                        >

                                            <?php if (
                                                $puedeCancelar
                                            ): ?>

                                                <input
                                                    type="checkbox"
                                                    class="table-check check-asignacion"
                                                    value="<?= (int)$asignacion['id'] ?>"
                                                    aria-label="Seleccionar asignación de <?= e(
                                                        $asignacion[
                                                            'joven_nombre'
                                                        ]
                                                    ) ?>"
                                                >

                                            <?php else: ?>

                                                <span
                                                    class="table-check-placeholder"
                                                    aria-hidden="true"
                                                ></span>

                                            <?php endif; ?>

                                        </td>


                                        <!-- JOVEN -->

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


                                        <!-- RESPONSABLE -->

                                        <td>

                                            <?= e(
                                                $asignacion[
                                                    'usuario_nombre'
                                                ]
                                            ) ?>

                                        </td>


                                        <!-- ESTADO -->

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


                                        <!-- ASIGNADO POR -->

                                        <td>

                                            <?= e(
                                                $asignacion[
                                                    'asignado_por_nombre'
                                                ]
                                            ) ?>

                                        </td>


                                        <!-- FECHA -->

                                        <td>

                                            <?= e(
                                                formatearFecha(
                                                    $asignacion[
                                                        'fecha_asignacion'
                                                    ]
                                                )
                                            ) ?>

                                        </td>


                                    </tr>


                                <?php endforeach; ?>


                            </tbody>

                        </table>

                    </div>

                </div>


            <?php else: ?>


                <div class="gx-empty">

                    <i class="fa-solid fa-inbox"></i>

                    <h3>
                        No hay asignaciones
                    </h3>

                    <p>

                        Todavía no hay jóvenes asignados durante
                        <?= e(
                            $meses[$mes] . ' ' . $anio
                        ) ?>.

                    </p>

                </div>


            <?php endif; ?>


        </div>

    </section>


</div>


<!-- =====================================================
     JAVASCRIPT
====================================================== -->



<script
    src="<?= BASE_URL ?>/assets/js/modules/seguimientos/asignaciones.js?v=<?= filemtime(
        __DIR__ . '/../../assets/js/modules/seguimientos/asignaciones.js'
    ) ?>"
></script>


<?php

require_once __DIR__ . "/../../includes/footer.php";
?>