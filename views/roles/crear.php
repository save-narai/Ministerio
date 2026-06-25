
<?php

require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/permiso.php";
require_once __DIR__ . "/../../config/conexion.php";

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

$permisos = $pdo->query("
    SELECT
        id,
        nombre,
        descripcion
    FROM permisos
    ORDER BY nombre ASC
")->fetchAll(PDO::FETCH_ASSOC);

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

            <div class="permissions-grid">

               <?php foreach($permisos as $permiso): ?>

    <label class="permission-card">

        <input
            type="checkbox"
            name="permisos[]"
            value="<?= (int)$permiso["id"] ?>"
        >

        <span class="checkmark"></span>

        <div class="permission-content">

            <strong>

                <?= htmlspecialchars(
                    $permiso["nombre"]
                ) ?>

            </strong>

            <?php if(!empty($permiso["descripcion"])): ?>

                <small>

                    <?= htmlspecialchars(
                        $permiso["descripcion"]
                    ) ?>

                </small>

            <?php endif; ?>

        </div>

    </label>

<?php endforeach; ?>

            </div>

        </div>

        <!-- =====================================
             BOTONES
        ====================================== -->

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
                name="crear_rol"
                class="btn btn-primary"
            >

               

                Guardar Rol

            </button>

        </div>

    </form>

</div>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>
