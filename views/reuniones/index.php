<?php
require_once "../../middleware/auth.php";
require_once "../../middleware/permiso.php";
require_once "../../config/conexion.php";

if (!tienePermiso('gestionar_reuniones')) {
    header("Location: ../dashboard.php");
    exit;
}

/* FILTRO */
$tipos = ["todos", "REUNION_JOVENES", "GRUPO_CONEXION", "EVENTO_ESPECIAL"];
$filtro = $_GET["tipo"] ?? "todos";
if (!in_array($filtro, $tipos)) $filtro = "todos";

/* QUERY */
$query = "
SELECT r.*,
COUNT(a.id) as total_registros,
SUM(a.asistio = 1) as asistieron
FROM reuniones r
LEFT JOIN asistencia a ON a.reunion_id = r.id
";

if ($filtro !== "todos") {
    $query .= " WHERE r.tipo = :tipo";
}

$query .= " GROUP BY r.id ORDER BY r.fecha DESC";

$stmt = $pdo->prepare($query);

$filtro !== "todos"
    ? $stmt->execute(["tipo"=>$filtro])
    : $stmt->execute();

$reuniones = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* CSS */
$extraCSS = '<link rel="stylesheet" href="' . BASE_URL . '/assets/css/modules/reuniones/reuniones.css">';
require_once "../../includes/header.php";
?>

<div class="reuniones">
<div class="page">

    <!-- HEADER -->

    <div class="page-header">

        <div class="page-header-left">

            <h1 class="page-title">
                Gestión de Reuniones
            </h1>

            <div class="page-subtitle">
                Administra reuniones, asistencia y eventos especiales
            </div>

        </div>

        <div class="page-header-right">

            <a
                href="crear.php"
                class="btn btn-primary"
            >
                <i class="fa-solid fa-plus"></i>
                Nueva reunión
            </a>

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

        </div>

    </div>

    <!-- FILTROS -->

    <div class="page-section">

        <div class="reuniones-filtros">

            <div class="reuniones-tipos">

                <a
                    href="?tipo=todos"
                    class="<?= $filtro == 'todos' ? 'active' : '' ?>"
                >
                    Todos
                </a>

                <a
                    href="?tipo=REUNION_JOVENES"
                    class="<?= $filtro == 'REUNION_JOVENES' ? 'active' : '' ?>"
                >
                    Reunión
                </a>

                <a
                    href="?tipo=GRUPO_CONEXION"
                    class="<?= $filtro == 'GRUPO_CONEXION' ? 'active' : '' ?>"
                >
                    Conexión
                </a>

                <a
                    href="?tipo=EVENTO_ESPECIAL"
                    class="<?= $filtro == 'EVENTO_ESPECIAL' ? 'active' : '' ?>"
                >
                    Evento
                </a>

            </div>

            <div class="reuniones-tools">

                <input
                    type="month"
                    class="form-control"
                    id="filtroMes"
                >

                <input
                    type="text"
                    id="buscador"
                    class="search-input"
                    placeholder="Buscar reunión..."
                >

            </div>

        </div>

    </div>

    <!-- TABLA -->

    <div class="page-section">

        <div class="table-wrapper">

            <table
                id="tablaReuniones"
                class="table"
            >

                <thead>

                    <tr>

                        <th>Fecha</th>

                        <th>Tipo</th>

                        <th>Registros</th>

                        <th>Asistencia</th>

                        <th>%</th>

                        <th>Acciones</th>

                    </tr>

                </thead>

                <tbody>

                    <?php foreach($reuniones as $r):

                        $total = $r["total_registros"] ?? 0;

                        $asistieron = $r["asistieron"] ?? 0;

                        $porcentaje =
                            $total > 0
                            ? round(($asistieron / $total) * 100, 1)
                            : 0;

                        $tipoBonito = match($r["tipo"]) {

                            "REUNION_JOVENES" => "Reunión",

                            "GRUPO_CONEXION" => "Conexión",

                            "EVENTO_ESPECIAL" => "Evento",

                            default => $r["tipo"]
                        };

                    ?>

                    <tr>

                        <td>
                            <?= $r["fecha"] ?>
                        </td>

                        <td>

                            <span
                                class="badge tipo-<?= strtolower($r["tipo"]) ?>"
                            >

                                <?= $tipoBonito ?>

                            </span>

                        </td>

                        <td>
                            <?= $total ?>
                        </td>

                        <td>
                            <?= $asistieron ?>
                        </td>

                        <td>

                            <span
                                class="porcentaje <?= $porcentaje >= 70 ? 'alto' : ($porcentaje >= 40 ? 'medio' : 'bajo') ?>"
                            >

                                <?= $porcentaje ?>%

                            </span>

                        </td>

                        <td>

                            <div class="table-actions">

                                <a
                                    href="marcar.php?reunion_id=<?= $r["id"] ?>"
                                    class="btn-icon btn-success"
                                    data-tooltip="Marcar asistencia"
                                >
                                    <i class="fa-solid fa-clipboard-check"></i>
                                </a>

                                <a
                                    href="ver.php?id=<?= $r["id"] ?>"
                                    class="btn-icon btn-view"
                                    data-tooltip="Ver informe"
                                >
                                    <i class="fa-solid fa-eye"></i>
                                </a>

                                <a
                                    href="editar.php?id=<?= $r["id"] ?>"
                                    class="btn-icon btn-edit"
                                    data-tooltip="Editar"
                                >
                                    <i class="fa-solid fa-pen"></i>
                                </a>

                                <a
                                    href="../../controllers/reunionController.php?eliminar=<?= $r["id"] ?>"
                                    class="btn-icon btn-delete"
                                    data-tooltip="Eliminar"
                                    onclick="return confirm('¿Eliminar reunión?')"
                                >
                                    <i class="fa-solid fa-trash"></i>
                                </a>

                            </div>

                        </td>

                    </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<script>

document.addEventListener('DOMContentLoaded', () => {

    const tabla =
    initDataTable('#tablaReuniones');

    if(tabla){

        initSearch(
            'buscador',
            tabla
        );

        initExportButtons(
            tabla
        );
    }

});

</script>

<?php require_once "../../includes/footer.php"; ?>