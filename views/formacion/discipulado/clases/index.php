<?php

require_once __DIR__ . "/../../../../middleware/auth.php";
require_once __DIR__ . "/../../../../middleware/permiso.php";
require_once __DIR__ . "/../../../../config/conexion.php";
require_once __DIR__ . "/../../../../services/discipuladoService.php";

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

$clases = obtenerClasesDiscipulado($pdo, $cicloId);
$usuarios = obtenerUsuarios($pdo);

require_once __DIR__ . "/../../../../includes/header.php";

?>

<div class="clases-discipulado-page">

    <!-- =====================================
         PAGE HEADER
    ===================================== -->

    <div class="page-header">

        <div class="page-header-left">

            <h1 class="page-title">
                Clases · <?= htmlspecialchars($ciclo['nombre']) ?>
            </h1>

            <p class="page-subtitle">
                Clases configurables de este ciclo de discipulado.
            </p>

        </div>

        <div class="page-header-right">

            <a
                href="../ver.php?ciclo_id=<?= (int)$cicloId ?>"
                class="btn btn-back"
            >
                Volver al ciclo
            </a>

            <a
                href="crear.php?ciclo_id=<?= (int)$cicloId ?>"
                class="btn btn-primary"
            >
                Nueva clase
            </a>

        </div>

    </div>

    <br>

    <!-- =====================================
         TABLA
    ===================================== -->

    <div class="page-section">

        <div class="section-header">

            <div>

                <h2 class="section-title">
                    Clases del ciclo
                </h2>

                <p class="section-subtitle">
                    <?= (int)count($clases) ?> clase(s) asignada(s) desde el catálogo base.
                </p>

            </div>

        </div>

        <div class="table-responsive">

            <table class="table gx-table">

                <thead>

                    <tr>

                        <th>#</th>
                        <th>Nombre</th>
                        <th>Fecha programada</th>
                        <th>Modalidad</th>
                        <th>Profesor</th>
                        <th>Material</th>
                        <th>Estado</th>
                        <th>Acciones</th>

                    </tr>

                </thead>

                <tbody>

                    <?php if (empty($clases)): ?>

                        <tr>

                            <td colspan="8" class="text-center">
                                Este ciclo todavía no tiene clases registradas.
                            </td>

                        </tr>

                    <?php else: ?>

                        <?php foreach ($clases as $clase): ?>

                            <tr>

                                <td>
                                    <?= (int)$clase['numero_orden'] ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($clase['nombre']) ?>
                                </td>

                                <td>
                                    <?= $clase['fecha_programada'] ? htmlspecialchars($clase['fecha_programada']) : '—' ?>
                                </td>

                                <td>
                                    <?= $clase['modalidad_programada'] ? htmlspecialchars($clase['modalidad_programada']) : '—' ?>
                                </td>

                                <td>
                                    <form action="<?= BASE_URL ?>/controllers/discipuladoClaseController.php" method="POST">
                                        <?= csrfField() ?><input type="hidden" name="action" value="asignar_profesor_clase_discipulado"><input type="hidden" name="ciclo_id" value="<?= (int)$cicloId ?>"><input type="hidden" name="id" value="<?= (int)$clase['id'] ?>">
                                        <select class="form-select" name="profesor_id" onchange="this.form.submit()"><option value="">Sin profesor</option><?php foreach ($usuarios as $usuario): ?><option value="<?= (int)$usuario['id'] ?>" <?= (int)$clase['profesor_id'] === (int)$usuario['id'] ? 'selected' : '' ?>><?= htmlspecialchars($usuario['nombre']) ?></option><?php endforeach; ?></select>
                                    </form>
                                </td>

                                <td>

                                    <?php if ($clase['material_id']): ?>

                                        <div class="discipulado-dropdown" data-dropdown>

                                            <button
                                                type="button"
                                                class="btn-icon btn-view"
                                                data-tooltip="Material de la clase"
                                                data-dropdown-toggle
                                            >
                                                <i class="fa-solid fa-file-pdf"></i>
                                            </button>

                                            <div class="discipulado-dropdown-menu">

                                                <a
                                                    target="_blank"
                                                    href="<?= BASE_URL ?>/controllers/discipuladoMaterialController.php?id=<?= (int)$clase['material_id'] ?>"
                                                >
                                                    <i class="fa-solid fa-eye"></i>
                                                    Ver PDF
                                                </a>

                                                <a
                                                    href="<?= BASE_URL ?>/controllers/discipuladoMaterialController.php?id=<?= (int)$clase['material_id'] ?>&modo=descargar"
                                                >
                                                    <i class="fa-solid fa-download"></i>
                                                    Descargar
                                                </a>

                                            </div>

                                        </div>

                                    <?php else: ?>

                                        —

                                    <?php endif; ?>

                                </td>

                                <td>

                                    <?php
                                        $badgeClase = match ($clase['estado']) {
                                            'PROGRAMADA' => 'badge-info',
                                            'REALIZADA' => 'badge-success',
                                            'CANCELADA' => 'badge-danger',
                                            default => 'badge-info'
                                        };
                                    ?>

                                    <span class="badge <?= $badgeClase ?>">
                                        <?= htmlspecialchars($clase['estado']) ?>
                                    </span>

                                </td>

                                <td>

                                    <div class="table-actions">

                                        <a
                                            href="editar.php?ciclo_id=<?= (int)$cicloId ?>&id=<?= (int)$clase['id'] ?>"
                                            class="btn-icon btn-edit"
                                            data-tooltip="Editar clase"
                                        >
                                            <i class="fa-solid fa-pen"></i>
                                        </a>

                                        <?php if ($clase['estado'] !== 'REALIZADA'): ?>

                                            <form
                                                action="<?= BASE_URL ?>/controllers/discipuladoClaseController.php"
                                                method="POST"
                                                onsubmit="return confirm('¿Marcar esta clase como realizada?');"
                                            >

                                                <?= csrfField(); ?>

                                                <input type="hidden" name="action" value="cambiar_estado_clase_discipulado">
                                                <input type="hidden" name="ciclo_id" value="<?= (int)$cicloId ?>">
                                                <input type="hidden" name="id" value="<?= (int)$clase['id'] ?>">
                                                <input type="hidden" name="estado" value="REALIZADA">

                                                <button
                                                    type="submit"
                                                    class="btn-icon btn-complete"
                                                    data-tooltip="Marcar realizada"
                                                >
                                                    <i class="fa-solid fa-check"></i>
                                                </button>

                                            </form>

                                        <?php endif; ?>

                                        <!-- Las clases de discipulado no se cancelan desde la
                                             interfaz (sección 23): el estado CANCELADA se
                                             conserva únicamente por compatibilidad histórica. -->

                                        <form
                                            action="<?= BASE_URL ?>/controllers/discipuladoClaseController.php"
                                            method="POST"
                                            onsubmit="return confirm('¿Eliminar esta clase? Esta acción no se puede deshacer.');"
                                        >

                                            <?= csrfField(); ?>

                                            <input type="hidden" name="action" value="eliminar_clase_discipulado">
                                            <input type="hidden" name="ciclo_id" value="<?= (int)$cicloId ?>">
                                            <input type="hidden" name="id" value="<?= (int)$clase['id'] ?>">

                                            <button
                                                type="submit"
                                                class="btn-icon btn-delete"
                                                data-tooltip="Eliminar clase"
                                            >
                                                <i class="fa-solid fa-trash"></i>
                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<script
    src="<?= BASE_URL ?>/assets/js/modulos/discipulado-clases.js"
    defer>
</script>

<?php require_once __DIR__ . "/../../../../includes/footer.php"; ?>
