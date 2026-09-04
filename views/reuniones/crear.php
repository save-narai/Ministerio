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

/* =====================================
   CICLOS ACTIVOS + SUS CLASES (FASE 7)

   Se arma un mapa ciclo_id => [clases] en PHP y se imprime
   como JSON para que el select de "Clase" se filtre en el
   navegador según el ciclo elegido, sin necesidad de peticiones
   AJAX (no existe ese patrón en el resto del proyecto).
===================================== */

$ciclosActivosDiscipulado = obtenerCiclosDiscipulado($pdo, ['estado' => 'ACTIVO']);

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
            <i class="fa-solid fa-calendar-plus"></i>
        </div>

        <div class="form-header-content">

            <h1 class="form-title">
                Crear reunión
            </h1>

            <p class="form-subtitle">
                Registra una nueva reunión, discipulado o evento especial.
            </p>

        </div>

    </div>

    <form
        class="form"
        action="<?= BASE_URL ?>/controllers/reunionController.php"
        method="POST"
    >

        <input
            type="hidden"
            name="csrf_token"
            value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"
        >

        <input
            type="hidden"
            name="action"
            value="crear_reunion"
        >

        <div class="form-grid">

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

                    <option value="">
                        Seleccionar
                    </option>

                    <option value="REUNION_JOVENES">
                        Reunión Jóvenes
                    </option>

                    <option value="GRUPO_CONEXION">
                        Grupo Conexión
                    </option>

                    <option value="DISCIPULADO">
                        Discipulado
                    </option>

                    <option value="EVENTO_ESPECIAL">
                        Evento Especial
                    </option>

                    <option value="OTRO">
                        Otro...
                    </option>

                </select>

            </div>

            <!-- NOMBRE DEL EVENTO (SOLO PARA OTRO) -->

            <div
                class="form-group"
                id="grupoTipoPersonalizado"
                style="display:none;"
            >

                <label class="form-label">
                    Nombre del evento
                </label>

                <input
                    class="form-input"
                    type="text"
                    name="tipo_personalizado"
                    id="tipoPersonalizado"
                    placeholder="Ej: Campamento Juvenil"
                >

            </div>

            <!-- FECHA -->

            <div class="form-group">

                <label class="form-label">
                    Fecha
                </label>

                <input
                    class="form-input"
                    type="date"
                    name="fecha"
                    required
                >

            </div>

            <!-- =====================================
                 DISCIPULADO: CICLO + CLASE + MODALIDAD
                 (FASE 7 — solo visible si tipo = DISCIPULADO)
            ===================================== -->

            <div
                class="form-group"
                id="grupoCicloDiscipulado"
                style="display:none;"
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

                        <option value="<?= (int)$cicloActivo['id'] ?>">
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
                style="display:none;"
            >

                <label class="form-label">
                    Clase de ese ciclo
                </label>

                <select
                    class="form-select"
                    name="clase_id"
                    id="claseDiscipulado"
                >
                    <option value="">Selecciona primero un ciclo</option>
                </select>

            </div>

            <div
                class="form-group"
                id="grupoModalidadDiscipulado"
                style="display:none;"
            >

                <label class="form-label">
                    Modalidad de la reunión
                </label>

                <select
                    class="form-select"
                    name="modalidad_reunion"
                    id="modalidadDiscipulado"
                >
                    <option value="PRESENCIAL">Presencial</option>
                    <option value="VIRTUAL">Virtual</option>
                </select>

            </div>

            <div
                class="form-group"
                id="grupoRecuperacionDiscipulado"
                style="display:none;"
            >

                <label class="form-label">
                    <input type="checkbox" name="es_recuperacion" value="1" id="esRecuperacionDiscipulado">
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
                Guardar reunión
            </button>

        </div>

    </form>

</div>

<script>
    const clasesPorCicloDiscipulado = <?= json_encode($clasesPorCicloDiscipulado, JSON_UNESCAPED_UNICODE) ?>;
</script>

<script src="<?= BASE_URL ?>/assets/js/modulos/reuniones/crear.js"></script>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>