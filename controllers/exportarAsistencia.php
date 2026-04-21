<?php
require_once "../config/conexion.php";

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=reporte_asistencia.xls");

$stmt = $pdo->query("
    SELECT r.tipo,
           r.fecha,
           j.nombre_completo,
           a.asistio
    FROM asistencia a
    JOIN reuniones r ON a.reunion_id = r.id
    JOIN jovenes j ON a.joven_id = j.id
    ORDER BY r.fecha DESC
");

echo "Tipo\tFecha\tNombre\tAsistió\n";

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row["tipo"] . "\t";
    echo $row["fecha"] . "\t";
    echo $row["nombre_completo"] . "\t";
    echo ($row["asistio"] ? "SI" : "NO") . "\n";
}


