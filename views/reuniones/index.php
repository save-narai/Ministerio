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

    <!-- HEADER -->
    <div class="reuniones__header">
       <h1 class="reuniones__title">Gestión de Reuniones</h1>

        <div class="top-actions">
            <a href="crear.php" class="reuniones__btn reuniones__btn--top">
    Nueva reunión
</a>
        </div>
    </div>

    <!-- FILTROS -->
    <div class="reuniones__filtros">
        <a href="?tipo=todos" class="<?= $filtro=='todos'?'active':'' ?>">Todos</a>
        <a href="?tipo=REUNION_JOVENES" class="<?= $filtro=='REUNION_JOVENES'?'active':'' ?>">Reunión</a>
        <a href="?tipo=GRUPO_CONEXION" class="<?= $filtro=='GRUPO_CONEXION'?'active':'' ?>">Conexión</a>
        <a href="?tipo=EVENTO_ESPECIAL" class="<?= $filtro=='EVENTO_ESPECIAL'?'active':'' ?>">Evento</a>
    </div>

    <!-- TABLA -->
    <div class="reuniones__table">
        <table class="tabla">
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
                    $porcentaje = $total>0 ? round(($asistieron/$total)*100,1):0;

                    $tipoBonito = match($r["tipo"]) {
                        "REUNION_JOVENES"=>"Reunión",
                        "GRUPO_CONEXION"=>"Conexión",
                        "EVENTO_ESPECIAL"=>"Evento",
                        default=>$r["tipo"]
                    };
                ?>
                <tr>
                    <td><?= $r["fecha"] ?></td>

                    <td>
                        <span class="badge tipo-<?= strtolower($r["tipo"]) ?>">
                            <?= $tipoBonito ?>
                        </span>
                    </td>

                    <td><?= $total ?></td>
                    <td><?= $asistieron ?></td>

                    <td>
                        <span class="porcentaje 
                        <?= $porcentaje>=70?'alto':($porcentaje>=40?'medio':'bajo') ?>">
                            <?= $porcentaje ?>%
                        </span>
                    </td>

                    <td class="acciones">
                        <a href="marcar.php?reunion_id=<?= $r["id"] ?>" class="btn editar">Marcar</a>
                        <a href="ver.php?id=<?= $r["id"] ?>" class="btn ver">Ver</a>
                        <a href="editar.php?id=<?= $r["id"] ?>" class="btn editar">Editar</a>
                        <a href="../../controllers/reunionController.php?eliminar=<?= $r["id"] ?>"
                           class="btn eliminar"
                           onclick="return confirm('¿Eliminar reunión?')">
                           Eliminar
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<?php require_once "../../includes/footer.php"; ?>