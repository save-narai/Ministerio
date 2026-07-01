<?php
require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/permiso.php";

if (!tienePermiso('gestionar_jovenes')) {
    header("Location: ../dashboard.php");
    exit;
}


require_once __DIR__ . "/../../includes/header.php";
?>


<div class="form-card">

    <div class="form-header">

        <div class="form-header-icon">

            <i class="fa-solid fa-user-plus"></i>

        </div>

        <div class="form-header-content">

            <h1 class="form-title">
                Crear Joven
            </h1>

            <p class="form-subtitle">
                Registra un nuevo joven dentro del sistema de seguimiento y discipulado.
            </p>

        </div>

    </div>

    <form
        id="formJoven"
        class="form"
        action="<?= BASE_URL ?>/controllers/jovenController.php"
        method="POST"
    >

        <input
            type="hidden"
            name="csrf_token"
            value="<?= $_SESSION['csrf_token'] ?>"
        >

        <div class="form-grid">

            <div class="form-group form-group-full">

                <label class="form-label">
                    Nombre Completo
                </label>

                <input
                    class="form-input"
                    type="text"
                    name="nombre_completo"
                    required
                >

            </div>

            <div class="form-group">

                <label class="form-label">
                    Fecha de Nacimiento
                </label>

                <input
                    class="form-input"
                    type="date"
                    name="fecha_nacimiento"
                    id="fecha"
                >

            </div>

            <div class="form-group">

                <label class="form-label">
                    Edad
                </label>

                <input
                    class="form-input"
                    type="number"
                    name="edad_manual"
                    id="edad"
                >

            </div>

            <div class="form-group">

                <label class="form-label">
                    Teléfono
                </label>

                <input
                    class="form-input"
                    type="tel"
                    name="telefono"
                    id="telefono"
                    maxlength="10"
                >

                <small
                    id="telefonoError"
                    class="telefono-error"
                ></small>

                <div class="check-wrapper">

                    <label class="check-custom">

                        <input
                            type="checkbox"
                            name="sinTelefono"
                            id="sinTelefono"
                        >

                        <span class="checkmark"></span>

                        <span>
                            No tiene teléfono
                        </span>

                    </label>

                </div>

            </div>

            <div class="form-group">

                <label class="form-label">
                    Fecha de Ingreso
                </label>

                <input
                    class="form-input"
                    type="date"
                    name="fecha_ingreso"
                    value="<?= date('Y-m-d') ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label class="form-label">
                    Género
                </label>

                <select
                    class="form-select"
                    name="genero"
                >

                    <option value="">
                        Seleccionar
                    </option>

                    <option value="M">
                        Masculino
                    </option>

                    <option value="F">
                        Femenino
                    </option>

                </select>

            </div>

            <div class="form-group">

                <label class="form-label">
                    Estado Espiritual
                </label>

                <select
                    class="form-select"
                    name="estado_espiritual"
                >

                    <option value="">
                        Seleccionar
                    </option>

                    <option value="NUEVO">
                        Nuevo
                    </option>

                    <option value="CONGREGANTE">
                        Congregante
                    </option>

                    <option value="DISCIPULADO">
                        Discipulado
                    </option>

                    <option value="SERVIDOR">
                        Servidor
                    </option>

                    <option value="LIDER">
                        Líder
                    </option>

                </select>

            </div>

            <div class="form-group form-group-full">

                <label class="form-label">
                    ¿Es servidor?
                </label>

                <select
                    class="form-select"
                    name="es_servidor"
                >

                    <option value="0">
                        No
                    </option>

                    <option value="1">
                        Sí
                    </option>

                </select>

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
    class="btn btn-primary"
>
    Guardar Joven
</button>

        </div>

    </form>

</div>

<script
    src="<?= BASE_URL ?>/assets/js/modulos/jovenes/crear.js">
</script>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>