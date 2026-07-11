<?php

require_once "../../middleware/auth.php";
require_once "../../middleware/permiso.php";
require_once "../../config/conexion.php";
require_once "../../services/usuarioService.php";

if (!tienePermiso('gestionar_usuarios')) {

    header("Location: ../dashboard.php");
    exit;
}

$usuarios = obtenerUsuarios($pdo);


/* =====================================
   ESTADÍSTICAS
===================================== */

$totalUsuarios = count($usuarios);

$totalActivos = count(
    array_filter(
        $usuarios,
        fn($u) => $u['activo']
    )
);

$totalInactivos = $totalUsuarios - $totalActivos;

$totalRoles = $pdo
    ->query("SELECT COUNT(*) FROM roles")
    ->fetchColumn();

/* =====================================
   HEADER
===================================== */

require_once "../../includes/header.php";

?>

<div class="usuarios-page">

    <!-- =====================================
         HEADER
    ====================================== -->

    <div class="page-header">

        <div class="page-header-left">

            <h1 class="page-title">
                Usuarios
            </h1>

            <div class="page-subtitle">
                Administra accesos, cuentas y permisos del sistema.
            </div>

        </div>

        <div class="page-header-right">

            <a
                href="crear.php"
                class="btn btn-primary"
            >

                <i class="fa-solid fa-user-plus"></i>

                Nuevo Usuario

            </a>

        </div>

    </div>

    <!-- =====================================
         STATS
    ====================================== -->

    <div class="stats-grid gx-stats">

        <div class="stat-card info">

            <span class="stat-number">
                <?= $totalUsuarios ?>
            </span>

            <span class="stat-label">
                Total usuarios
            </span>

        </div>

        <div class="stat-card success">

            <span class="stat-number">
                <?= $totalActivos ?>
            </span>

            <span class="stat-label">
                Activos
            </span>

        </div>

        <div class="stat-card danger">

            <span class="stat-number">
                <?= $totalInactivos ?>
            </span>

            <span class="stat-label">
                Inactivos
            </span>

        </div>

        <div class="stat-card purple">

            <span class="stat-number">
                <?= $totalRoles ?>
            </span>

            <span class="stat-label">
                Roles registrados
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
                    Gestión general de cuentas
                </h2>

            </div>

            <div class="gx-toolbar">

                <div class="search-wrapper">

                    <input
                        type="text"
                        id="buscador"
                        class="search-input"
                        placeholder="Buscar usuario..."
                    >

                </div>

            </div>

        </div>

        <div class="table-responsive">

            <table
                id="tablaUsuarios"
                class="table gx-table"
            >

                <thead>

                    <tr>
<th>ID</th>
<th>Nombre</th>
<th>Usuario</th>
<th>Correo</th>
<th>Rol</th>
<th>Estado</th>
<th>Acciones</th>

                    </tr>

                </thead>

                <tbody>

                    <?php foreach ($usuarios as $u): ?>

                        <tr>

                            <td>
                                <?= (int) $u["id"] ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($u["nombre"]) ?>
                            </td>
<td>
    <?= htmlspecialchars($u["usuario"]) ?>
</td>

<td>
    <?= htmlspecialchars(
        $u["correo"] ?? "-"
    ) ?>
</td>

<td>
    <?= htmlspecialchars(
        $u["rol"] ?? "Sin rol"
    ) ?>
</td>
                            <td>

                                <?php if ($u["activo"]): ?>

                                    <span class="badge badge-success">
                                        Activo
                                    </span>

                                <?php else: ?>

                                    <span class="badge badge-danger">
                                        Inactivo
                                    </span>

                                <?php endif; ?>

                            </td>

                            <td>

                                <div class="table-actions">

                                    <!-- EDITAR -->

                                    <a
                                        href="editar.php?id=<?= (int) $u['id'] ?>"
                                        class="btn-icon btn-edit"
                                        data-tooltip="Editar usuario"
                                    >

                                        <i class="fa-solid fa-pen"></i>

                                    </a>

                                    <!-- ACTIVAR / DESACTIVAR -->

                                    <?php if ($_SESSION['user_id'] != $u['id']): ?>

                                        <a
                                            href="../../controllers/toggleUsuario.php?id=<?= (int) $u['id'] ?>"
                                            class="btn-icon btn-danger"
                                            data-tooltip="<?= $u['activo']
                                                ? 'Desactivar usuario'
                                                : 'Activar usuario' ?>"
                                        >

                                            <i class="fa-solid fa-power-off"></i>

                                        </a>

                                    <?php else: ?>

                                        <span
                                            class="btn-icon btn-disabled"
                                            data-tooltip="No puedes desactivar tu propia cuenta"
                                        >

                                            <i class="fa-solid fa-lock"></i>

                                        </span>

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

<script
    src="<?= BASE_URL ?>/assets/js/modulos/usuarios/index.js">
</script>

<?php require_once "../../includes/footer.php"; ?>