<?php

require_once __DIR__ . "/../../../middleware/auth.php";
require_once __DIR__ . "/../../../middleware/permiso.php";
require_once __DIR__ . "/../../../config/conexion.php";
require_once __DIR__ . "/../../../services/discipuladoService.php";

if (!tienePermiso('gestionar_reuniones')) {

    header("Location: ../../../dashboard.php");
    exit;

}

if (!isset($_GET["ciclo_id"])) {

    die("Ciclo no encontrado");

}

$cicloId = (int)$_GET["ciclo_id"];

$ciclo = obtenerCicloDiscipuladoPorId($pdo, $cicloId);

if (!$ciclo) {

    die("Ciclo no encontrado");

}

/* =====================================
   DATOS DE LA TABLA (clases × jóvenes)

   obtenerChecklistDiscipulado() ya arma
   la estructura ['clases' => ..., 'inscripciones' => [...con 'celdas']],
   e 'inscripciones' ya trae 'clases_pendientes', 'repaso_1'
   y 'repaso_2' (ver obtenerInscripcionesDiscipulado()).
===================================== */

$checklistCiclo = obtenerChecklistDiscipulado($pdo, $cicloId);
$clasesCiclo = $checklistCiclo['clases'];
$filasMatriz = $checklistCiclo['inscripciones'];

/* Posición donde va la primera columna "Repaso": justo
   después de la 4ª clase (o después de la última clase si
   el ciclo tiene 4 o menos). La segunda va siempre al final,
   después de la última clase. Reproduce el mismo layout de
   la hoja de cálculo que se usaba a mano. */

$totalClasesCiclo = count($clasesCiclo);
$posicionRepaso1 = min(4, $totalClasesCiclo);

/* Última observación de cada inscripción, para precargar el
   campo de "Observación" (un solo campo de texto libre: se
   muestra únicamente el valor más reciente, no un historial). */

$ultimaObservacionPorInscripcion = [];

foreach ($filasMatriz as $fila) {

    $observacionesFila = obtenerObservacionesInscripcionDiscipulado(
        $pdo,
        (int)$fila['id']
    );

    $ultimaObservacionPorInscripcion[(int)$fila['id']] =
        $observacionesFila[0]['observacion'] ?? '';

}

require_once __DIR__ . "/../../../includes/header.php";

?>

