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
   PERÍODO
========================================================== */

$anioActual =
    (int)date('Y');

$mesActual =
    (int)date('m');


$anio =
    (int)(
        $_GET['anio']
        ?? $anioActual
    );


$mes =
    (int)(
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

    $anio =
        $anioActual;
}


/* ==========================================================
   VALIDAR MES
========================================================== */

/*
 * 0 = Todos los meses
 */

if (
    $mes < 0 ||
    $mes > 12
) {

    $mes =
        $mesActual;
}


/* ==========================================================
   TEXTO DEL PERÍODO
========================================================== */

$periodoTexto =
    $mes === 0

        ? 'Todos los meses de ' . $anio

        : (
            ($meses[$mes] ?? 'Mes inválido')
            . ' '
            . $anio
        );


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
        ON u.rol_id = r.id

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
    count(
        $jovenesPendientes
    );


$totalAsignados =
    0;

$totalEnProceso =
    0;

$totalCompletados =
    0;

$totalCancelados =
    0;


foreach (
    $asignaciones
    as $asignacion
) {

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

            $totalAsignados++;

            break;


        case 'EN_PROCESO':

            $totalAsignados++;

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


<div class="seguimientos-page asignaciones-page">


    <!-- =====================================================
         HEADER
    ====================================================== -->

    <div class="page-header">

        <div class="page-header-left">

            <h1 class="page-title">
                Asignaciones de seguimiento
            </h1>


            <p class="page-subtitle">

                Distribuye los jóvenes pendientes entre
                los usuarios responsables.

            </p>


            <span class="badge badge-info">

                <?= e(
                    $periodoTexto
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
                Asignados
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
         FILTRO DE PERÍODO
    ====================================================== -->

    <section class="gx-card gx-card--soft asignaciones-filtro-card">

        <div class="gx-card__header">

            <div>

                <h2>
                    Filtrar período
                </h2>

                <p>
                    Selecciona un mes concreto o consulta
                    todas las asignaciones del año.
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

                            <option
                                value="0"
                                <?= $mes === 0
                                    ? 'selected'
                                    : '' ?>
                            >

                                Todos los meses

                            </option>


                            <?php foreach (
                                $meses
                                as $numero =>
                                $nombreMes
                            ): ?>

                                <option
                                    value="<?= (int)$numero ?>"
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
                            id="anio"
                            name="anio"
                            type="number"
                            class="form-input"
                            min="2000"
                            max="2100"
                            value="<?= (int)$anio ?>"
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

    <section class="page-section asignaciones-section">

        <div class="section-header asignaciones-main-header">

            <div>

                <h2 class="section-title">
                    Jóvenes pendientes sin asignar
                </h2>

                <p class="section-subtitle">

                    <?= $mes === 0

                        ? 'Jóvenes que continúan pendientes según el universo de seguimiento y que no tienen asignación durante ningún mes del año seleccionado.'

                        : 'Jóvenes que continúan pendientes durante el período seleccionado y que aún no tienen asignación.'

                    ?>

                </p>

            </div>

        </div>


        <?php if (!empty($jovenesPendientes)): ?>


            <!-- =================================================
                 FORMULARIO PRINCIPAL
            ================================================== -->

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


                <!--
                 * Para asignar siempre debe existir
                 * un mes específico.
                 *
                 * Cuando estamos en "Todos los meses"
                 * estos campos no se usan para crear asignaciones.
                 -->

                <input
                    type="hidden"
                    name="anio"
                    value="<?= (int)$anio ?>"
                >


                <input
                    type="hidden"
                    name="mes"
                    id="mesAsignacion"
                    value="<?= $mes === 0 ? '' : (int)$mes ?>"
                >


                <!-- CONFIGURACIÓN -->

                <div class="asignaciones-form-card">

                    <div class="asignaciones-card-header">

                        <div>

                            <h3 class="asignaciones-card-title">

                                Asignar seleccionados

                            </h3>

                            <p class="asignaciones-card-subtitle">

                                Define quién realizará el seguimiento
                                y agrega una observación opcional.

                            </p>

                        </div>

                    </div>


                    <div class="asignaciones-form-grid">

                        <!-- USUARIO -->

                        <div class="form-group">

                            <label
                                class="form-label"
                                for="usuario_id"
                            >

                                <i class="fa-solid fa-user-check"></i>

                                Usuario responsable

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


                        <!-- OBSERVACIONES -->

                        <div class="form-group">

                            <label
                                class="form-label"
                                for="observaciones"
                            >

                                <i class="fa-solid fa-comment-dots"></i>

                                Observaciones

                            </label>


                            <textarea
                                id="observaciones"
                                name="observaciones"
                                class="form-textarea"
                                rows="4"
                                maxlength="2000"
                                placeholder="Ejemplo: Contactar durante esta semana."
                            ></textarea>

                        </div>

                    </div>


                    <div class="asignaciones-form-actions">

                        <button
                            type="submit"
                            class="btn btn-primary"
                            id="btnAsignarJovenes"
                            <?= $mes === 0
                                ? 'disabled title="Selecciona un mes específico para asignar jóvenes."'
                                : '' ?>
                        >

                            <i class="fa-solid fa-user-plus"></i>

                            Asignar jóvenes

                        </button>

                    </div>


                    <?php if ($mes === 0): ?>

                        <div class="form-info">

                            <i class="fa-solid fa-circle-info"></i>

                            <span>

                                Para crear una asignación debes seleccionar
                                un mes específico. El filtro
                                <strong>Todos los meses</strong>
                                es únicamente para consultar.

                            </span>

                        </div>

                    <?php endif; ?>

                </div>


                <div class="asignaciones-section-gap"></div>


                <!-- SELECCIÓN -->

                <div class="asignaciones-table-card">

                    <div class="asignaciones-card-header">

                        <div>

                            <h3 class="asignaciones-card-title">

                                Seleccionar jóvenes

                            </h3>

                            <p class="asignaciones-card-subtitle">

                                Marca uno o varios jóvenes para
                                incluirlos en la asignación.

                            </p>

                        </div>

                    </div>


                    <div class="asignaciones-selection-toolbar">

                        <div class="search-wrapper">

                            <input
                                type="text"
                                id="buscarAsignaciones"
                                class="search-input"
                                placeholder="Buscar joven..."
                                autocomplete="off"
                            >

                        </div>


                        <button
                            type="button"
                            id="selectTodos"
                            class="btn btn-outline"
                        >

                            <i class="fa-solid fa-square-check"></i>

                            Seleccionar todos

                        </button>

                    </div>


                    <div class="table-responsive">

                        <table
                            class="table gx-table"
                            id="tablaAsignaciones"
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
                                            <?= $mes === 0
                                                ? ''
                                                : '' ?>
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
                                                $joven['genero']
                                                ?? ''
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

                                        <!-- CHECK -->

                                        <td
                                            class="table-check-column"
                                        >

                                            <input
                                                type="checkbox"
                                                class="check-joven table-check"
                                                name="joven_ids[]"
                                                value="<?= (int)$joven['id'] ?>"
                                            >

                                        </td>


                                        <!-- NOMBRE -->

                                        <td>

                                            <span
                                                class="seguimiento-nombre"
                                            >

                                                <?= e(
                                                    $joven['nombre_completo']
                                                ) ?>

                                            </span>

                                        </td>


                                        <!-- GÉNERO -->

                                        <td>

                                            <?= match ($genero) {

                                                'F' =>
                                                    'Femenino',

                                                'M' =>
                                                    'Masculino',

                                                default =>
                                                    '—'

                                            } ?>

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


                    <div class="asignaciones-table-footer">

                        <span class="asignaciones-seleccion-info">

                            Selecciona los jóvenes que deseas
                            incluir en esta asignación.

                        </span>

                    </div>

                </div>

            </form>


        <?php else: ?>


            <div class="asignaciones-empty">

                <i class="fa-solid fa-circle-check"></i>

                <h3>
                    No hay jóvenes pendientes sin asignar
                </h3>

                <p>

                    Todos los jóvenes que pertenecen al universo
                    de seguimiento ya tienen seguimiento,
                    excepción o asignación según el filtro seleccionado.

                </p>

            </div>


        <?php endif; ?>


    </section>


    <br>


    <!-- =========================================================
         ASIGNACIONES DEL PERÍODO
    ========================================================== -->

    <section class="page-section asignaciones-section">

        <div class="section-header">

            <div>

                <h2 class="section-title">

                    <?= $mes === 0
                        ? 'Asignaciones del año'
                        : 'Asignaciones del período'
                    ?>

                </h2>


                <p class="section-subtitle">

                    <?= $mes === 0

                        ? 'Consulta todas las asignaciones registradas durante el año seleccionado.'

                        : 'Consulta y administra los jóvenes que ya fueron distribuidos entre los usuarios responsables.'

                    ?>

                </p>

            </div>

        </div>


        <?php if (!empty($asignaciones)): ?>


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


                <input
                    type="hidden"
                    name="anio"
                    value="<?= (int)$anio ?>"
                >


                <input
                    type="hidden"
                    name="mes"
                    value="<?= (int)$mes ?>"
                >


                <div class="asignaciones-table-card">

                    <div class="gx-toolbar asignaciones-toolbar">

                        <div class="asignaciones-seleccion-info">

                            <span
                                id="contadorAsignacionesSeleccionadas"
                            >
                                0 seleccionadas
                            </span>

                        </div>


                        <div class="asignaciones-toolbar__actions">

                            <button
                                type="button"
                                id="selectTodasAsignaciones"
                                class="btn btn-outline"
                            >

                                <i class="fa-solid fa-square-check"></i>

                                Seleccionar todos

                            </button>


                            <button
                                type="submit"
                                id="cancelarSeleccionadas"
                                class="btn btn-danger"
                                disabled
                            >

                                <i class="fa-solid fa-xmark"></i>

                                Cancelar seleccionadas

                            </button>

                        </div>

                    </div>


                    <div class="table-responsive">

                        <table
                            class="table gx-table"
                            id="tablaAsignacionesActuales"
                        >

                            <thead>

                                <tr>

                                    <th
                                        class="table-check-column"
                                    >

                                        <input
                                            type="checkbox"
                                            id="checkTodasAsignaciones"
                                            class="table-check"
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


                                    <?php if ($mes === 0): ?>

                                        <th>
                                            Período
                                        </th>

                                    <?php endif; ?>


                                    <th>
                                        Fecha
                                    </th>


                                    <th>
                                        Acción
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


                                    $puedeCancelar =
                                        in_array(
                                            $estado,
                                            [
                                                'PENDIENTE',
                                                'EN_PROCESO'
                                            ],
                                            true
                                        );


                                    $mesAsignacion =
                                        (int)(
                                            $asignacion['mes']
                                            ?? 0
                                        );


                                    $anioAsignacion =
                                        (int)(
                                            $asignacion['anio']
                                            ?? 0
                                        );


                                    $periodoAsignacion =
                                        $meses[
                                            $mesAsignacion
                                        ]
                                        ?? 'Mes desconocido';

                                    ?>

                                    <tr>

                                        <!-- CHECK -->

                                        <td
                                            class="table-check-column"
                                        >

                                            <?php if ($puedeCancelar): ?>

                                                <input
                                                    type="checkbox"
                                                    class="check-asignacion table-check"
                                                    name="ids[]"
                                                    value="<?= (int)$asignacion['id'] ?>"
                                                >

                                            <?php else: ?>

                                                <input
                                                    type="checkbox"
                                                    class="table-check"
                                                    disabled
                                                >

                                            <?php endif; ?>

                                        </td>


                                        <!-- JOVEN -->

                                        <td>

                                            <span
                                                class="seguimiento-nombre"
                                            >

                                                <?= e(
                                                    $asignacion['joven_nombre']
                                                    ?? 'Sin nombre'
                                                ) ?>

                                            </span>

                                        </td>


                                        <!-- RESPONSABLE -->

                                        <td>

                                            <span
                                                class="seguimiento-responsable"
                                            >

                                                <?= e(
                                                    $asignacion['usuario_nombre']
                                                    ?? 'Sin responsable'
                                                ) ?>

                                            </span>

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

                                            <span
                                                class="seguimiento-responsable"
                                            >

                                                <?= e(
                                                    $asignacion['asignado_por_nombre']
                                                    ?? '—'
                                                ) ?>

                                            </span>

                                        </td>


                                        <!-- PERÍODO -->

                                        <?php if ($mes === 0): ?>

                                            <td>

                                                <span
                                                    class="badge badge-info"
                                                >

                                                    <?= e(
                                                        $periodoAsignacion
                                                        . ' '
                                                        . $anioAsignacion
                                                    ) ?>

                                                </span>

                                            </td>

                                        <?php endif; ?>


                                        <!-- FECHA -->

                                        <td>

                                            <span
                                                class="seguimiento-fecha"
                                            >

                                                <?= e(
                                                    formatearFecha(
                                                        $asignacion['fecha_asignacion']
                                                        ?? null
                                                    )
                                                ) ?>

                                            </span>

                                        </td>


                                        <!-- ACCIÓN -->

                                        <td>

                                            <div
                                                class="seguimiento-acciones"
                                            >

                                                <?php if ($puedeCancelar): ?>

                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-danger btn-cancelar-asignacion"
                                                        data-id="<?= (int)$asignacion['id'] ?>"
                                                        data-nombre="<?= e(
                                                            $asignacion['joven_nombre']
                                                            ?? 'este joven'
                                                        ) ?>"
                                                    >

                                                        <i
                                                            class="fa-solid fa-xmark"
                                                        ></i>

                                                        Cancelar

                                                    </button>

                                                <?php else: ?>

                                                    <span
                                                        class="text-muted"
                                                    >
                                                        —
                                                    </span>

                                                <?php endif; ?>

                                            </div>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                </div>

            </form>


            <!-- CANCELACIÓN INDIVIDUAL -->

            <form
                action="<?= BASE_URL ?>/controllers/asignacionSeguimientoController.php"
                method="POST"
                id="formCancelarIndividual"
                style="display:none;"
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
                    value="cancelar_asignacion"
                >


                <input
                    type="hidden"
                    name="id"
                    id="cancelarAsignacionId"
                    value=""
                >

            </form>


        <?php else: ?>


            <div class="asignaciones-empty">

                <i class="fa-solid fa-inbox"></i>

                <h3>
                    No hay asignaciones
                </h3>


                <p>

                    <?php if ($mes === 0): ?>

                        No hay asignaciones registradas durante
                        <?= e($anio) ?>.

                    <?php else: ?>

                        No hay jóvenes asignados durante
                        <?= e(
                            $meses[$mes]
                            . ' '
                            . $anio
                        ) ?>.

                    <?php endif; ?>

                </p>

            </div>


        <?php endif; ?>

    </section>

</div>


<!-- =========================================================
     JAVASCRIPT
========================================================= -->

<script
    src="<?= BASE_URL ?>/assets/js/modulos/seguimientos/asignaciones.js"
></script>


<?php

require_once __DIR__ . "/../../includes/footer.php";

?>