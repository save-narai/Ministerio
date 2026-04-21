<?php
require_once "../../middleware/auth.php";
require_once "../../middleware/permiso.php";
require_once "../../config/conexion.php";

if (!tienePermiso('gestionar_reuniones')){
    die("No tienes permiso.");
}

/* =====================================
   DESACTIVAR DISCIPULADOS VENCIDOS
===================================== */
$pdo->prepare("
    UPDATE jovenes
    SET discipulado_activo = 0,
        es_nuevo = 0
    WHERE discipulado_activo = 1
    AND discipulado_fin <= CURDATE()
")->execute();

$reunion_id = isset($_GET["reunion_id"]) ? (int)$_GET["reunion_id"] : null;

/* ============================
   CREAR REUNIÓN
============================ */
if (!$reunion_id) {
?>
    <h2>Crear Reunión</h2>

    <form action="../../controllers/asistenciaController.php" method="POST">
        <label>Tipo:</label>
        <select name="tipo" required>
            <option value="REUNION_JOVENES">Reunión Jóvenes</option>
<option value="GRUPO_CONEXION">Grupo de Conexión</option>
<option value="EVENTO_ESPECIAL">Evento Especial</option>
        </select>

        <br><br>

        <label>Fecha:</label>
        <input type="date" name="fecha" required>

        <br><br>

        <button type="submit" name="crear_reunion">
            Crear
        </button>
    </form>
<?php
    exit();
}

/* ============================
   MARCAR ASISTENCIA
============================ */

// Obtener tipo reunión
$stmt = $pdo->prepare("SELECT tipo FROM reuniones WHERE id = ?");
$stmt->execute([$reunion_id]);
$reunion = $stmt->fetch(PDO::FETCH_ASSOC);
$tipoReunion = $reunion["tipo"] ?? "";

// Obtener jóvenes activos
$jovenes = $pdo->query("
    SELECT *,
    TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) AS edad
    FROM jovenes
    WHERE estado_actividad = 'ACTIVO'
    ORDER BY nombre_completo ASC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Marcar Asistencia</h2>

<form action="../../controllers/asistenciaController.php" method="POST">
<input type="hidden" name="reunion_id" value="<?= $reunion_id ?>">

<table border="1" cellpadding="5">

<tr>
    <th>Nombre</th>
    <th>Grupo Edad</th>
    <th>Asistió</th>
    <th>Estado</th>
    <th>Grupo Conexión</th>
    <th>Primera Vez Discipulado</th>

    <?php if ($tipoReunion === "GRUPO_CONEXION"): ?>
        <th>Discipulado</th>
    <?php endif; ?>
</tr>

<?php foreach($jovenes as $j):

    if($j["edad"] >= 15 && $j["edad"] <= 17){
        $grupo = "TEENAGERS";
    } else {
        $grupo = "REMANENTE";
    }
?>

<tr>
    <td><?= htmlspecialchars($j["nombre_completo"]) ?></td>
    <td><?= htmlspecialchars($grupo) ?></td>

    <td>
        <input type="checkbox" name="asistencia[]" value="<?= $j["id"] ?>">
    </td>

    <td>
        <?= $j["es_nuevo"] ? "NUEVO" : "ANTIGUO" ?>
    </td>

    <td>
        <input type="checkbox" name="conexion[]" value="<?= $j["id"] ?>">
    </td>

    <td>
        <input type="checkbox" name="primera_vez[]" value="<?= $j["id"] ?>">
    </td>

    <?php if ($tipoReunion === "GRUPO_CONEXION"): ?>
        <td>
            <input type="checkbox" name="discipulado[]" value="<?= $j["id"] ?>">
        </td>
    <?php endif; ?>

    <input
        type="hidden"
        name="grupo_edad[<?= $j["id"] ?>]"
        value="<?= htmlspecialchars($grupo) ?>"
    >
</tr>

<?php endforeach; ?>

</table>

<br>

<button type="submit" name="guardar_asistencia">
    Guardar Asistencia
</button>

</form>