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
   OBTENER ID
===================================== */

$rol_id = (int)($_GET["id"] ?? 0);

if ($rol_id <= 0) {

    header("Location: index.php");
    exit;
}

/* =====================================
   OBTENER ROL
===================================== */

$stmt = $pdo->prepare("
    SELECT
        id,
        nombre
    FROM roles
    WHERE id = :id
");

$stmt->execute([
    "id" => $rol_id
]);

$rol = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$rol) {

    header("Location: index.php");
    exit;
}

/* =====================================
   PERMISOS DISPONIBLES
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
   PERMISOS DEL ROL
===================================== */

$stmt = $pdo->prepare("
    SELECT permiso_id
    FROM rol_permiso
    WHERE rol_id = :rol_id
");

$stmt->execute([
    "rol_id" => $rol_id
]);

$permisosRol =
    $stmt->fetchAll(PDO::FETCH_COLUMN);

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
                Editar Rol
            </h1>

            <p class="form-subtitle">

                Gestiona los permisos asignados al rol:

                <strong>
                    <?= htmlspecialchars($rol["nombre"]) ?>
                </strong>

            </p>

        </div>

    </div>

    <!-- =====================================
         INFORMACIÓN
    ====================================== -->

    <div class="form-info">

        <i class="fa-solid fa-circle-info"></i>

        Selecciona los permisos que estarán disponibles para este rol dentro del sistema.

    </div>

    <!-- =====================================
         FORMULARIO
    ====================================== -->

    <form
        action="<?= BASE_URL ?>/controllers/rolController.php"
        method="POST"
        class="form"
    >

        <input
            type="hidden"
            name="rol_id"
            value="<?= $rol_id ?>"
        >

        <!-- NOMBRE DEL ROL -->

        <div class="form-grid">

            <div class="form-group form-group-full">

                <label class="form-label">
                    Nombre del Rol
                </label>

                <input
                    type="text"
                    class="form-input"
                    value="<?= htmlspecialchars($rol["nombre"]) ?>"
                    disabled
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
                        Activa o desactiva los permisos disponibles.
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
                            <?= in_array(
                                $permiso["id"],
                                $permisosRol
                            ) ? 'checked' : '' ?>
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

                            <?php if(!empty($permiso["descripcion"])): ?>

                                <small>

                                    <?= htmlspecialchars(
                                        $permiso["descripcion"]
                                    ) ?>

                                </small>

                            <?php else: ?>

                                <small>

                                    Permiso del sistema.

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
                Volver
            </a>

            <button
                type="submit"
                name="guardar_permisos"
                class="btn btn-primary"
            >
                Guardar Cambios
            </button>

        </div>

    </form>

</div>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>