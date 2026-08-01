<?php

require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/permiso.php";
require_once __DIR__ . "/../../config/conexion.php";
require_once __DIR__ . "/../../services/reunionService.php";

if (!tienePermiso('gestionar_reuniones')) {
    header("Location: ../dashboard.php");
    exit;
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

<script src="<?= BASE_URL ?>/assets/js/modulos/reuniones/crear.js"></script>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>