<?php
require_once "../../middleware/auth.php";
require_once "../../config/conexion.php";

if(!isset($_GET["id"])) {
    die("ID no especificado.");
}

$reunion_id = (int)$_GET["id"];

/* ===============================
   INFORMACIÓN REUNIÓN
================================= */
$stmt = $pdo->prepare("SELECT * FROM reuniones WHERE id = ?");
$stmt->execute([$reunion_id]);
$reunion = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$reunion){
    die("Reunión no encontrada.");
}

/* ===============================
   LISTADO ASISTENCIA
================================= */
$stmt = $pdo->prepare("
   SELECT
    j.nombre_completo,
    j.es_servidor,
    a.asistio,
    a.grupo_edad,
    a.participa_discipulado,
    a.primera_vez_discipulado
FROM asistencia a
JOIN jovenes j ON a.joven_id = j.id
WHERE a.reunion_id = ?
");
$stmt->execute([$reunion_id]);
$lista = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ===============================
   ESTADÍSTICAS GENERALES
================================= */
$total = count($lista);
$asistieron = 0;

$servidoresTotal = 0;
$servidoresPresentes = 0;

$noServidoresTotal = 0;
$noServidoresPresentes = 0;

/* ===== NUEVAS VARIABLES ===== */
$teenTotal = 0;
$teenPresentes = 0;

$remanenteTotal = 0;
$remanentePresentes = 0;

$discipuladoTotal = 0;
$discipuladoPresentes = 0;

foreach($lista as $l){

    if($l["asistio"]){
        $asistieron++;
    }

/* ===============================
   PRIMERA VEZ DISCIPULADO
================================= */

$primeraVezTotal = 0;
$primeraVezPresentes = 0;

foreach($lista as $l){

    if(!empty($l["primera_vez_discipulado"])){

        $primeraVezTotal++;

        if(!empty($l["asistio"])){
            $primeraVezPresentes++;
        }
    }
}

    // Servidores
    if($l["es_servidor"]){
        $servidoresTotal++;
        if($l["asistio"]){
            $servidoresPresentes++;
        }
    } else {
        $noServidoresTotal++;
        if($l["asistio"]){
            $noServidoresPresentes++;
        }
    }

    // Grupo edad
    if($l["grupo_edad"] == "TEENAGERS"){
        $teenTotal++;
        if($l["asistio"]) $teenPresentes++;
    }

    if($l["grupo_edad"] == "REMANENTE"){
        $remanenteTotal++;
        if($l["asistio"]) $remanentePresentes++;
    }

    // Discipulado
    if($l["participa_discipulado"]){
        $discipuladoTotal++;
        if($l["asistio"]) $discipuladoPresentes++;
    }
}

/* ===============================
   CONCLUSIÓN AUTOMÁTICA
================================= */
$conclusion = "";

$porcentajeServidores = $servidoresTotal > 0
    ? round(($servidoresPresentes / $servidoresTotal) * 100, 1)
    : 0;

$porcentajeNoServidores = $noServidoresTotal > 0
    ? round(($noServidoresPresentes / $noServidoresTotal) * 100, 1)
    : 0;

if($porcentajeNoServidores > $porcentajeServidores){
    $conclusion = "La asistencia de los jóvenes no servidores fue superior a la de los servidores en esta reunión.";
}
elseif($porcentajeServidores > $porcentajeNoServidores){
    $conclusion = "La asistencia de los servidores fue superior al promedio general en esta reunión.";
}
else{
    $conclusion = "La asistencia estuvo equilibrada entre servidores y no servidores.";
}

$porcentaje = $total > 0 ? round(($asistieron / $total) * 100, 1) : 0;
?>

<?php
$tipoBonito = match($reunion["tipo"]) {
    "REUNION_JOVENES" => "Reunión Jóvenes",
    "GRUPO_CONEXION" => "Grupo de Conexión",
    "EVENTO_ESPECIAL" => "Evento Especial",
    default => $reunion["tipo"]
};
?>

<p><strong>Tipo:</strong> <?= $tipoBonito ?></p>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Informe Reunión</title>
<style>
body { font-family: Arial; background:#f4f6f9; margin:20px;}
.card { background:#fff; padding:20px; margin-bottom:20px; border-radius:8px;}
</style>
</head>
<body>

<h2>📊 Informe de Reunión</h2>

<div class="card">
<p><strong>Fecha:</strong> <?= $reunion["fecha"] ?></p>
<p><strong>Tipo:</strong> <?= $reunion["tipo"] ?></p>
<p><strong>Total Registros:</strong> <?= $total ?></p>
<p><strong>Asistieron:</strong> <?= $asistieron ?></p>
<p><strong>% Asistencia:</strong> <?= $porcentaje ?>%</p>
</div>

<div class="card">
<h3>🌱 Primera Vez Discipulado</h3>
<p>Total: <?= $primeraVezTotal ?></p>
<p>Asistieron: <?= $primeraVezPresentes ?></p>
<p>%:
<?= ($primeraVezTotal > 0) ? round(($primeraVezPresentes / $primeraVezTotal) * 100, 1) : 0 ?>%
</p>
</div>

<div class="card">
<h3>📋 Lista Detallada</h3>

<table width="100%" border="1" cellpadding="5">
<tr>
<th>Nombre</th>
<th>Servidor</th>
<th>Grupo</th>
<th>Discipulado</th>
<th>Primera Vez</th>
<th>Asistencia</th>
</tr>

<?php foreach($lista as $l): ?>
<tr>
<td><?= $l["nombre_completo"] ?></td>
<td><?= $l["es_servidor"] ? "Sí" : "No" ?></td>
<td><?= $l["grupo_edad"] ?? "-" ?></td>
<td><?= $l["participa_discipulado"] ? "Sí" : "No" ?></td>
<td><?= $l["primera_vez_discipulado"] ? "🌱 Sí" : "-" ?></td>
<td><?= $l["asistio"] ? "✅" : "❌" ?></td>
</tr>
<?php endforeach; ?>

</table>
</div>

<div class="card">
<h3>🧠 Conclusión Automática</h3>
<p><?= $conclusion ?></p>
</div>

<a href="index.php">⬅ Volver</a>

</body>
</html>