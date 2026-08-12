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
   PERMISOS
========================================================== */

if (!tienePermiso('asignar_seguimientos')) {

    header("Location: ../dashboard.php");

    exit;
}


/* ==========================================================
   ACTIVIDAD
========================================================== */

actualizarEstadoActividad($pdo);


/* ==========================================================
   PERÍODO SELECCIONADO
========================================================== */

$anioActual = (int) date('Y');
$mesActual  = (int) date('m');

$anio = (int) (
    $_GET['anio']
    ?? $anioActual
);

$mes = (int) (
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
   USUARIOS DISPONIBLES PARA RECIBIR ASIGNACIONES
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
        ON u.rol_id = r.id

    WHERE u.activo = 1

    AND r.nombre = 'USUARIO'

    ORDER BY
        u.nombre ASC

");

$stmt->execute();

$usuariosDisponibles =
    $stmt->fetchAll(PDO::FETCH_ASSOC);


/* ==========================================================
   ESTADÍSTICAS DE ASIGNACIONES
========================================================== */

$totalPendientes =
    count($jovenesPendientes);

$totalAsignados =
    count($asignaciones);

$totalCompletados = 0;

$totalEnProceso = 0;

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

        case 'COMPLETADO':

            $totalCompletados++;

            break;

        case 'EN_PROCESO':

            $totalEnProceso++;

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

                Asignaciones activas

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
         FILTROS
    ====================================================== -->

    <section class="gx-card gx-card--soft">


        <div class="gx-card__header">

            <div>

                <h2>

                    Filtrar período

                </h2>


                <p>

                    Selecciona el mes que deseas gestionar.

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
         JÓVENES PENDIENTES SIN ASIGNAR
    ====================================================== -->

    <section class="gx-card gx-card--soft asignaciones-card">


        <div class="gx-card__header">

            <div>

                <h2 class="section-title">

                    Jóvenes pendientes sin asignar

                </h2>


                <p class="section-subtitle">

                    Selecciona uno o varios jóvenes y asígnalos
                    a un usuario responsable.

                </p>

            </div>

        </div>


        <div class="gx-card__body">


            <?php if (!empty($jovenesPendientes)): ?>


                <form
                    action="<?= BASE_URL ?>/controllers/asignacionSeguimientoController.php"
                    method="POST"
                    id="formAsignarJovenes"
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


                    <!-- =================================================
                         CONTENEDOR DE JÓVENES SELECCIONADOS
                         Se rellena mediante JavaScript para que la
                         selección sobreviva a la paginación de DataTables.
                    ================================================== -->

                    <div id="idsJovenesSeleccionados"></div>


                    <!-- =================================================
                         CONTROLES
                    ================================================== -->

                    <div class="asignaciones-controles">


                        <div class="form-grid">


                            <div class="form-group">

                                <label
                                    class="form-label"
                                    for="usuario_id"
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


                            <div class="form-group">

                                <label
                                    class="form-label"
                                    for="selectTodos"
                                >

                                    <i class="fa-solid fa-list-check"></i>

                                    Selección

                                </label>


                                <button
                                    type="button"
                                    id="selectTodos"
                                    class="btn btn-outline"
                                >

                                    <i class="fa-solid fa-square-check"></i>

                                    Seleccionar todos

                                </button>

                            </div>


                        </div>


                        <div class="form-group">

                            <label
                                class="form-label"
                                for="observaciones"
                            >

                                <i class="fa-solid fa-comment-dots"></i>

                                Observaciones de la asignación

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


                    <br>

<!-- =================================================
     TABLA DE JÓVENES
================================================== -->

<div class="asignaciones-tabla">

    <div class="table-responsive">

        <table
            class="table gx-table"
            id="tablaAsignaciones"
        >

            <thead>

                <tr>

                    <th class="table-check-column">

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
                                $joven['genero'] ?? ''
                            )
                        );

                    $estadoEspiritual =
                        strtoupper(
                            trim(
                                $joven['estado_espiritual']
                                ?? 'NUEVO'
                            )
                        );

                    ?>

                    <tr>

                        <!-- CHECKBOX -->

                        <td class="table-check-column">

                            <input
                                type="checkbox"
                                class="check-joven table-check"
                                name="joven_ids[]"
                                value="<?= (int)$joven['id'] ?>"
                                aria-label="Seleccionar <?= e(
                                    $joven['nombre_completo']
                                ) ?>"
                            >

                        </td>


                        <!-- NOMBRE -->

                        <td>

                            <span class="seguimiento-nombre">

                                <?= e(
                                    $joven['nombre_completo']
                                ) ?>

                            </span>

                        </td>


                        <!-- GÉNERO -->

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


                        <!-- TELÉFONO -->

                        <td>

                            <?= e(
                                $joven['telefono']
                                ?: 'Sin teléfono'
                            ) ?>

                        </td>


                        <!-- ESTADO -->

                        <td>

                            <span class="badge badge-warning">

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


<div class="form-actions">

    <button
        type="submit"
        class="btn btn-primary"
    >

        <i class="fa-solid fa-user-plus"></i>

        Asignar seleccionados

    </button>

</div>

<!-- =================================================
     TABLA DE ASIGNACIONES
================================================== -->

<div class="table-responsive">

    <table
        class="table gx-table"
        id="tablaAsignacionesActuales"
    >

        <thead>

            <tr>

                <th class="table-check-column">

                    <input
                        type="checkbox"
                        id="checkAsignaciones"
                        class="table-check"
                        aria-label="Seleccionar asignaciones"
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

                ?>

                <tr
                    class="<?= $estado === 'CANCELADO'
                        ? 'asignacion-cancelada'
                        : '' ?>"
                >

                    <!-- CHECKBOX -->

                    <td class="table-check-column">

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

                            <input
                                type="checkbox"
                                class="check-asignacion table-check"
                                value="<?= (int)$asignacion['id'] ?>"
                                aria-label="Seleccionar asignación de <?= e(
                                    $asignacion['joven_nombre']
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

                        <span class="seguimiento-nombre">

                            <?= e(
                                $asignacion['joven_nombre']
                            ) ?>

                        </span>

                    </td>


                    <!-- RESPONSABLE -->

                    <td>

                        <?= e(
                            $asignacion['usuario_nombre']
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

            <?php else: ?>


                <div class="gx-empty">


                    <i class="fa-solid fa-inbox"></i>


                    <h3>

                        No hay asignaciones

                    </h3>


                    <p>

                        Todavía no hay jóvenes asignados durante
                        <?= e($meses[$mes] . ' ' . $anio) ?>.

                    </p>


                </div>


            <?php endif; ?>


        </div>


    </section>


</div>


<script>

document.addEventListener(
    "DOMContentLoaded",
    () => {

        const tablaPendientes =
            initDataTable(
                "#tablaAsignaciones",
                {
                    searching: false
                }
            );


        const tablaActuales =
            initDataTable(
                "#tablaAsignacionesActuales",
                {
                    searching: false
                }
            );


        if (tablaPendientes) {

            initSearch(
                "buscadorAsignaciones",
                tablaPendientes
            );

        }

    }
);

</script>


<?php

require_once __DIR__ . "/../../includes/footer.php";

?>