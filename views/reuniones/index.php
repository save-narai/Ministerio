<?php
require_once "../../config/conexion.php";
require_once "../../middleware/auth.php";
require_once "../../middleware/permiso.php";

if (!tienePermiso('gestionar_reuniones')) {
    header("Location: ../dashboard.php");
    exit;
}

$reuniones = $pdo->query("
    SELECT r.*,
    (SELECT COUNT(*) FROM asistencia a WHERE a.reunion_id = r.id) as total_registros
    FROM reuniones r
    ORDER BY r.fecha DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Reuniones</title>

<style>
body { 
    font-family: Arial; 
    background:#f4f6f9; 
    margin:20px;
}

table { 
    width:100%; 
    border-collapse:collapse; 
    background:#fff;
}

th, td { 
    padding:10px; 
    border-bottom:1px solid #ddd; 
    text-align:center;
}

th { 
    background:#007bff; 
    color:white;
}

.btn { 
    padding:6px 10px; 
    border-radius:4px; 
    text-decoration:none; 
    color:white; 
    font-size:13px;
    margin:2px;
    display:inline-block;
}

.btn-ver { background:#17a2b8; }
.btn-marcar { background:#28a745; }
.btn-editar { background:#ffc107; color:black; }
.btn-eliminar { background:#dc3545; }
.btn-crear { background:#28a745; padding:8px 12px; }

.btn:hover {
    opacity:0.85;
}
</style>
</head>

<body>

<h2>📅 Historial de Reuniones</h2>

<a href="crear.php" class="btn btn-crear">➕ Crear Reunión</a>

<br><br>

<table>
<tr>
<th>Fecha</th>
<th>Tipo</th>
<th>Total Registros</th>
<th>Acciones</th>
</tr>

<?php foreach($reuniones as $r): ?>
<tr>
<td><?= $r["fecha"] ?></td>
<td><?= $r["tipo"] ?></td>
<td><?= $r["total_registros"] ?></td>

<td>

<a class="btn btn-marcar" 
   href="marcar.php?reunion_id=<?= $r["id"] ?>">
Marcar
</a>

<a class="btn btn-ver" 
   href="ver.php?id=<?= $r["id"] ?>">
Informe
</a>

<a class="btn btn-editar" 
   href="editar.php?id=<?= $r["id"] ?>">
Editar
</a>

<a class="btn btn-eliminar"
   href="../../controllers/reunionController.php?eliminar=<?= $r["id"] ?>"
   onclick="return confirm('¿Eliminar esta reunión?')">
Eliminar
</a>

</td>
</tr>
<?php endforeach; ?>

</table>

<br>
<a href="../dashboard.php">⬅ Volver al Dashboard</a>

</body>
</html>