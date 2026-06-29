<?php
require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/permiso.php";
require_once __DIR__ . "/../../services/actividadService.php";
require_once __DIR__ . "/../../config/conexion.php";


actualizarEstadoActividad($pdo);


if (!tienePermiso('gestionar_jovenes')) {

    $_SESSION["error"] = "No tienes permiso";

    header("Location: ../dashboard.php");

    exit;
}


/* =========================
   ID
========================= */

$id = (int)($_GET["id"] ?? 0);


if ($id <= 0) {

    header("Location:index.php");

    exit;
}


/* =========================
   CSS
========================= */


/* =========================
   JOVEN
========================= */

$stmt = $pdo->prepare("
    SELECT *
    FROM jovenes
    WHERE id = ?
");

$stmt->execute([$id]);


$joven = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$joven){

    $_SESSION["error"] = "Joven no encontrado";

    header("Location:index.php");

    exit;

}



/* =========================
   DATOS PREPARADOS
========================= */


$nombre =
htmlspecialchars($joven["nombre_completo"]);



$telefono =
htmlspecialchars($joven["telefono"] ?? "—");



$observaciones =
htmlspecialchars(
    $joven["observaciones"] ?? "Sin observaciones"
);



$genero = match($joven["genero"] ?? ''){

    "M" => "Masculino",

    "F" => "Femenino",

    default => "—"

};



$estadoEspiritual =
ucfirst(
    strtolower(
        $joven["estado_espiritual"] ?? "—"
    )
);



/* =========================
   GENERO PERFIL
========================= */


$claseGenero =
($joven["genero"] ?? '') === "F"

? "perfil-chica"

: "perfil-chico";





/* =========================
   EDAD
========================= */


$edad = "—";

$edadAprox=false;



if(!empty($joven["fecha_nacimiento"])){

    $edad =
    (new DateTime($joven["fecha_nacimiento"]))
    ->diff(new DateTime())
    ->y;


}
elseif(!empty($joven["edad_manual"])){

    $edad =
    (int)$joven["edad_manual"];

    $edadAprox=true;

}



/* =========================
   ASISTENCIA
========================= */


