<?php

<<<<<<< HEAD
require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/permiso.php";
require_once __DIR__ . "/../../services/actividadService.php";
require_once __DIR__ . "/../../config/conexion.php";

require_once __DIR__ . "/../../services/seguimientoService.php";
require_once __DIR__ . "/../../helpers/format.php";
require_once __DIR__ . "/../../helpers/fechas.php";

/* =========================
   PERMISOS
========================= */

if (!tienePermiso('gestionar_seguimientos')) {

    header("Location: ../dashboard.php");
    exit;
}

/* =========================
   ACTUALIZAR ACTIVIDAD
========================= */

actualizarEstadoActividad($pdo);

/* =========================
   RESUMEN
========================= */

$data = obtenerResumenSeguimientosMes();

$seguimientosMes     = $data['seguimientosMes'] ?? [];
$totalActivos        = $data['totalActivos'] ?? 0;
$totalConSeguimiento = $data['totalConSeguimiento'] ?? 0;
$totalSinSeguimiento = $data['totalSinSeguimiento'] ?? 0;
$porcentaje          = $data['porcentaje'] ?? 0;
$color               = $data['color'] ?? '';
$mesTexto            = $data['mesTexto'] ?? '';

/* =========================
   ALERTAS
========================= */

$stmt = $pdo->prepare("
    SELECT

        id,
        nombre_completo,
        telefono,
        genero

    FROM jovenes

    WHERE estado_actividad = 'ACTIVO'

    AND id NOT IN (

        SELECT joven_id

        FROM seguimientos

        WHERE DATE_FORMAT(
            fecha_contacto,
            '%Y-%m'
        ) = DATE_FORMAT(
            CURDATE(),
            '%Y-%m'
        )
    )

    ORDER BY nombre_completo ASC
");

$stmt->execute();

$alertas = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   HEADER
========================= */
=======
declare(strict_types=1);

require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/permiso.php";

require_once __DIR__ . "/../../config/conexion.php";

require_once __DIR__ . "/../../services/actividadService.php";
require_once __DIR__ . "/../../services/seguimientoService.php";

require_once __DIR__ . "/../../helpers/format.php";
require_once __DIR__ . "/../../helpers/fechas.php";

/* =========================================================
   PERMISOS
========================================================= */

if (!tienePermiso("gestionar_seguimientos")) {

    header("Location: ../dashboard.php");
    exit;

}

/* =========================================================
   ACTUALIZAR ACTIVIDAD
========================================================= */

actualizarEstadoActividad($pdo);

/* =========================================================
   DATOS
========================================================= */

$resumen = obtenerResumenSeguimientosMes($pdo);

$seguimientosMes = obtenerHistorialSeguimientos($pdo);

$pendientes = obtenerJovenesPendientes($pdo);

/* =========================================================
   HEADER
========================================================= */
>>>>>>> 3e2d89c (Actualización del proyecto)

require_once __DIR__ . "/../../includes/header.php";

?>

<div class="seguimientos-page">

    <!-- =====================================
         PAGE HEADER
    ====================================== -->

    <div class="page-header">

        <div class="page-header-left">

            <h1 class="page-title">
<<<<<<< HEAD
                Seguimientos
            </h1>

            <div class="page-subtitle">
                Consolidado mensual del acompañamiento ministerial.
            </div>
=======

                Seguimientos

            </h1>

            <p class="page-subtitle">

                Control mensual del acompañamiento pastoral y discipulado.

            </p>
>>>>>>> 3e2d89c (Actualización del proyecto)

        </div>

        <div class="page-header-right">

            <span class="badge badge-primary">

<<<<<<< HEAD
                <?= e($mesTexto) ?>

            </span>

            <a
                href="reporte_pdf.php"
                target="_blank"
                class="btn btn-primary"
            >

                <i class="fa-solid fa-file-pdf"></i>

                Exportar PDF

            </a>
=======
                <?= e($resumen["mesTexto"]) ?>

            </span>

            <div class="export-dropdown">

                <button
                    type="button"
                    class="export-dropdown__trigger"
                >

                    <i class="fa-solid fa-download"></i>

                    Exportar

                    <i class="fa-solid fa-chevron-down"></i>

                </button>

                <div class="export-dropdown__menu">

                    <button
                        type="button"
                        class="export-option"
                        id="exportPdf"
                    >

                        <i class="fa-solid fa-file-pdf"></i>

                        PDF

                    </button>

                    <button
                        type="button"
                        class="export-option"
                        id="exportExcel"
                    >

                        <i class="fa-solid fa-file-excel"></i>

                        Excel

                    </button>

                    <button
                        type="button"
                        class="export-option"
                        id="exportWord"
                    >

                        <i class="fa-solid fa-file-word"></i>

                        Word

                    </button>

                    <button
                        type="button"
                        class="export-option"
                        id="exportCsv"
                    >

                        <i class="fa-solid fa-file-csv"></i>

                        CSV

                    </button>

                    <button
                        type="button"
                        class="export-option"
                        id="exportPrint"
                    >

                        <i class="fa-solid fa-print"></i>

                        Imprimir

                    </button>

                </div>

            </div>
>>>>>>> 3e2d89c (Actualización del proyecto)

        </div>

    </div>

    <!-- =====================================
         ESTADÍSTICAS
    ====================================== -->

    <div class="gx-stats">

<<<<<<< HEAD
        <div class="stat-card info">

            <span class="stat-number">

                <?= $totalActivos ?>

            </span>

            <span class="stat-label">

                Jóvenes activos

            </span>

        </div>

        <div class="stat-card success">

            <span class="stat-number">

                <?= $totalConSeguimiento ?>

            </span>

            <span class="stat-label">

                Con seguimiento

            </span>

        </div>

        <div class="stat-card danger">

            <span class="stat-number">

                <?= $totalSinSeguimiento ?>

            </span>

            <span class="stat-label">

                Sin seguimiento

            </span>

        </div>

        <div class="stat-card <?= e($color ?: 'orange') ?>">

            <span class="stat-number">

                <?= number_format($porcentaje, 0) ?>%

            </span>

            <span class="stat-label">

                Cumplimiento

            </span>

        </div>

    </div>

    <!-- =====================================
         ALERTAS
    ====================================== -->

    <?php if(count($alertas) > 0): ?>

        <div class="page-section">

            <div class="section-header">
=======
        <div class="gx-stat-card info">

            <div class="gx-stat-value">

                <?= $resumen["totalActivos"] ?>

            </div>

            <div class="gx-stat-label">

                Jóvenes activos

            </div>

        </div>

        <div class="gx-stat-card success">

            <div class="gx-stat-value">

                <?= $resumen["totalConSeguimiento"] ?>

            </div>

            <div class="gx-stat-label">

                Con seguimiento

            </div>

        </div>

        <div class="gx-stat-card warning">

            <div class="gx-stat-value">

                <?= $resumen["totalSinSeguimiento"] ?>

            </div>

            <div class="gx-stat-label">

                Pendientes

            </div>

        </div>

        <div class="gx-stat-card <?= e($resumen["color"]) ?>">

            <div class="gx-stat-value">

                <?= $resumen["porcentaje"] ?>%

            </div>

            <div class="gx-stat-label">

                Cumplimiento

            </div>

        </div>

    </div>                                                                                                                                         <!-- =====================================
         JÓVENES PENDIENTES
    ====================================== -->

    <div class="page-section">

        <div class="section-header">

            <div>
>>>>>>> 3e2d89c (Actualización del proyecto)

                <h2 class="section-title">

                    <i class="fa-solid fa-triangle-exclamation"></i>

<<<<<<< HEAD
                    Jóvenes sin seguimiento

                    <span class="section-counter">

                        <?= count($alertas) ?>

                    </span>

                </h2>

            </div>

            <p class="section-subtitle">

                Estos jóvenes aún no registran seguimiento durante el mes actual.

            </p>

            <div class="seguimientos-alertas">

                <?php foreach($alertas as $j): ?>

                    <div class="seguimiento-alerta-card">

                        <div class="seguimiento-alerta-left">

                            <div class="avatar">

                                <?= mb_strtoupper(
                                    mb_substr(
                                        $j["nombre_completo"],
                                        0,
                                        1
                                    )
                                ) ?>

                            </div>

                            <div>

                                <h4>

                                    <?= e($j["nombre_completo"]) ?>

                                </h4>

                                <span>

                                    <?= e(
                                        $j["telefono"]
                                        ?: "Sin teléfono"
                                    ) ?>

                                </span>

                            </div>

                        </div>

                        <div class="seguimiento-alerta-right">

                            <span class="badge badge-danger">

                                Pendiente

                            </span>

                            <a
                                href="../jovenes/ver.php?id=<?= $j["id"] ?>"
                                class="btn-mini <?= ($j["genero"] ?? '') === 'F'
                                    ? 'chica'
                                    : 'chico' ?>"
                            >

                                <i class="fa-solid fa-user"></i>

                                Ver perfil

                            </a>

                        </div>

                    </div>

                <?php endforeach; ?>
=======
                    Jóvenes pendientes de seguimiento

                </h2>

                <p class="section-subtitle">

                    Jóvenes activos que aún no registran seguimiento durante el mes actual.

                </p>

            </div>

            <div class="gx-toolbar">

                <div class="search-wrapper">

                    <input
                        type="text"
                        id="buscadorPendientes"
                        class="search-input"
                        placeholder="Buscar joven..."
                        autocomplete="off"
                    >

                </div>

                <span class="badge badge-danger">

                    <?= count($pendientes) ?>

                </span>
>>>>>>> 3e2d89c (Actualización del proyecto)

            </div>

        </div>

<<<<<<< HEAD
        <div class="section-divider"></div>

    <?php endif; ?>

    <!-- =====================================
         TABLA
    ====================================== -->

    <div class="page-section">

        <div class="section-header historial-header">

            <h2 class="section-title">

                <i class="fa-solid fa-clock-rotate-left"></i>

                Historial de seguimientos

            </h2>

            <div class="search-wrapper historial-search">

                <input
                    type="text"
                    id="buscador"
                    class="search-input"
                    placeholder="Buscar seguimiento..."
                    autocomplete="off"
                >

            </div>

        </div>

        <div class="table-responsive">

            <table
                id="tablaSeguimientos"
                class="table gx-table"
            >

                <thead>

                    <tr>

                        <th>Nombre</th>

                        <th>Modalidad</th>

                        <th>Estado</th>

                        <th>Responsable</th>

                        <th>Fecha</th>

                    </tr>

                </thead>

                <tbody>

                <?php if(empty($seguimientosMes)): ?>

                        <tr>

                            <td
                                colspan="5"
                                class="text-center"
                            >

                                No existen seguimientos registrados este mes.

                            </td>

                        </tr>

                    <?php else: ?>

                        <?php foreach($seguimientosMes as $s): ?>

                            <tr>

                                <td>

                                    <?= e($s["nombre_completo"]) ?>

                                </td>

                                <td>

                                    <?= ucfirst(
                                        strtolower(
                                            e($s["modalidad_contacto"])
                                        )
                                    ) ?>

                                </td>

                                <td>

                                    <span class="estado <?= strtolower(
                                        str_replace(
                                            '_',
                                            '-',
                                            $s["estado_proceso"]
                                        )
                                    ) ?>">

                                        <?= ucfirst(
                                            strtolower(
                                                str_replace(
                                                    '_',
                                                    ' ',
                                                    e($s["estado_proceso"])
                                                )
                                            )
                                        ) ?>

                                    </span>

                                </td>

                                <td>

                                    <?= e(
                                        $s["responsable_nombre"]
                                        ?? "-"
                                    ) ?>

                                </td>

                                <td>

                                    <?= formatearFecha(
                                        $s["fecha_contacto"]
                                    ) ?>
