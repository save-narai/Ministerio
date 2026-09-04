<?php

require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/permiso.php";
require_once __DIR__ . "/../../config/conexion.php";
require_once __DIR__ . "/../../services/reunionService.php";
require_once __DIR__ . "/../../services/discipuladoService.php";

if (!tienePermiso('gestionar_reuniones')) {

    header("Location: ../dashboard.php");
    exit;

}

if (!isset($_GET["id"])) {

    die("Reunión no encontrada");

}

$id = (int) $_GET["id"];

$stmt = $pdo->prepare("

    SELECT *

    FROM reuniones

    WHERE id = ?

");

$stmt->execute([$id]);

$reunion = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reunion) {

    die("Reunión no encontrada");

}

$esPersonalizado = !in_array(
    $reunion["tipo"],
    [
        "Reunión Jóvenes",
        "Grupo Conexión",
        "Discipulado",
        "Evento Especial"
    ],
    true
);

/* =====================================
   VÍNCULO DE DISCIPULADO ACTUAL (FASE 7)
===================================== */

$vinculoActual = obtenerVinculoReunionDiscipulado($pdo, $id);

$ciclosActivosDiscipulado = obtenerCiclosDiscipulado($pdo, ['estado' => 'ACTIVO']);

/* Si el vínculo actual apunta a un ciclo que ya no está
   ACTIVO (por ejemplo, se finalizó después de crear la
   reunión), se agrega igual a la lista para no perder la
   selección existente al editar. */

if (
    $vinculoActual
    &&
    !in_array((int)$vinculoActual['ciclo_id'], array_column($ciclosActivosDiscipulado, 'id'), true)
) {

    $cicloDelVinculo = obtenerCicloDiscipuladoPorId($pdo, (int)$vinculoActual['ciclo_id']);

    if ($cicloDelVinculo) {
        $ciclosActivosDiscipulado[] = $cicloDelVinculo;
    }

}

$clasesPorCicloDiscipulado = [];

foreach ($ciclosActivosDiscipulado as $cicloActivo) {

    $clasesPorCicloDiscipulado[(int)$cicloActivo['id']] =
        array_map(
            fn (array $c) => [
                'id' => (int)$c['id'],
                'nombre' => 'Clase ' . $c['numero_orden'] . ' — ' . $c['nombre']
            ],
            obtenerClasesDiscipulado($pdo, (int)$cicloActivo['id'])
        );

}

require_once __DIR__ . "/../../includes/header.php";

?>

