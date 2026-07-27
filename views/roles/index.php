<?php

require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/permiso.php";
require_once __DIR__ . "/../../config/conexion.php";
<<<<<<< HEAD
=======
require_once __DIR__ . "/../../helpers/csrf.php";

generarCsrf();
>>>>>>> 3e2d89c (Actualización del proyecto)

if (!tienePermiso('gestionar_roles')) {

    header("Location: ../dashboard.php");
    exit;
}

/* =====================================
   ROLES
===================================== */

$roles = $pdo->query("
    SELECT
        r.id,
        r.nombre,

        (
            SELECT COUNT(*)
            FROM rol_permiso rp
            WHERE rp.rol_id = r.id
        ) AS total_permisos,

        (
            SELECT COUNT(*)
            FROM usuarios u
            WHERE u.rol_id = r.id
        ) AS total_usuarios

    FROM roles r

    ORDER BY r.nombre ASC
")->fetchAll(PDO::FETCH_ASSOC);

/* =====================================
   ESTADÍSTICAS
===================================== */

$totalRoles = count($roles);

$totalPermisos = $pdo->query("
    SELECT COUNT(*)
    FROM permisos
")->fetchColumn();

$totalUsuarios = $pdo->query("
    SELECT COUNT(*)
    FROM usuarios
")->fetchColumn();

/* =====================================
   HEADER
===================================== */

require_once __DIR__ . "/../../includes/header.php";

?>

<div class="roles-page">

    <!-- =====================================
         PAGE HEADER
    ===================================== -->

    <div class="page-header">

        <div class="page-header-left">

            <h1 class="page-title">
                Gestión de Roles
            </h1>

            <p class="page-subtitle">
                Administra los roles y permisos del sistema.
            </p>

        </div>

        <div class="page-header-right">

            <a
                href="crear.php"
                class="btn btn-primary"
            >

                <i class="fa-solid fa-plus"></i>
                Nuevo Rol

            </a>

        </div>

    </div>

    <!-- =====================================
         ESTADÍSTICAS
    ===================================== -->

    <div class="gx-stats">

        <div class="gx-stat-card info">

            <div class="gx-stat-value">
                <?= $totalRoles ?>
            </div>

            <div class="gx-stat-label">
                Roles registrados
            </div>

        </div>

        <div class="gx-stat-card success">

            <div class="gx-stat-value">
                <?= $totalPermisos ?>
            </div>

            <div class="gx-stat-label">
                Permisos disponibles
            </div>

        </div>

        <div class="gx-stat-card warning">

            <div class="gx-stat-value">
                <?= $totalUsuarios ?>
            </div>

            <div class="gx-stat-label">
                Usuarios asignados
            </div>

        </div>

    </div>

    <!-- =====================================
         TABLA
    ===================================== -->

    <div class="page-section">

        <div class="section-header">

            <div>

                <h2 class="section-title">
                    Roles del Sistema
                </h2>

                <p class="section-subtitle">
                    Configuración y administración de permisos.
                </p>

            </div>

            <div class="gx-toolbar">

                <div
                    class="search-wrapper tooltip"
                    data-tooltip="Buscar rol"
                >

                    <input
                        type="text"
                        id="buscador"
                        class="search-input"
                        placeholder="Buscar rol..."
                    >

                </div>

            </div>

        </div>

        <div class="table-responsive">

            <table
                id="tablaRoles"
                class="table gx-table"
            >

                <thead>

                    <tr>

                        <th>ID</th>
                        <th>Nombre del Rol</th>
                        <th>Permisos</th>
                        <th>Usuarios</th>
                        <th>Acciones</th>

                    </tr>

                </thead>

                <tbody>

                    <?php if(empty($roles)): ?>

                        <tr>

                            <td colspan="5" class="text-center">
                                No existen roles registrados.
                            </td>

                        </tr>

                    <?php else: ?>

                        <?php foreach($roles as $rol): ?>

                            <tr>

                                <td>
                                    #<?= (int)$rol["id"] ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($rol["nombre"]) ?>
                                </td>

                                <td>

                                    <span class="badge badge-info">

                                        <?= (int)$rol["total_permisos"] ?>

                                        Permisos

                                    </span>

                                </td>

                                <td>

                                    <span class="badge badge-success">

                                        <?= (int)$rol["total_usuarios"] ?>

                                        Usuarios

                                    </span>

                                </td>

                                <td>

                                    <div class="table-actions">

                                        <a
                                            href="editar.php?id=<?= (int)$rol['id'] ?>"
                                            class="btn btn-primary btn-sm"
                                        >

<<<<<<< HEAD
                                            <i class="fa-solid fa-pen"></i>
=======
                                           
>>>>>>> 3e2d89c (Actualización del proyecto)

                                            Editar

                                        </a>

                                        <?php if($rol["nombre"] !== "ADMIN"): ?>

                                            <form
<<<<<<< HEAD
                                                action="<?= BASE_URL ?>/controllers/rolController.php"
                                                method="POST"
                                                onsubmit="return confirm('¿Deseas eliminar este rol?');"
                                            >

                                                <input
                                                    type="hidden"
                                                    name="id"
                                                    value="<?= (int)$rol['id'] ?>"
                                                >

                                                <button
                                                    type="submit"
                                                    name="eliminar_rol"
                                                    class="btn btn-back btn-sm"
                                                >

                                                    <i class="fa-solid fa-trash"></i>

                                                    Eliminar

                                                </button>

                                            </form>
=======
    action="<?= BASE_URL ?>/controllers/rolController.php"
    method="POST"
    onsubmit="return confirm('¿Deseas eliminar este rol?');"
>

    <input
        type="hidden"
        name="id"
        value="<?= (int)$rol['id'] ?>"
    >

    <input
        type="hidden"
        name="action"
        value="eliminar_rol"
    >

    <input
        type="hidden"
        name="csrf_token"
        value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"
    >

    <button
        type="submit"
        class="btn btn-back btn-sm"
    >
        Eliminar
    </button>

</form>
>>>>>>> 3e2d89c (Actualización del proyecto)

                                        <?php endif; ?>

                                    </div>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

<<<<<<< HEAD
    <div class="form-actions">

        <a
            href="../dashboard.php"
            class="btn btn-back"
        >

            <i class="fa-solid fa-arrow-left"></i>

            Volver

        </a>

    </div>
=======
>>>>>>> 3e2d89c (Actualización del proyecto)

</div>

<script src="<?= BASE_URL ?>/assets/js/modulos/roles/index.js"></script>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>