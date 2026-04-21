<?php
require_once "../../middleware/auth.php";
require_once "../../config/conexion.php";

if(isset($_POST["crear_reunion"])){

    $stmt = $pdo->prepare("
        INSERT INTO reuniones (tipo, fecha)
        VALUES (?, ?)
    ");

    $stmt->execute([
        $_POST["tipo"],
        $_POST["fecha"]
    ]);

    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Crear Reunión</title>
<style>
body { font-family: Arial; background:#f4f6f9; margin:20px;}
.card { background:#fff; padding:20px; border-radius:8px;}
input, select { width:100%; padding:8px; margin-bottom:10px;}
button { background:#28a745; color:white; padding:10px; border:none; border-radius:5px;}
</style>
</head>
<body>

<div class="card">
<h2>➕ Crear Reunión</h2>

<form action="../../controllers/asistenciaController.php" method="POST">

<label>Tipo de reunión</label>
<select name="tipo" required>
    <option value="REUNION_JOVENES">Reunión Jóvenes</option>
    <option value="GRUPO_CONEXION">Grupo de Conexión</option>
    <option value="EVENTO_ESPECIAL">Evento Especial</option>
</select>

<label>Fecha</label>
<input type="date" name="fecha" required>

<button type="submit" name="crear_reunion">
Crear Reunión
</button>

</form>

<br>
<a href="index.php">⬅ Volver</a>

</div>

</body>
</html>
