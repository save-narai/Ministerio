<?php

require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/permiso.php";
require_once __DIR__ . "/../../config/conexion.php";
require_once __DIR__ . "/../../services/discipuladoService.php";


/* =========================================================
   PERMISOS
========================================================= */

if (!tienePermiso('gestionar_reuniones')) {

    header("Location: ../dashboard.php");
    exit;
}


/* =========================================================
   VALIDAR ID
========================================================= */

if (!isset($_GET["id"])) {

    die("ID inválido");
}

$reunion_id = (int) $_GET["id"];


/* =========================================================
   OBTENER REUNIÓN
========================================================= */

$stmt = $pdo->prepare("
    SELECT *
    FROM reuniones
    WHERE id = ?
");

$stmt->execute([
    $reunion_id
]);

$reunion = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reunion) {

    die("No existe");
}


/* =========================================================
   VÍNCULO CON DISCIPULADO (FASE 7)

   Solo existe si esta reunión fue creada/editada con
   tipo = Discipulado y se le asoció ciclo + clase. Si no
   existe vínculo, esta reunión no afecta el progreso de
   discipulado (aunque sea de tipo Discipulado).
========================================================= */

$vinculoDiscipulado = obtenerVinculoReunionDiscipulado($pdo, $reunion_id);

$asistentesSinInscripcionDiscipulado =
    $vinculoDiscipulado
        ? obtenerAsistentesSinInscripcionDiscipulado(
            $pdo,
            $reunion_id,
            (int)$vinculoDiscipulado['ciclo_id']
        )
        : [];


/* =========================================================
   OBTENER DATA DE ASISTENCIA
========================================================= */