<div class="participantes-discipulado-page">

    <!-- =====================================
         PAGE HEADER
    ===================================== -->

    <div class="page-header">

        <div class="page-header-left">

            <h1 class="page-title">
                Asistencia · <?= htmlspecialchars($ciclo['nombre']) ?>
            </h1>

            <p class="page-subtitle">
                <?= (int)count($filasMatriz) ?> inscritos
            </p>

        </div>

        <div class="page-header-right">

            <a href="ver.php?ciclo_id=<?= (int)$cicloId ?>" class="btn btn-back">
                Volver al ciclo
            </a>

            <a href="participantes/inscribir.php?ciclo_id=<?= (int)$cicloId ?>" class="btn btn-primary">
                Inscribir joven
            </a>

        </div>

    </div>

    <!-- =====================================
         TABLA DE ASISTENCIA
         (checkboxes con autoguardado — sin recargar la página)
    ===================================== -->

    <div class="page-section">

        <?php if (empty($filasMatriz)): ?>

            <p class="text-center">
                Todavía no hay jóvenes inscritos en este ciclo.
            </p>

        <?php else: ?>

            <div
                class="discipulado-matriz-wrap"
                data-discipulado-matriz
                data-ciclo-id="<?= (int)$cicloId ?>"
            >

                <table class="discipulado-matriz">

                    <thead>

                        <tr>

                            <th class="col-numero">#</th>
                            <th class="col-nombre">Nombre y Apellido</th>
                            <th class="col-modalidad">Modalidad</th>

                            <?php foreach ($clasesCiclo as $indice => $clase): ?>

                                <th
                                    class="col-clase"
                                    title="<?= htmlspecialchars($clase['nombre']) ?>"
                                >
                                    Clase <?= (int)$clase['numero_orden'] ?>
                                </th>

                                <?php if ($indice + 1 === $posicionRepaso1): ?>
                                    <th class="col-repaso">Repaso</th>
                                <?php endif; ?>

                            <?php endforeach; ?>

                            <?php if ($totalClasesCiclo > 0): ?>
                                <th class="col-repaso">Repaso</th>
                            <?php endif; ?>

                            <th class="col-pendientes">Pendientes</th>
                            <th class="col-observaciones">Observación</th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php foreach ($filasMatriz as $numeroFila => $fila): ?>

                            <?php
                                $inscripcionId = (int)$fila['id'];
                                $estaActivo = $fila['estado'] === 'ACTIVO';
                                $estaRetirado = $fila['estado'] === 'CANCELADO';
                            ?>

                            <tr
                                data-inscripcion-id="<?= $inscripcionId ?>"
                                data-estado="<?= htmlspecialchars($fila['estado']) ?>"
                                class="<?= $estaRetirado ? 'discipulado-fila-retirada' : '' ?>"
                            >

                                <td class="col-numero"><?= $numeroFila + 1 ?></td>

                                <td class="col-nombre">
                                    <a href="participantes/progreso.php?ciclo_id=<?= (int)$cicloId ?>&id=<?= $inscripcionId ?>">
                                        <?= htmlspecialchars($fila['nombre_completo']) ?>
                                    </a>
                                </td>

                                <td class="col-modalidad">

                                    <select
                                        class="discipulado-matriz-select"
                                        data-modalidad-select
                                        <?= $estaActivo ? '' : 'disabled' ?>
                                    >

                                        <option value="PRESENCIAL" <?= $fila['modalidad_principal'] === 'PRESENCIAL' ? 'selected' : '' ?>>
                                            Presencial
                                        </option>

                                        <option value="VIRTUAL" <?= $fila['modalidad_principal'] === 'VIRTUAL' ? 'selected' : '' ?>>
                                            Virtual
                                        </option>

                                    </select>

                                </td>

                                <?php foreach ($clasesCiclo as $indice => $clase): ?>

                                    <?php
                                        $claseId = (int)$clase['id'];
                                        $celda = $fila['celdas'][$claseId] ?? null;
                                        $completada = $celda !== null;
                                    ?>

                                    <td
                                        class="col-clase discipulado-matriz-celda <?= $completada ? 'discipulado-cell-completa' : '' ?>"
                                        data-matriz-celda
                                    >

                                        <input
                                            type="checkbox"
                                            data-clase-checkbox
                                            data-clase-id="<?= $claseId ?>"
                                            <?= $completada ? 'checked' : '' ?>
                                            <?= $estaActivo ? '' : 'disabled' ?>
                                            aria-label="<?= htmlspecialchars($clase['nombre']) ?> — <?= htmlspecialchars($fila['nombre_completo']) ?>"
                                        >

                                    </td>

                                    <?php if ($indice + 1 === $posicionRepaso1): ?>

                                        <td class="col-repaso">
                                            <input
                                                type="checkbox"
                                                data-repaso-checkbox
                                                data-numero-repaso="1"
                                                <?= !empty($fila['repaso_1']) ? 'checked' : '' ?>
                                                <?= $estaActivo ? '' : 'disabled' ?>
                                                aria-label="Repaso 1 — <?= htmlspecialchars($fila['nombre_completo']) ?>"
                                            >
                                        </td>

                                    <?php endif; ?>

                                <?php endforeach; ?>

                                <?php if ($totalClasesCiclo > 0): ?>

                                    <td class="col-repaso">
                                        <input
                                            type="checkbox"
                                            data-repaso-checkbox
                                            data-numero-repaso="2"
                                            <?= !empty($fila['repaso_2']) ? 'checked' : '' ?>
                                            <?= $estaActivo ? '' : 'disabled' ?>
                                            aria-label="Repaso 2 — <?= htmlspecialchars($fila['nombre_completo']) ?>"
                                        >
                                    </td>

                                <?php endif; ?>

                                <td class="col-pendientes" data-pendientes-valor>
                                    <?= (int)$fila['clases_pendientes'] ?>
                                </td>

                                <td class="col-observaciones">

                                    <input
                                        type="text"
                                        class="discipulado-matriz-input"
                                        data-observacion-input
                                        placeholder="Sin observación"
                                        value="<?= htmlspecialchars($ultimaObservacionPorInscripcion[$inscripcionId] ?? '') ?>"
                                        data-ultimo-valor="<?= htmlspecialchars($ultimaObservacionPorInscripcion[$inscripcionId] ?? '') ?>"
                                        <?= $estaActivo ? '' : 'disabled' ?>
                                    >

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        <?php endif; ?>

    </div>

</div>

<script
    src="<?= BASE_URL ?>/assets/js/modulos/discipulado-matriz.js"
    defer>
</script>

<?php require_once __DIR__ . "/../../../includes/footer.php"; ?>
