<?php

require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/permiso.php";
require_once __DIR__ . "/../../config/conexion.php";

if (!tienePermiso('gestionar_reuniones')) {

    header("Location: ../dashboard.php");
    exit;
}

if (!isset($_GET["id"])) {

    die("ID inválido");
}

$reunion_id = (int)$_GET["id"];

/* =========================
   REUNIÓN
========================= */

$stmt = $pdo->prepare("
    SELECT *
    FROM reuniones
    WHERE id = ?
");

$stmt->execute([$reunion_id]);

$reunion = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reunion) {

    die("No existe");
}

/* =========================
   DATA
========================= */

$stmt = $pdo->prepare("
SELECT

    j.nombre_completo,

    j.es_servidor,

    a.asistio,

    a.grupo_edad,

    a.participa_discipulado,

    a.primera_vez_discipulado

FROM asistencia a

JOIN jovenes j
    ON j.id = a.joven_id

WHERE a.reunion_id = ?
");

$stmt->execute([$reunion_id]);

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   STATS
========================= */

$total = count($data);

$asistieron = 0;
$servidores = 0;
$servidoresAsist = 0;
$conexion = 0;
$discipulado = 0;

foreach ($data as $d) {

    if ($d["asistio"]) {
        $asistieron++;
    }

    if ($d["es_servidor"]) {

        $servidores++;

        if ($d["asistio"]) {
            $servidoresAsist++;
        }
    }

    if ($d["participa_discipulado"]) {
        $discipulado++;
    }

    if ($d["primera_vez_discipulado"]) {
        $conexion++;
    }
}

$porcentaje = $total > 0
    ? round(($asistieron / $total) * 100)
    : 0;

/* =========================
   CONFIG EXTRA
========================= */

$mostrarDiscipulado = in_array(
    $reunion["tipo"],
    ["GRUPO_CONEXION", "DISCIPULADO"]
);

$estadoAsistencia =
    $porcentaje >= 70
        ? "Excelente"
        : (
            $porcentaje >= 40
                ? "Regular"
                : "Baja"
        );

/* =========================
   TIPO BONITO
========================= */

$tipoBonito = match($reunion["tipo"]) {

    "REUNION_JOVENES" => "Reunión Jóvenes",
    "GRUPO_CONEXION" => "Grupo Conexión",
    "DISCIPULADO" => "Discipulado",
    "EVENTO_ESPECIAL" => "Evento Especial",

    default => $reunion["tipo"]
};



require_once __DIR__ . "/../../includes/header.php";

?>

<div class="page">

    <!-- HEADER -->

    <div class="page-header">

        <div class="page-header-left">

            <h1 class="page-title">
                Informe de Reunión
            </h1>

            <div class="page-subtitle">

                <?= $tipoBonito ?>

                ·

                <?= date("d/m/Y", strtotime($reunion["fecha"])) ?>

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

                    <a
                        href="reporte_reunion_pdf.php?id=<?= $reunion_id ?>"
                        target="_blank"
                        class="export-option"
                    >

                        <i class="fa-solid fa-file-pdf"></i>

                        PDF

                    </a>

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

        </div>

    </div>

   

<!-- ESTADÍSTICAS -->

<div class="stats-grid reunion-stats">

    <div class="stat-card info">

        <span class="stat-number">
            <?= $total ?>
        </span>

        <span class="stat-label">
            Total registros
        </span>

    </div>

    <div class="stat-card success">

        <span class="stat-number">
            <?= $asistieron ?>
        </span>

        <span class="stat-label">
            Asistieron
        </span>

    </div>

    <div class="stat-card info">

        <span class="stat-number">
            <?= $porcentaje ?>%
        </span>

        <span class="stat-label">
            Asistencia
        </span>

    </div>

    <div class="stat-card purple">

        <span class="stat-number">
            <?= $servidoresAsist ?>/<?= $servidores ?>
        </span>

        <span class="stat-label">
            Servidores
        </span>

    </div>

</div>
<!-- TABLA -->

<div class="page-section">

    <h3 class="page-section-title">
        Participantes registrados
    </h3>

    <div class="search-bar reunion-search">

        <input
            type="text"
            id="buscarParticipante"
            class="search-input"
            placeholder="Buscar participante..."
        >

    </div>

    <br>

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

    <div class="table-wrapper">

        <table
            id="tablaParticipantes"
            class="table"
        >

            <thead>

                <tr>

                    <th>Nombre</th>
                    <th>Servidor</th>
                    <th>Grupo</th>

                    <?php if($mostrarDiscipulado): ?>

                        <th>Discipulado</th>
                        <th>Primera vez</th>

                    <?php endif; ?>

                    <th>Asistencia</th>

                </tr>

            </thead>

            <tbody>

                <?php foreach($data as $d): ?>

                <tr
                    data-grupo="<?= strtolower($d["grupo_edad"] ?? '') ?>"
                    data-asistencia="<?= $d["asistio"] ? 'asistio' : 'falto' ?>"
                >

                    <td>
                        <?= htmlspecialchars($d["nombre_completo"]) ?>
                    </td>

                    <td>
                        <?= $d["es_servidor"] ? "Sí" : "No" ?>
                    </td>

                    <td>
                        <?= $d["grupo_edad"] ?? "-" ?>
                    </td>

                    <?php if($mostrarDiscipulado): ?>

                    <td>
                        <?= $d["participa_discipulado"] ? "✔" : "-" ?>
                    </td>

                    <td>
                        <?= $d["primera_vez_discipulado"] ? "✔" : "-" ?>
                    </td>

                    <?php endif; ?>

                    <td>

                        <?php if($d["asistio"]): ?>

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

    <!-- BOTONES -->

    <div class="btn-group">

        <a
            href="index.php"
            class="btn btn-secondary"
        >

      

            Volver

        </a>

    </div>

</div>


<script
    src="<?= BASE_URL ?>/assets/js/modulos/reuniones/ver.js">
</script>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>