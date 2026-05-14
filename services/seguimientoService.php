<?php

require_once __DIR__ . "/../config/conexion.php";

/* =========================
   RESUMEN GENERAL
========================= */
function obtenerResumenSeguimientosMes() {

    global $pdo;

    $mesNumero = date('m');
    $anio = date('Y');

    $meses = [
        '01'=>'Enero','02'=>'Febrero','03'=>'Marzo','04'=>'Abril',
        '05'=>'Mayo','06'=>'Junio','07'=>'Julio','08'=>'Agosto',
        '09'=>'Septiembre','10'=>'Octubre','11'=>'Noviembre','12'=>'Diciembre'
    ];

    $mesTexto = $meses[$mesNumero] . ' ' . $anio;

    /* =========================
       ACTIVOS
    ========================= */
    $totalActivos = $pdo->query("
        SELECT COUNT(*) 
        FROM jovenes 
        WHERE estado_actividad = 'ACTIVO'
    ")->fetchColumn();

    /* =========================
       SEGUIMIENTOS DEL MES
    ========================= */
    $stmt = $pdo->prepare("
        SELECT 
            j.id AS joven_id,
            j.nombre_completo,
            TIMESTAMPDIFF(YEAR, j.fecha_nacimiento, CURDATE()) AS edad,
            j.telefono,
            j.genero,
            s.modalidad_contacto,
            s.estado_proceso,
            s.observaciones,
            u.nombre AS responsable_nombre,
            s.fecha_contacto
        FROM seguimientos s
        INNER JOIN jovenes j ON s.joven_id = j.id
        LEFT JOIN usuarios u ON s.responsable_id = u.id
        WHERE MONTH(s.fecha_contacto) = MONTH(CURDATE())
        AND YEAR(s.fecha_contacto) = YEAR(CURDATE())
        ORDER BY j.nombre_completo ASC, s.fecha_contacto DESC
    ");

    $stmt->execute();
    $seguimientosMes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    /* =========================
       CÁLCULOS
    ========================= */
    $jovenesConSeguimiento = array_unique(array_column($seguimientosMes, 'joven_id'));

    $totalConSeguimiento = count($jovenesConSeguimiento);
    $totalSinSeguimiento = $totalActivos - $totalConSeguimiento;

    $porcentaje = $totalActivos > 0
        ? round(($totalConSeguimiento / $totalActivos) * 100)
        : 0;

    /* =========================
       SEMÁFORO 🔥
    ========================= */
    $color = "bad";
    if ($porcentaje >= 90) $color = "ok";
    elseif ($porcentaje >= 70) $color = "warn";

    return compact(
        "mesTexto",
        "totalActivos",
        "seguimientosMes",
        "totalConSeguimiento",
        "totalSinSeguimiento",
        "porcentaje",
        "color"
    );
}