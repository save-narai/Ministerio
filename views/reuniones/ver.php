<?php

require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/permiso.php";
require_once __DIR__ . "/../../config/conexion.php";

if (!tienePermiso('gestionar_reuniones')) {

    header("Location: ../dashboard.php");

    exit;
}

if (!isset($_GET["id"])) {

    die("ID inválido");
}

$reunion_id = (int)$_GET["id"];

/* =========================
   REUNIÓN
========================= */

$stmt = $pdo->prepare("
    SELECT *
    FROM reuniones
    WHERE id = ?
");

$stmt->execute([$reunion_id]);

$reunion = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reunion) {

    die("No existe");
}

/* =========================
   DATA
========================= */

$stmt = $pdo->prepare("
SELECT

    j.nombre_completo,

    j.es_servidor,

    a.asistio,

    a.grupo_edad,

    a.participa_discipulado,

    a.primera_vez_discipulado

FROM asistencia a

JOIN jovenes j
    ON j.id = a.joven_id

WHERE a.reunion_id = ?
");

$stmt->execute([$reunion_id]);

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   STATS
========================= */

$total = count($data);

$asistieron = 0;

$servidores = 0;

$servidoresAsist = 0;

$conexion = 0;

$discipulado = 0;

foreach ($data as $d) {

    if ($d["asistio"]) {

        $asistieron++;
    }

    if ($d["es_servidor"]) {

        $servidores++;

        if ($d["asistio"]) {

            $servidoresAsist++;
        }
    }

    if ($d["participa_discipulado"]) {

        $discipulado++;
    }

    if ($d["primera_vez_discipulado"]) {

        $conexion++;
    }
}

$porcentaje = $total > 0
    ? round(($asistieron / $total) * 100)
    : 0;

/* =========================
   TIPO BONITO
========================= */

$tipoBonito = match($reunion["tipo"]) {

    "REUNION_JOVENES" => "Reunión Jóvenes",

    "GRUPO_CONEXION" => "Grupo Conexión",

    "DISCIPULADO" => "Discipulado",

    "EVENTO_ESPECIAL" => "Evento Especial",

    default => $reunion["tipo"]
};

/* =========================
   CSS
========================= */

$extraCSS = '
<link rel="stylesheet" href="' . BASE_URL . '/assets/css/modules/reuniones/ver.css">
';

require_once __DIR__ . "/../../includes/header.php";

?>

<div class="ver">

    <!-- HEADER -->

    <div class="ver__header">

        <div>

            <h1>
                Informe reunión
            </h1>

            <span>
                <?= $tipoBonito ?>
                •
                <?= date("d/m/Y", strtotime($reunion["fecha"])) ?>
            </span>

        </div>

        <div class="ver__actions">

            <a
            href="reporte_reunion_pdf.php?id=<?= $reunion_id ?>"
            class="btn-pdf"
            target="_blank">

                 Descargar PDF

            </a>

        </div>

    </div>

    <!-- CARDS -->

    <div class="cards">

        <div class="card">
            <h3>Total</h3>
            <p><?= $total ?></p>
        </div>

        <div class="card">
            <h3>Asistencia</h3>
            <p><?= $asistieron ?></p>
        </div>

        <div class="card">
            <h3>Porcentaje</h3>
            <p><?= $porcentaje ?>%</p>
        </div>

        <div class="card">
            <h3>Servidores</h3>
            <p><?= $servidoresAsist ?>/<?= $servidores ?></p>
        </div>

        <div class="card">
            <h3>Discipulado</h3>
            <p><?= $discipulado ?></p>
        </div>

        <div class="card">
            <h3>Primera vez</h3>
            <p><?= $conexion ?></p>
        </div>

    </div>

    <!-- PROGRESS -->

    <?php if($porcentaje > 0): ?>

    <div class="progress">

        <div
        class="progress-bar"
        style="width: <?= $porcentaje ?>%">

        </div>

    </div>

    <?php endif; ?>

    <!-- TABLA -->

    <div class="tabla">

        <table>

            <thead>

                <tr>

                    <th>Nombre</th>

                    <th>Servidor</th>

                    <th>Grupo</th>

                    <th>Discipulado</th>

                    <th>Primera vez</th>

                    <th>Asistencia</th>

                </tr>

            </thead>

            <tbody>

                <?php foreach($data as $d): ?>

                <tr>

                    <td>
                        <?= htmlspecialchars($d["nombre_completo"]) ?>
                    </td>

                    <td>
                        <?= $d["es_servidor"] ? "Sí" : "No" ?>
                    </td>

                    <td>
                        <?= $d["grupo_edad"] ?? "-" ?>
                    </td>

                    <td>
                        <?= $d["participa_discipulado"] ? "✔" : "-" ?>
                    </td>

                    <td>
                        <?= $d["primera_vez_discipulado"] ? "✔" : "-" ?>
                    </td>

                    <td>

                        <span class="<?= $d["asistio"] ? 'ok' : 'no' ?>">

                            <?= $d["asistio"]
                                ? "Asistió"
                                : "Faltó" ?>

                        </span>

                    </td>

                </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    </div>

    <br>

    <a
    href="index.php"
    class="btn-volver">

         Volver

    </a>

</div>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>