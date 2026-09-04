<?php

require_once __DIR__ . "/../../../middleware/auth.php";
require_once __DIR__ . "/../../../middleware/permiso.php";
require_once __DIR__ . "/../../../config/conexion.php";
require_once __DIR__ . "/../../../services/discipuladoService.php";

if (!tienePermiso('gestionar_reuniones')) {

    header("Location: ../../dashboard.php");
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

$clases = obtenerClasesDiscipulado($pdo, $cicloId);
$usuarios = obtenerUsuarios($pdo);

require_once __DIR__ . "/../../../includes/header.php";

?>

<div class="discipulado-page">

    <!-- =====================================
         PAGE HEADER
    ===================================== -->

    <div class="page-header">

        <div class="page-header-left">

            <h1 class="page-title">
                Profesores · <?= htmlspecialchars($ciclo['nombre']) ?>
            </h1>

        </div>

        <div class="page-header-right">

            <a href="ver.php?ciclo_id=<?= (int)$cicloId ?>" class="btn btn-back">
                Volver al ciclo
            </a>

        </div>

    </div>

    <!-- =====================================
         LISTA: CLASE → PROFESOR
    ===================================== -->

    <div class="page-section">

        <?php if (empty($clases)): ?>

            <p class="text-center">
                Este ciclo todavía no tiene clases configuradas.
            </p>

        <?php else: ?>

            <div
                class="table-responsive"
                data-discipulado-profesores
                data-ciclo-id="<?= (int)$cicloId ?>"
            >

                <table class="table gx-table">

                    <thead>
                        <tr>
                            <th>Clase</th>
                            <th>Profesor</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($clases as $clase): ?>

                            <tr data-clase-id="<?= (int)$clase['id'] ?>">

                                <td>
                                    Clase <?= (int)$clase['numero_orden'] ?> —
                                    <?= htmlspecialchars($clase['nombre']) ?>
                                </td>

                                <td>

                                    <select
                                        class="form-select"
                                        data-profesor-select
                                    >

                                        <option value="">Sin profesor</option>

                                        <?php foreach ($usuarios as $usuario): ?>

                                            <option
                                                value="<?= (int)$usuario['id'] ?>"
                                                <?= (int)$clase['profesor_id'] === (int)$usuario['id'] ? 'selected' : '' ?>
                                            >
                                                <?= htmlspecialchars($usuario['nombre']) ?>
                                            </option>

                                        <?php endforeach; ?>

                                    </select>

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
    src="<?= BASE_URL ?>/assets/js/modulos/discipulado-profesores.js"
    defer>
</script>

<?php require_once __DIR__ . "/../../../includes/footer.php"; ?>