$stmt = $pdo->prepare("
    SELECT

        j.nombre_completo,

        j.es_servidor,

        a.asistio,

        a.grupo_edad,

        a.participa_discipulado,

        a.grupo_conexion,

        a.primera_vez

    FROM asistencia a

    JOIN jovenes j
        ON j.id = a.joven_id

    WHERE a.reunion_id = ?

    ORDER BY j.nombre_completo ASC
");

$stmt->execute([
    $reunion_id
]);

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================================================
   ESTADÍSTICAS GENERALES
========================================================= */

$total = count($data);

$asistieron = 0;

$servidores = 0;
$servidoresAsist = 0;

$participaronDiscipulado = 0;
$primeraVezDiscipulado = 0;

$participaronConexion = 0;


/* =========================================================
   RECORRER PARTICIPANTES
========================================================= */

foreach ($data as $d) {


    /* -----------------------------------------------------
       ASISTENCIA
    ----------------------------------------------------- */

    if (
        (int)($d["asistio"] ?? 0) === 1
    ) {

        $asistieron++;
    }


    /* -----------------------------------------------------
       SERVIDORES
    ----------------------------------------------------- */

    if (
        (int)($d["es_servidor"] ?? 0) === 1
    ) {

        $servidores++;

        if (
            (int)($d["asistio"] ?? 0) === 1
        ) {

            $servidoresAsist++;
        }
    }


    /* -----------------------------------------------------
       DISCIPULADO
       
       Solo contamos personas que:
       1. Asistieron
       2. Están en discipulado
    ----------------------------------------------------- */

    if (
        (int)($d["participa_discipulado"] ?? 0) === 1
        &&
        (int)($d["asistio"] ?? 0) === 1
    ) {

        $participaronDiscipulado++;
    }

    /* -----------------------------------------------------
       PRIMERA VEZ EN LA REUNIÓN

       Solo contamos personas que:
       1. Asistieron
       2. Es su primera vez
    ----------------------------------------------------- */

    if (
        (int)($d["primera_vez"] ?? 0) === 1
        &&
        (int)($d["asistio"] ?? 0) === 1
    ) {

        $primeraVezDiscipulado++;

    }


    /* -----------------------------------------------------
       GRUPO DE CONEXIÓN

       Solo contamos personas que:
       1. Asistieron
       2. Pertenecen a conexión
    ----------------------------------------------------- */

    if (
        (int)($d["grupo_conexion"] ?? 0) === 1
        &&
        (int)($d["asistio"] ?? 0) === 1
    ) {

        $participaronConexion++;

    }
}

/* =========================================================
   PORCENTAJE GENERAL DE ASISTENCIA
========================================================= */

$porcentaje = $total > 0

    ? round(
        ($asistieron / $total) * 100
    )

    : 0;


/* =========================================================
   PORCENTAJE EN DISCIPULADO

   Se calcula sobre las personas que asistieron.
========================================================= */

$porcentajeDiscipulado = $asistieron > 0

    ? round(
        ($participaronDiscipulado / $asistieron) * 100
    )

    : 0;


/* =========================================================
   PORCENTAJE PRIMERA VEZ

   Se calcula sobre las personas que asistieron.
========================================================= */

$porcentajePrimeraVez = $asistieron > 0

    ? round(
        ($primeraVezDiscipulado / $asistieron) * 100
    )

    : 0;


/* =========================================================
   PORCENTAJE EN GRUPO DE CONEXIÓN

   Se calcula sobre las personas que asistieron.
========================================================= */

$porcentajeConexion = $asistieron > 0

    ? round(
        ($participaronConexion / $asistieron) * 100
    )

    : 0;


/* =========================================================
   CONFIGURACIÓN EXTRA
========================================================= */

$mostrarDiscipulado = in_array(

    $reunion["tipo"],

    [
        "GRUPO_CONEXION",
        "DISCIPULADO"
    ],

    true

);


/* =========================================================
   ESTADO DE ASISTENCIA
========================================================= */

$estadoAsistencia =

    $porcentaje >= 70

        ? "Excelente"

        : (

            $porcentaje >= 40

                ? "Regular"

                : "Baja"

        );


/* =========================================================
   TIPO DE REUNIÓN BONITO
========================================================= */

$tipoBonito = match ($reunion["tipo"]) {

    "REUNION_JOVENES" =>
        "Reunión Jóvenes",

    "GRUPO_CONEXION" =>
        "Grupo Conexión",

    "DISCIPULADO" =>
        "Discipulado",

    "EVENTO_ESPECIAL" =>
        "Evento Especial",

    default =>
        $reunion["tipo"]

};

require_once __DIR__ . "/../../includes/header.php";
?>


<!-- =========================================================
     HEADER
========================================================= -->

<div class="page-header">

    <div class="page-header-left">

        <h1 class="page-title">
            Informe de Reunión
        </h1>

        <div class="page-subtitle">

            <?= htmlspecialchars($tipoBonito) ?>

            ·

            <?= date(
                "d/m/Y",
                strtotime($reunion["fecha"])
            ) ?>

        </div>

    </div>


    <div class="page-header-right">

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

                <!-- PDF -->

                <a
                    href="reporte_reunion_pdf.php?id=<?= $reunion_id ?>"
                    target="_blank"
                    class="export-option"
                >

                    <i class="fa-solid fa-file-pdf"></i>

                    PDF

                </a>


                <!-- EXCEL -->

                <button
                    type="button"
                    class="export-option"
                    id="exportExcel"
                >

                    <i class="fa-solid fa-file-excel"></i>

                    Excel

                </button>


                <!-- WORD -->

                <button
                    type="button"
                    class="export-option"
                    id="exportWord"
                >

                    <i class="fa-solid fa-file-word"></i>

                    Word

                </button>


                <!-- CSV -->

                <button
                    type="button"
                    class="export-option"
                    id="exportCsv"
                >

                    <i class="fa-solid fa-file-csv"></i>

                    CSV

                </button>


                <!-- IMPRIMIR -->

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

    </div>

</div>

<br>
<!-- =========================================================
     ESTADÍSTICAS
========================================================= -->

<div class="stats-grid">


    <!-- =====================================================
         TOTAL DE REGISTROS
    ====================================================== -->

    <div class="stat-card info">

        <span class="stat-number">
            <?= $total ?>
        </span>

        <span class="stat-label">
            Total registros
        </span>

    </div>


    <!-- =====================================================
         ASISTIERON
    ====================================================== -->

    <div class="stat-card success">

        <span class="stat-number">
            <?= $asistieron ?>
        </span>

        <span class="stat-label">
            Asistieron
        </span>

    </div>


    <!-- =====================================================
         PORCENTAJE DE ASISTENCIA
    ====================================================== -->

    <div class="stat-card info">

        <span class="stat-number">
            <?= $porcentaje ?>%
        </span>

        <span class="stat-label">
            Asistencia
        </span>

    </div>


    <!-- =====================================================
         SERVIDORES
    ====================================================== -->

    <div class="stat-card purple">

        <span class="stat-number">
            <?= $servidoresAsist ?>/<?= $servidores ?>
        </span>

        <span class="stat-label">
            Servidores
        </span>

    </div>


    <!-- =====================================================
         EN DISCIPULADO
    ====================================================== -->

    <div class="stat-card purple">

        <span class="stat-number">
            <?= $participaronDiscipulado ?>
        </span>

        <span class="stat-label">
            En discipulado
        </span>

    </div>


    <!-- =====================================================
         PRIMERA VEZ
    ====================================================== -->

    <div class="stat-card success">

        <span class="stat-number">
            <?= $primeraVezDiscipulado ?>
        </span>

        <span class="stat-label">
            Primera vez
        </span>

    </div>


    <!-- =====================================================
         EN CONEXIÓN
    ====================================================== -->

    <div class="stat-card purple">

        <span class="stat-number">
            <?= $participaronConexion ?>
        </span>

        <span class="stat-label">
            En conexión
        </span>

    </div>


</div>



<?php if ($vinculoDiscipulado): ?>

    <div class="page-section">

        <p>
            <strong>Discipulado:</strong>
            Ciclo "<?= htmlspecialchars($vinculoDiscipulado['ciclo_nombre']) ?>" ·
            Clase <?= (int)$vinculoDiscipulado['numero_orden'] ?> — <?= htmlspecialchars($vinculoDiscipulado['clase_nombre']) ?> ·
            Modalidad <?= htmlspecialchars($vinculoDiscipulado['modalidad']) ?>
            <?php if ($vinculoDiscipulado['es_recuperacion']): ?>
                · <span class="badge badge-warning">Recuperación</span>
            <?php endif; ?>
            <br>
            <a href="<?= BASE_URL ?>/views/formacion/discipulado/ver.php?ciclo_id=<?= (int)$vinculoDiscipulado['ciclo_id'] ?>">
                Ver ciclo de discipulado
            </a>
        </p>

        <?php if (!empty($asistentesSinInscripcionDiscipulado)): ?>

            <p>
                <span class="badge badge-warning">Aviso</span>
                <?= count($asistentesSinInscripcionDiscipulado) ?> joven(es) asistieron pero no tienen una inscripción activa en este ciclo de discipulado, así que no se registró progreso para ellos (su asistencia general sí quedó guardada):
                <?= htmlspecialchars(implode(', ', array_column($asistentesSinInscripcionDiscipulado, 'nombre_completo'))) ?>
            </p>

        <?php endif; ?>

    </div>

<?php endif; ?>

<!-- =========================================================
     BUSCADOR
========================================================= -->

<div class="search-bar reunion-search">

    <input
        type="text"
        id="buscarParticipante"
        class="search-input"
        placeholder="Buscar participante..."
        autocomplete="off"
    >

</div>


<br>


<!-- =========================================================
     FILTROS
========================================================= -->

<div class="filters-bar reunion-filters">

    <button
        type="button"
        class="filter-chip filter-chip--active"
        data-filter="todos"
    >
        Todos
    </button>


    <button
        type="button"
        class="filter-chip"
        data-filter="asistio"
    >
        Asistieron
    </button>


    <button
        type="button"
        class="filter-chip"
        data-filter="falto"
    >
        Faltaron
    </button>


    <button
        type="button"
        class="filter-chip"
        data-filter="teen"
    >
        Teen
    </button>


    <button
        type="button"
        class="filter-chip"
        data-filter="remanente"
    >
        Remanente
    </button>

</div>


<!-- =========================================================
     TABLA
========================================================= -->


<!-- =========================================================
     PARTICIPANTES
========================================================= -->


<div class="page-section">

    <div class="table-wrapper">

    <h3 class="page-section-title">
    Participantes registrados
</h3>



        <table
            id="tablaParticipantes"
            class="table"
        >

        <thead>

            <tr>

                <th>
                    Nombre
                </th>

                <th>
                    Servidor
                </th>

                <th>
                    Grupo
                </th>

                <th>
                    Discipulado
                </th>

                <th>
                    Primera vez
                </th>

                <th>
                    Conexión
                </th>

                <th>
                    Asistencia
                </th>

            </tr>

        </thead>


    <tbody>

    <?php foreach ($data as $d): ?>

        <?php

        $grupo = strtolower(
            trim(
                $d["grupo_edad"] ?? ""
            )
        );

        $asistio = (
            (int)($d["asistio"] ?? 0) === 1
        );

        $esServidor = (
            (int)($d["es_servidor"] ?? 0) === 1
        );

        $enDiscipulado = (
            (int)($d["participa_discipulado"] ?? 0) === 1
        );

        $primeraVez = (
            (int)($d["primera_vez"] ?? 0) === 1
        );

        $enConexion = (
            (int)($d["grupo_conexion"] ?? 0) === 1
        );

        ?>

        <tr
            data-grupo="<?= htmlspecialchars($grupo) ?>"
            data-asistencia="<?= $asistio
                ? 'asistio'
                : 'falto'
            ?>"
        >


                    <!-- NOMBRE -->

                    <td>

                        <?= htmlspecialchars(
                            $d["nombre_completo"] ?? ""
                        ) ?>

                    </td>


                    <!-- SERVIDOR -->

                    <td>

                        <?= $esServidor
                            ? "Sí"
                            : "No"
                        ?>

                    </td>


                    <!-- GRUPO -->

                    <td>

                        <?= htmlspecialchars(
                            $d["grupo_edad"] ?? "-"
                        ) ?>

                    </td>


                    <!-- DISCIPULADO -->

                    <td>

                        <?php if ($enDiscipulado): ?>

                            <span class="badge badge-success">
                                Sí
                            </span>

                        <?php else: ?>

                            <span class="badge badge-secondary">
                                -
                            </span>

                        <?php endif; ?>

                    </td>


                    <!-- PRIMERA VEZ -->

                    <td>

                        <?php if ($primeraVez): ?>

                            <span class="badge badge-success">
                                Sí
                            </span>

                        <?php else: ?>

                            <span class="badge badge-secondary">
                                -
                            </span>

                        <?php endif; ?>

                    </td>


                    <!-- CONEXIÓN -->

                    <td>

                        <?php if ($enConexion): ?>

                            <span class="badge badge-success">
                                Sí
                            </span>

                        <?php else: ?>

                            <span class="badge badge-secondary">
                                -
                            </span>

                        <?php endif; ?>

                    </td>


                    <!-- ASISTENCIA -->

                    <td>

                        <?php if ($asistio): ?>

                            <span class="badge badge-success">
                                Asistió
                            </span>

                        <?php else: ?>

                            <span class="badge badge-danger">
                                Faltó
                            </span>

                        <?php endif; ?>

                    </td>

                </tr>

            <?php endforeach; ?>

        </tbody>

         </table>

    </div>

</div>

<!-- =========================================================
     BOTONES
========================================================= -->

<div class="btn-group">

    <a
        href="index.php"
        class="btn btn-secondary"
    >

        Volver

    </a>

</div>


<!-- =========================================================
     JAVASCRIPT
========================================================= -->

<script
    src="<?= BASE_URL ?>/assets/js/modulos/reuniones/ver.js"
></script>


<?php

require_once __DIR__ . "/../../includes/footer.php";

