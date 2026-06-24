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
        ) AS total_permisos

    FROM roles r

    ORDER BY r.nombre ASC
")->fetchAll(PDO::FETCH_ASSOC);

/* =====================================
   ESTADÍSTICAS
===================================== */

$totalRoles = count($roles);

$totalPermisos = $pdo->query("
    SELECT COUNT(*) FROM permisos
")->fetchColumn();

/* =====================================
   HEADER
===================================== */

require_once __DIR__ . "/../../includes/header.php";

?>

<div class="roles-page">

    <!-- =====================================
         HEADER
    ====================================== -->

    <div class="page-header">

        <div class="page-header-left">

            <h1 class="page-title">
                Gestión de Roles
            </h1>

            <p class="page-subtitle">

                Administra roles y permisos del sistema.

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
    ====================================== -->

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

    </div>

    <!-- =====================================
         TABLA
    ====================================== -->

    <div class="page-section">

        <div class="section-header">

            <div>

                <h2 class="section-title">

                    Roles del sistema

                </h2>

                <p class="section-subtitle">

                    Configuración y administración de permisos.

                </p>

            </div>

            <div class="gx-toolbar">

                <div class="search-wrapper">

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

                            <?= htmlspecialchars(
                                $rol["nombre"]
                            ) ?>

                        </td>

                        <td>

                            <span class="badge badge-info">

                                <?= $rol["total_permisos"] ?>

                                permisos

                            </span>

                        </td>

                        <td>

                            <div class="table-actions">

                                <a
                                    href="editar.php?id=<?= (int)$rol["id"] ?>"
                                    class="btn-icon btn-edit"
                                    title="Editar permisos"
                                >

                                    <i class="fa-solid fa-shield-halved"></i>

                                </a>

                            </div>

                        </td>

                    </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    </div>

    <!-- =====================================
         BOTONES
    ====================================== -->

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

    initDataTable('#tablaRoles');

});

</script>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>