<div class="form-card">

    <div class="form-header">

        <div class="form-header-icon">

            <i class="fa-solid fa-calendar-days"></i>

        </div>

        <div class="form-header-content">

            <h1 class="form-title">
                Editar reunión
            </h1>

            <p class="form-subtitle">
                Actualiza la información de esta reunión.
            </p>

        </div>

    </div>

    <form
        class="form"
        method="POST"
        action="<?= BASE_URL ?>/controllers/reunionController.php"
    >

        <input
            type="hidden"
            name="action"
            value="actualizar_reunion"
        >

        <input
            type="hidden"
            name="id"
            value="<?= $reunion["id"] ?>"
        >

        <input
            type="hidden"
            name="csrf_token"
            value="<?= $_SESSION['csrf_token'] ?>"
        >

        <div class="form-grid">

                    <!-- FECHA -->

            <div class="form-group">

                <label class="form-label">
                    Fecha
                </label>

                <input
                    class="form-input"
                    type="date"
                    name="fecha"
                    value="<?= htmlspecialchars($reunion["fecha"]) ?>"
                    required
                >

            </div>

            <!-- TIPO -->

            <div class="form-group">

                <label class="form-label">
                    Tipo de reunión
                </label>

                <select
                    class="form-select"
                    name="tipo"
                    id="tipo"
                    required
                >

                    <option
                        value="REUNION_JOVENES"
                        <?= $reunion["tipo"] === "Reunión Jóvenes" ? "selected" : "" ?>
                    >
                        Reunión Jóvenes
                    </option>

                    <option
                        value="GRUPO_CONEXION"
                        <?= $reunion["tipo"] === "Grupo Conexión" ? "selected" : "" ?>
                    >
                        Grupo Conexión
                    </option>

                    <option
                        value="DISCIPULADO"
                        <?= $reunion["tipo"] === "Discipulado" ? "selected" : "" ?>
                    >
                        Discipulado
                    </option>

                    <option
                        value="EVENTO_ESPECIAL"
                        <?= $reunion["tipo"] === "Evento Especial" ? "selected" : "" ?>
                    >
                        Evento Especial
                    </option>

                    <option
                        value="OTRO"
                        <?= $esPersonalizado ? "selected" : "" ?>
                    >
                        Otro...
                    </option>

                </select>

            </div>

            <!-- NOMBRE DEL EVENTO -->

            <div
                class="form-group"
                id="grupoTipoPersonalizado"
                style="<?= $esPersonalizado ? '' : 'display:none;' ?>"
            >

                <label class="form-label">
                    Nombre del evento
                </label>

                <input
                    class="form-input"
                    type="text"
                    name="tipo_personalizado"
                    id="tipoPersonalizado"
                    value="<?= $esPersonalizado ? htmlspecialchars($reunion["tipo"]) : "" ?>"
                    placeholder="Ej: Campamento Juvenil"
                >

            </div>

            <!-- =====================================
                 DISCIPULADO: CICLO + CLASE + MODALIDAD
                 (FASE 7 — solo visible si tipo = DISCIPULADO)
            ===================================== -->

            <div
                class="form-group"
                id="grupoCicloDiscipulado"
                style="<?= $esPersonalizado === false && $reunion['tipo'] === 'Discipulado' ? '' : 'display:none;' ?>"
            >

                <label class="form-label">
                    Ciclo de discipulado (opcional)
                </label>

                <select
                    class="form-select"
                    name="ciclo_id"
                    id="cicloDiscipulado"
                >

                    <option value="">Sin asociar a un ciclo</option>

                    <?php foreach ($ciclosActivosDiscipulado as $cicloActivo): ?>

                        <option
                            value="<?= (int)$cicloActivo['id'] ?>"
                            <?= $vinculoActual && (int)$vinculoActual['ciclo_id'] === (int)$cicloActivo['id'] ? 'selected' : '' ?>
                        >
                            <?= htmlspecialchars($cicloActivo['nombre']) ?>
                        </option>

                    <?php endforeach; ?>

                </select>

                <small>
                    Si no seleccionas un ciclo, esta reunión no afectará el progreso de discipulado de nadie.
                </small>

            </div>

            <div
                class="form-group"
                id="grupoClaseDiscipulado"
                style="<?= $esPersonalizado === false && $reunion['tipo'] === 'Discipulado' ? '' : 'display:none;' ?>"
            >

                <label class="form-label">
                    Clase de ese ciclo
                </label>

                <select
                    class="form-select"
                    name="clase_id"
                    id="claseDiscipulado"
                >

                    <?php if ($vinculoActual): ?>

                        <option value="">Sin clase asociada</option>

                        <?php foreach (($clasesPorCicloDiscipulado[(int)$vinculoActual['ciclo_id']] ?? []) as $clase): ?>

                            <option
                                value="<?= (int)$clase['id'] ?>"
                                <?= (int)$clase['id'] === (int)$vinculoActual['clase_id'] ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($clase['nombre']) ?>
                            </option>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <option value="">Selecciona primero un ciclo</option>

                    <?php endif; ?>

                </select>

            </div>

            <div
                class="form-group"
                id="grupoModalidadDiscipulado"
                style="<?= $esPersonalizado === false && $reunion['tipo'] === 'Discipulado' ? '' : 'display:none;' ?>"
            >

                <label class="form-label">
                    Modalidad de la reunión
                </label>

                <select
                    class="form-select"
                    name="modalidad_reunion"
                    id="modalidadDiscipulado"
                >
                    <option value="PRESENCIAL" <?= (!$vinculoActual || $vinculoActual['modalidad'] === 'PRESENCIAL') ? 'selected' : '' ?>>Presencial</option>
                    <option value="VIRTUAL" <?= ($vinculoActual && $vinculoActual['modalidad'] === 'VIRTUAL') ? 'selected' : '' ?>>Virtual</option>
                </select>

            </div>

            <div
                class="form-group"
                id="grupoRecuperacionDiscipulado"
                style="<?= $esPersonalizado === false && $reunion['tipo'] === 'Discipulado' ? '' : 'display:none;' ?>"
            >

                <label class="form-label">
                    <input
                        type="checkbox"
                        name="es_recuperacion"
                        value="1"
                        id="esRecuperacionDiscipulado"
                        <?= ($vinculoActual && (int)$vinculoActual['es_recuperacion'] === 1) ? 'checked' : '' ?>
                    >
                    Esta reunión es una recuperación
                </label>

            </div>

        </div>

                <div class="form-actions">

            <a
                href="index.php"
                class="btn btn-back"
            >

            
                Volver

            </a>

            <button
                type="submit"
                class="btn btn-primary"
            >

                Guardar cambios

            </button>

        </div>

    </form>

</div>

<script>
    const clasesPorCicloDiscipulado = <?= json_encode($clasesPorCicloDiscipulado, JSON_UNESCAPED_UNICODE) ?>;
</script>

<script src="<?= BASE_URL ?>/assets/js/modulos/reuniones/editar.js"></script>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>