=======
        <div class="gx-table-container">

            <div class="table-responsive">

                <table
                    id="tablaPendientes"
                    class="table gx-table"
                >

                    <thead>

                        <tr>

                            <th>Joven</th>

                            <th>Teléfono</th>

                            <th>Estado</th>

                            <th class="text-center">

                                Perfil

                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php if (empty($pendientes)): ?>

                            <tr>

                                <td
                                    colspan="4"
                                    class="text-center"
                                >

                                    Todos los jóvenes ya cuentan con seguimiento este mes.
>>>>>>> 3e2d89c (Actualización del proyecto)

                                </td>

                            </tr>

<<<<<<< HEAD
                        <?php endforeach; ?>

                    <?php endif; ?>

                </tbody>

            </table>
=======
                        <?php else: ?>

                            <?php foreach ($pendientes as $joven): ?>

                                <tr>

                                    <td>

                                        <?= e($joven["nombre_completo"]) ?>

                                    </td>

                                    <td>

                                        <?= e($joven["telefono"] ?: "Sin teléfono") ?>

                                    </td>

                                    <td>

                                        <span class="badge badge-warning">

                                            Pendiente

                                        </span>

                                    </td>

                                    <td class="text-center">

                                        <a
                                            href="../jovenes/ver.php?id=<?= (int) $joven["id"] ?>"
                                            class="btn-mini <?= ($joven["genero"] ?? "") === "F" ? "chica" : "chico" ?>"
                                        >

                                            <i class="fa-solid fa-user"></i>

                                            Ver perfil

                                        </a>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>                                                                                                                                     <!-- =====================================
         HISTORIAL DE SEGUIMIENTOS
    ====================================== -->

    <div class="page-section">

        <div class="section-header">

            <div>

                <h2 class="section-title">

                    <i class="fa-solid fa-clock-rotate-left"></i>

                    Historial de seguimientos

                </h2>

                <p class="section-subtitle">

                    Seguimientos registrados durante el mes actual.

                </p>

            </div>

            <div class="gx-toolbar">

                <div class="search-wrapper">

                    <input
                        type="text"
                        id="buscador"
                        class="search-input"
                        placeholder="Buscar seguimiento..."
                        autocomplete="off"
                    >

                </div>

            </div>

        </div>

        <div class="gx-table-container">

            <div class="table-responsive">

                <table
                    id="tablaSeguimientos"
                    class="table gx-table"
                >

                    <thead>

                        <tr>

                            <th>Joven</th>

                            <th>Modalidad</th>

                            <th>Estado</th>

                            <th>Responsable</th>

                            <th>Fecha</th>

                            <th class="text-center">

                                Acciones

                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php if (empty($seguimientosMes)): ?>

                            <tr>

                                <td
                                    colspan="6"
                                    class="text-center"
                                >

                                    No existen seguimientos registrados durante este mes.

                                </td>

                            </tr>

                        <?php else: ?>

                            <?php foreach ($seguimientosMes as $seguimiento): ?>

                                <?php

                                $estado = $seguimiento["estado_proceso"];

                                $estadoClase = match ($estado) {

                                    "PENDIENTE"   => "badge-warning",

                                    "EN_PROCESO" => "badge-info",

                                    "FINALIZADO" => "badge-success",

                                    default      => "badge-secondary"

                                };

                                $modalidad = ucfirst(

                                    strtolower(

                                        $seguimiento["modalidad_contacto"]

                                    )

                                );

                                $estadoTexto = ucfirst(

                                    strtolower(

                                        str_replace(

                                            "_",

                                            " ",

                                            $estado

                                        )

                                    )

                                );

                                ?>

                                <tr>

                                    <td>

                                        <?= e($seguimiento["nombre_completo"]) ?>

                                    </td>

                                    <td>

                                        <?= $modalidad ?>

                                    </td>

                                    <td>

                                        <span class="badge <?= $estadoClase ?>">

                                            <?= $estadoTexto ?>

                                        </span>

                                    </td>

                                    <td>

                                        <?= e($seguimiento["responsable_nombre"] ?? "-") ?>

                                    </td>

                                    <td>

                                        <?= formatearFecha(

                                            $seguimiento["fecha_contacto"]

                                        ) ?>

                                    </td>

                                    <td>

                                        <div class="table-actions">

                                            <a
                                                href="../jovenes/ver.php?id=<?= (int) $seguimiento["joven_id"] ?>"
                                                class="btn-icon btn-view"
                                                title="Ver perfil"
                                            >

                                                <i class="fa-solid fa-user"></i>

                                            </a>

                                            <a
                                                href="editar.php?id=<?= (int) $seguimiento["id"] ?>"
                                                class="btn-icon btn-edit"
                                                title="Editar"
                                            >

                                                <i class="fa-solid fa-pen"></i>

                                            </a>

                                            <form
                                                action="<?= BASE_URL ?>/controllers/seguimientoController.php"
                                                method="POST"
                                                class="inline-form"
                                                onsubmit="return confirm('¿Deseas eliminar este seguimiento?');"
                                            >

                                                <input
                                                    type="hidden"
                                                    name="id"
                                                    value="<?= (int) $seguimiento["id"] ?>"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="action"
                                                    value="eliminar"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="csrf_token"
                                                    value="<?= htmlspecialchars($_SESSION["csrf_token"]) ?>"
                                                >

                                                <button
                                                    type="submit"
                                                    class="btn-icon btn-delete"
                                                    title="Eliminar"
                                                >

                                                    <i class="fa-solid fa-trash"></i>

                                                </button>

                                            </form>

                                        </div>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>
>>>>>>> 3e2d89c (Actualización del proyecto)

        </div>

    </div>

<<<<<<< HEAD
    <!-- =====================================
         BOTONES
    ====================================== -->

    <div class="form-actions">

        <a
            href="../dashboard.php"
            class="btn btn-back"
        >

            <i class="fa-solid fa-arrow-left"></i>

            Volver

        </a>

    </div>

</div>

<script
    src="<?= BASE_URL ?>/assets/js/modulos/seguimientos/index.js">
</script>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>
=======
  

</div>

<!-- =====================================
     JAVASCRIPT
====================================== -->

<script src="<?= BASE_URL ?>/assets/js/modulos/seguimientos/index.js"></script>

<?php

require_once __DIR__ . "/../../includes/footer.php";
>>>>>>> 3e2d89c (Actualización del proyecto)
