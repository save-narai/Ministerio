<?php

require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/permiso.php";
require_once __DIR__ . "/../../config/conexion.php";
require_once __DIR__ . "/../../helpers/csrf.php";

/* =====================================
   CSRF TOKEN
===================================== */

generarCsrf();


/* =====================================
   PERMISOS
===================================== */

if (!tienePermiso('gestionar_roles')) {

    header("Location: ../dashboard.php");
    exit;
}

/* =====================================
   OBTENER PERMISOS DISPONIBLES
===================================== */

$stmt = $pdo->prepare("
    SELECT
        id,
        nombre,
        descripcion
    FROM permisos
    ORDER BY nombre ASC
");

$stmt->execute();

$permisos = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =====================================
   HEADER
===================================== */

require_once __DIR__ . "/../../includes/header.php";

?>

<div class="form-card">

    <!-- =====================================
         HEADER
    ====================================== -->

    <div class="form-header">

        <div class="form-header-icon">

            <i class="fa-solid fa-shield-halved"></i>

        </div>

        <div class="form-header-content">

            <h1 class="form-title">
                Crear Rol
            </h1>

            <p class="form-subtitle">

                Crea un nuevo rol y define los permisos
                que tendrá dentro del sistema.

            </p>

        </div>

    </div>

    <!-- =====================================
         FORMULARIO
    ====================================== -->
<form
    action="<?= BASE_URL ?>/controllers/rolController.php"
    method="POST"
    class="form"
>

    <?= csrfField(); ?>

    <input
        type="hidden"
        name="action"
        value="crear_rol"
    >

    
        <!-- NOMBRE DEL ROL -->

        <div class="form-grid">

            <div class="form-group form-group-full">

                <label class="form-label">

                    Nombre del Rol

                </label>

                <input
                    type="text"
                    name="nombre"
                    class="form-input"
                    placeholder="Ej: Coordinador"
                    maxlength="80"
                    autocomplete="off"
                    required
                >

            </div>

        </div>

        <!-- =====================================
             PERMISOS
        ====================================== -->

        <div class="page-section">

            <div class="section-header">

                <div>

                    <h2 class="section-title">
                        Permisos del Rol
                    </h2>

                    <p class="section-subtitle">
                        Selecciona los permisos que tendrá este rol.
                    </p>

                </div>

            </div>

            <div class="form-info">

                <i class="fa-solid fa-circle-info"></i>

                <span>
                    Un rol sin permisos no podrá realizar acciones dentro del sistema.
                </span>

            </div>

            <!-- Barra de acciones -->

            <div class="permissions-toolbar">

                <button
                    type="button"
                    id="seleccionarTodos"
                    class="btn btn-secondary btn-sm"
                >

                    <i class="fa-solid fa-check-double"></i>

                    Seleccionar todos

                </button>

                <button
                    type="button"
                    id="limpiarTodos"
                    class="btn btn-back btn-sm"
                >

                    <i class="fa-solid fa-eraser"></i>

                    Limpiar

                </button>

            </div>

            <?php if(empty($permisos)): ?>

                <div class="empty-state">

                    No existen permisos registrados.

                </div>

            <?php else: ?>

                <div class="permissions-grid">

                    <?php foreach ($permisos as $permiso): ?>

                        <label
                            class="permission-card"
                            for="permiso_<?= (int)$permiso["id"] ?>"
                        >

                            <input
                                id="permiso_<?= (int)$permiso["id"] ?>"
                                type="checkbox"
                                name="permisos[]"
                                value="<?= (int)$permiso["id"] ?>"
                            >

                            <span class="checkmark"></span>

                            <div class="permission-content">

                                <strong>

    <?= htmlspecialchars(

        ucwords(

            str_replace(
                '_',
                ' ',
                $permiso["nombre"]
            )

        )

    ) ?>

</strong>

                                <?php if (!empty($permiso["descripcion"])): ?>

                                    <small>

                                        <?= htmlspecialchars($permiso["descripcion"]) ?>

                                    </small>

                                <?php endif; ?>

                            </div>

                        </label>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

        </div>

        <!-- =====================================
             BOTONES
        ====================================== -->

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

    Guardar Rol

</button>

        </div>

    </form>

</div>

<script src="<?= BASE_URL ?>/assets/js/modulos/roles/crear.js"></script>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>