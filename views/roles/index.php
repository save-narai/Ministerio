<?php

require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/permiso.php";
require_once __DIR__ . "/../../config/conexion.php";

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

    <!-- HEADER -->

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

    <!-- ESTADÍSTICAS -->

    <div class="stats-grid gx-stats">

        <div class="stat-card info">

            <span class="stat-number">
                <?= $totalRoles ?>
            </span>

            <span class="stat-label">
                Roles registrados
            </span>

        </div>

        <div class="stat-card success">

            <span class="stat-number">
                <?= $totalPermisos ?>
            </span>

            <span class="stat-label">
                Permisos disponibles
            </span>

        </div>

        <div class="stat-card warning">

            <span class="stat-number">
                <?= $totalUsuarios ?>
            </span>

            <span class="stat-label">
                Usuarios asignados
            </span>

        </div>

    </div>

    <!-- TABLA -->

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
                        <th>Rol</th>
                        <th>Permisos</th>
                        <th>Usuarios</th>
                        <th>Acciones</th>

                    </tr>

                </thead>

                <tbody>

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

                                    <?= $rol["total_permisos"] ?>

                                    permisos

                                </span>

                            </td>

                            <td>

                                <span class="badge badge-success">

                                    <?= $rol["total_usuarios"] ?>

                                    usuarios

                                </span>

                            </td>

                            <td>

                                <div class="table-actions">

                                    <a
                                        href="editar.php?id=<?= (int)$rol['id'] ?>"
                                        class="btn btn-primary btn-sm"
                                    >

                                        Editar

                                    </a>

                                    <?php if($rol["nombre"] !== "ADMIN"): ?>

                                        <form
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

                                                Eliminar

                                            </button>

                                        </form>

                                    <?php endif; ?>

                                </div>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    </div>

    <div class="form-actions">

        <a
            href="../dashboard.php"
            class="btn btn-back"
        >

            <i class="fa-solid fa-arrow-left"></i>

            Volver

        </a>

    </div>

</div>

<script>

document.addEventListener('DOMContentLoaded', ()=>{

    const tabla =
        initDataTable('#tablaRoles');

    initSearch(
        'buscador',
        tabla
    );

});

</script>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>