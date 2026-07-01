<?php

require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/permiso.php";
require_once __DIR__ . "/../../config/conexion.php";

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
            value="<?= $_SESSION['csrf_token'] ?>"
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

            <!-- TIPO PERSONALIZADO -->

           <div
    class="form-group"
    id="grupoTipoPersonalizado"
>

    <label class="form-label">
        Tipo personalizado
    </label>

    <input
        class="form-input"
        type="text"
        name="tipo_personalizado"
        id="tipoPersonalizado"
        placeholder="Ej: Navidad, Campamento..."
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

                <i class="fa-solid fa-arrow-left"></i>

                Volver

            </a>

            <button
                type="submit"
                name="crear_reunion"
                class="btn btn-primary"
            >

                Guardar reunión

            </button>

        </div>

    </form>

</div>
<script
    src="<?= BASE_URL ?>/assets/js/modulos/reuniones/crear.js">
</script>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>