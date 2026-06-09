<?php
require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/permiso.php";
require_once __DIR__ . "/../../config/conexion.php";

if (!tienePermiso('gestionar_reuniones')) {
    header("Location: ../dashboard.php");
    exit;
}

/* =========================
   CSS
========================= */
$extraCSS = '
<link rel="stylesheet" href="' . BASE_URL . '/assets/css/modules/reuniones/reuniones.css">
';

require_once __DIR__ . "/../../includes/header.php";
?>

<div class="reuniones">

    <div class="form-card">

        <h1 class="form-title">Crear reunión</h1>

        <form action="<?= BASE_URL ?>/controllers/reunionController.php" method="POST">

            <!-- TIPO -->
            <div class="form-group">

                <label>Tipo de reunión</label>

                <select name="tipo" required>

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

                <input
                    type="text"
                    name="tipo_personalizado"
                    placeholder="Ej: Cumpleaños, Navidad"
                >

            </div>

            <!-- FECHA -->
            <div class="form-group">

                <label>Fecha</label>

                <input type="date" name="fecha" required>

            </div>

            <!-- BOTONES -->
            <div class="form-actions">

                <button
                    type="submit"
                    name="crear_reunion"
                    class="btn-primary"
                >
                    Guardar
                </button>

                <a href="index.php" class="btn-secondary">
                    Cancelar
                </a>

            </div>

        </form>

    </div>

</div>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>