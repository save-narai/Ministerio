<?php
require_once("../../config/conexion.php");
require_once("../../middleware/auth.php");
require_once("../../middleware/permiso.php");

if (!isset($_GET["id"])) {
    die("Reunión no encontrada");
}

$id = (int)$_GET["id"];

$stmt = $pdo->prepare("SELECT * FROM reuniones WHERE id = ?");
$stmt->execute([$id]);
$reunion = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reunion) {
    die("Reunión no encontrada");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Editar Reunión</title>
</head>
<body>

<h2>Editar Reunión</h2>

<form method="POST" action="../../controllers/reunionController.php">

    <input type="hidden" name="id" value="<?= $reunion["id"] ?>">

    <label>Fecha:</label><br>
    <input type="date" name="fecha" 
           value="<?= $reunion["fecha"] ?>" required><br><br>

    <label>Tipo:</label><br>
    <select name="tipo" required>
        <option value="REUNION_JOVENES" <?= $reunion["tipo"] == "REUNION_JOVENES" ? "selected" : "" ?>>
            Reunión Jóvenes
        </option>
        <option value="GRUPO_CONEXION" <?= $reunion["tipo"] == "GRUPO_CONEXION" ? "selected" : "" ?>>
            Grupo de Conexión
        </option>
        <option value="EVENTO_ESPECIAL" <?= $reunion["tipo"] == "EVENTO_ESPECIAL" ? "selected" : "" ?>>
            Evento Especial
        </option>
    </select>

    <br><br>
    <button type="submit" name="actualizar">Actualizar Reunión</button>

</form>

<br>
<a href="index.php">Volver</a>

</body>
</html>