$stmt=$pdo->prepare("
SELECT

SUM(asistio = 1) presentes,

SUM(asistio = 0) ausentes

FROM asistencia

WHERE joven_id=?

");


$stmt->execute([$id]);


$asistencia =
$stmt->fetch(PDO::FETCH_ASSOC);



$presentes =
(int)($asistencia["presentes"] ?? 0);


$ausentes =
(int)($asistencia["ausentes"] ?? 0);



$total =
$presentes+$ausentes;



$porcentaje =
$total > 0

? round(($presentes/$total)*100)

:0;



/* =========================
   CONEXION
========================= */

$con = estadoConexionJoven(
    $pdo,
    $id
);

$estadoConexion =
    $con["estado"];

$claseConexion =
match($con["color"]) {

    "danger" =>
        "conexion-danger",

    "warning" =>
        "conexion-warning",

    default =>
        "conexion-ok"
};



/* =========================
   SEGUIMIENTOS
========================= */


$stmt=$pdo->prepare("
SELECT

s.*,

u.nombre responsable_nombre

FROM seguimientos s

LEFT JOIN usuarios u

ON s.responsable_id=u.id

WHERE s.joven_id=?

ORDER BY s.fecha_contacto DESC

LIMIT 5
");


$stmt->execute([$id]);


$seguimientos =
$stmt->fetchAll(PDO::FETCH_ASSOC);



$totalSeguimientos =
count($seguimientos);



$ultimoSeguimiento =
$seguimientos[0] ?? null;



function claseEstado($estado){

    return strtolower(
        str_replace("_","-",$estado)
    );

}




$datosPerfil=[

"Edad" =>
$edad . ($edadAprox ? " (aprox)" : ""),

"Género" =>
$genero,

"Teléfono" =>
$telefono,

"Estado espiritual" =>
$estadoEspiritual

];




require_once __DIR__ . "/../../includes/header.php";

?>



<div class="ver-wrapper">

<div class="perfil-card <?= $claseGenero ?>">

<header class="perfil-header">

    <div class="perfil-header-info">

        <h2>
            <?= $nombre ?>
        </h2>

        <span class="perfil-conexion <?= $claseConexion ?>">

            <i class="fa-solid fa-circle"></i>

            <?= $estadoConexion ?>

        </span>

    </div>

    <span class="badge <?= ($joven['estado_actividad'] ?? '') === 'ACTIVO'
        ? 'badge-activo'
        : 'badge-inactivo' ?>">

        <?= ucfirst(
            strtolower(
                $joven["estado_actividad"] ?? "Activo"
            )
        ) ?>

    </span>

</header>

    <div class="perfil-grid">

        <?php foreach($datosPerfil as $titulo => $valor): ?>

            <div>

                <strong>
                    <?= $titulo ?>
                </strong>

                <?= $valor ?>

            </div>

        <?php endforeach; ?>

    </div>

    <div class="perfil-stats">

        <div class="stat-card presente">

            <b><?= $presentes ?></b>

            <span>Presentes</span>

        </div>

        <div class="stat-card ausente">

            <b><?= $ausentes ?></b>

            <span>Ausencias</span>

        </div>

        <div class="stat-card porcentaje">

            <b><?= $porcentaje ?>%</b>

            <span>Asistencia</span>

        </div>

        <div class="stat-card seguimiento">

            <b><?= $totalSeguimientos ?></b>

            <span>Seguimientos</span>

        </div>

    </div>

    <div class="perfil-obs">

        <strong>
            Observaciones generales
        </strong>

        <p>
            <?= nl2br($observaciones) ?>
        </p>

    </div>

    <?php if($ultimoSeguimiento): ?>

        <div class="perfil-obs">

            <strong>
                Estado de consolidación
            </strong>

            <span class="estado <?= claseEstado($ultimoSeguimiento["estado_proceso"]) ?>">

                <?= ucfirst(
                    strtolower(
                        str_replace(
                            "_",
                            " ",
                            $ultimoSeguimiento["estado_proceso"]
                        )
                    )
                ) ?>

            </span>

        </div>

    <?php endif; ?>

</div>

<div class="card">

    <div class="section-header">

        <h3>
            Últimos Seguimientos
        </h3>

        <a
            href="<?= BASE_URL ?>/views/seguimientos/index.php?joven_id=<?= $id ?>"
            class="btn-mini"
        >

            <i class="fa-solid fa-list"></i>

            Ver todos

        </a>

    </div>

    <?php if($seguimientos): ?>

        <div class="timeline">

            <?php foreach($seguimientos as $s): ?>

                <div class="timeline-item">

                    <div class="timeline-dot"></div>

                    <div class="timeline-content">

                        <div class="timeline-header">

                            <strong>

                                <?= ucfirst(
                                    strtolower(
                                        $s["modalidad_contacto"]
                                    )
                                ) ?>

                            </strong>

                            <span class="estado <?= claseEstado($s["estado_proceso"]) ?>">

                                <?= ucfirst(
                                    strtolower(
                                        str_replace(
                                            "_",
                                            " ",
                                            $s["estado_proceso"]
                                        )
                                    )
                                ) ?>

                            </span>

                        </div>

                        <div class="timeline-meta">

                            <span>

                                <i class="fa-solid fa-calendar"></i>

                                <?= $s["fecha_contacto"] ?>

                            </span>

                            <span>

                                <i class="fa-solid fa-user"></i>

                                <?= htmlspecialchars(
                                    $s["responsable_nombre"] ?? "—"
                                ) ?>

                            </span>

                        </div>

                        <p>

                            <?= nl2br(
                                htmlspecialchars(
                                    $s["observaciones"] ?? "Sin observaciones"
                                )
                            ) ?>

                        </p>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    <?php else: ?>

        <div class="empty-state">

            <i class="fa-solid fa-clipboard-list"></i>

            <h4>
                No hay seguimientos registrados
            </h4>

            <p>
                Este joven aún no tiene seguimientos asociados.
            </p>

        </div>

    <?php endif; ?>

</div>

<div class="btn-group">

    <a
        href="<?= BASE_URL ?>/views/jovenes/index.php"
        class="btn btn-secondary"
    >

        <i class="fa-solid fa-arrow-left"></i>

        Volver

    </a>

    <a
        href="<?= BASE_URL ?>/views/seguimientos/crear.php?id=<?= $id ?>"
        class="btn btn-primary btn-seguimiento"
    >

        <i class="fa-solid fa-user-plus"></i>

        Registrar seguimiento

    </a>

    <a
        href="<?= BASE_URL ?>/views/jovenes/perfil_pdf.php?id=<?= $id ?>"
        target="_blank"
        class="btn btn-pdf <?= $claseGenero ?>"
    >

        <i class="fa-solid fa-file-pdf"></i>

        PDF

    </a>

</div>
</div>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>