<?php
require_once "../../middleware/auth.php";
require_once "../../config/conexion.php";

$jovenes = $pdo->query("SELECT id, nombre_completo FROM jovenes ORDER BY nombre_completo")
    ->fetchAll(PDO::FETCH_ASSOC);

$responsables = $pdo->query("SELECT id, nombre FROM usuarios")
    ->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include("../../includes/header.php"); ?>


<h2>➕ Crear Seguimiento</h2>

<form action="../../controllers/seguimientoController.php" method="POST">

<label>Joven:</label><br>
<select name="joven_id" required>
<?php foreach($jovenes as $j): ?>
<option value="<?= $j["id"] ?>">
<?= htmlspecialchars($j["nombre_completo"]) ?>
</option>
<?php endforeach; ?>
</select><br><br>

<label>Fecha contacto:</label><br>
<input type="date" name="fecha_contacto" required><br><br>

<label>Modalidad:</label><br>
<select name="modalidad_contacto">
<option value="WHATSAPP">WhatsApp</option>
<option value="LLAMADA">Llamada</option>
<option value="VISITA">Visita</option>
</select><br><br>

<label>Estado:</label><br>
<select name="estado_proceso">
<option value="PENDIENTE">Pendiente</option>
<option value="EN_PROCESO">En Proceso</option>
<option value="FINALIZADO">Finalizado</option>
</select><br><br>

<label>Responsable:</label><br>
<select name="responsable_id">
<?php foreach($responsables as $r): ?>
<option value="<?= $r["id"] ?>">
<?= htmlspecialchars($r["nombre"]) ?>
</option>
<?php endforeach; ?>
</select><br><br>

<label>Observaciones:</label><br>
<textarea name="observaciones"></textarea><br><br>

<button type="submit" name="crear_seguimiento">
Guardar
</button>

</form>

<br>
<a href="index.php">⬅ Volver</a>

<?php include("../../includes/footer.php"); ?>