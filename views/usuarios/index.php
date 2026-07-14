<?php

declare(strict_types=1);

require_once "../../middleware/auth.php";
require_once "../../middleware/permiso.php";

require_once "../../config/conexion.php";

require_once "../../services/UsuarioService.php";
require_once __DIR__ . "/../../middleware/csrf.php";

generarCSRF();



/*
|--------------------------------------------------------------------------
| TODO
|--------------------------------------------------------------------------
|
| Cuando el RolService esté terminado,
| simplemente descomentar:
|
| require_once "../../services/RolService.php";
|
*/

// require_once "../../services/RolService.php";

/* ==========================================================
   PERMISOS
========================================================== */

if (

    !tienePermiso(

        'gestionar_usuarios'

    )

) {

    header(

        "Location: ../dashboard.php"

    );

    exit;

}

/* ==========================================================
   USUARIOS
========================================================== */

$usuarios = obtenerUsuarios(

    $pdo

);

/* ==========================================================
   ESTADÍSTICAS
========================================================== */

$totalUsuarios = count(

    $usuarios

);

$totalActivos = count(

    array_filter(

        $usuarios,

        fn(array $usuario): bool =>

            (bool) $usuario['activo']

    )

);

$totalInactivos =

    $totalUsuarios -

    $totalActivos;

/*
|--------------------------------------------------------------------------
| Temporalmente se mantiene esta consulta aquí.
|--------------------------------------------------------------------------
|
| Más adelante será:
|
| $totalRoles = obtenerTotalRoles($pdo);
|
*/

$totalRoles = (int)

    $pdo

        ->query(

            "SELECT COUNT(*) FROM roles"

        )

        ->fetchColumn();

/* ==========================================================
   CONFIGURACIÓN
========================================================== */

$titulo =

    'Usuarios';

$subtitulo =

    'Administra cuentas, accesos y permisos del sistema.';

/* ==========================================================
   HEADER
========================================================== */

require_once "../../includes/header.php";

?>

<div class="usuarios-page">

    <!-- ======================================================
         ENCABEZADO
    ======================================================= -->

    <header class="page-header">

        <div class="page-header-left">

            <h1 class="page-title">

                <?= htmlspecialchars($titulo) ?>

            </h1>

            <p class="page-subtitle">

                <?= htmlspecialchars($subtitulo) ?>

            </p>

        </div>

        <div class="page-header-right">

            <a

                href="crear.php"

                class="btn btn-primary"

            >

                <i class="fa-solid fa-user-plus"></i>

                Nuevo usuario

            </a>

        </div>

    </header>

    <!-- ======================================================
         ESTADÍSTICAS
    ======================================================= -->

    <section class="stats-grid gx-stats">

        <article class="stat-card info">

            <span class="stat-number">

                <?= $totalUsuarios ?>

            </span>

            <span class="stat-label">

                Usuarios registrados

            </span>

        </article>

        <article class="stat-card success">

            <span class="stat-number">

                <?= $totalActivos ?>

            </span>

            <span class="stat-label">

                Usuarios activos

            </span>

        </article>

        <article class="stat-card danger">

            <span class="stat-number">

                <?= $totalInactivos ?>

            </span>

            <span class="stat-label">

                Usuarios suspendidos

            </span>

        </article>

        <article class="stat-card purple">

            <span class="stat-number">

                <?= $totalRoles ?>

            </span>

            <span class="stat-label">

                Roles registrados

            </span>

        </article>

    </section>

  <!-- ======================================================
     TABLA
====================================================== -->

<section class="page-section">

    <div class="section-header">

        <div class="section-heading">

            <h2 class="section-title">
                Gestión General de Cuentas
            </h2>

            <p class="section-description">
                Administra usuarios, roles, estados y permisos del sistema.
            </p>

        </div>

        <div class="gx-toolbar">

            <div class="search-wrapper">

                <input
                    id="buscador"
                    type="search"
                    class="search-input"
                    placeholder="Buscar por nombre, usuario, correo o rol..."
                    autocomplete="off"
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
                    <th class="text-center">
                        Acciones
                    </th>

                </tr>

            </thead>

            <tbody>

            <?php foreach ($usuarios as $u): ?>

                <?php

                $esMiCuenta =

                    (int) $_SESSION['user_id'] === (int) $u['id'];

                $esAdministrador =

                    esAdministradorPrincipal(
                        $pdo,
                        (int) $u['id']
                    );

                $puedeGestionar =

                    puedeGestionarUsuario(
                        $pdo,
                        (int) $_SESSION['user_id'],
                        (int) $u['id']
                    );

                $puedeEliminar =

                    puedeEliminarUsuario(
                        $pdo,
                        (int) $_SESSION['user_id'],
                        (int) $u['id']
                    );

                ?>

                <tr>

                    <td>

                        <?= (int) $u['id'] ?>

                    </td>

                    <td>

                        <?= htmlspecialchars($u['nombre']) ?>

                    </td>

                    <td>

                        <?= htmlspecialchars($u['usuario']) ?>

                    </td>

                    <td>

                        <?= htmlspecialchars($u['correo'] ?? '-') ?>

                    </td>

                    <!-- ======================================
                         ROL
                    ======================================= -->

                    <td>

                        <div class="role-cell">

                            <span>

                                <?= htmlspecialchars($u['rol_nombre']) ?>

                            </span>

                            <?php if ($esAdministrador): ?>

                                <span class="badge badge-admin">

                                    Principal

                                </span>

                            <?php endif; ?>

                        </div>

                    </td>

                    <!-- ======================================
                         ESTADO
                    ======================================= -->

                    <td>

                        <?php if ((bool) $u['activo']): ?>

                            <span class="badge badge-success">

                                Activo

                            </span>

                        <?php else: ?>

                            <span class="badge badge-danger">

                                Suspendido

                            </span>

                        <?php endif; ?>

                    </td>

                    <!-- ======================================
                         ACCIONES
                    ======================================= -->

                    <td>

                        <div class="table-actions">

                            <?php if ($esAdministrador && !$esMiCuenta): ?>

                                <span
                                    class="btn-icon btn-disabled"
                                    data-tooltip="Cuenta protegida"
                                >

                                    <i class="fa-solid fa-shield-halved"></i>

                                </span>

                            <?php elseif (!$puedeGestionar): ?>

                                <span
                                    class="btn-icon btn-disabled"
                                    data-tooltip="No tienes permisos"
                                >

                                    <i class="fa-solid fa-lock"></i>

                                </span>

                            <?php else: ?>

                                <!-- EDITAR -->

                                <a
                                    href="editar.php?id=<?= (int) $u['id'] ?>"
                                    class="btn-icon btn-edit"
                                    data-tooltip="Editar usuario"
                                >

                                    <i class="fa-solid fa-pen"></i>

                                </a>

                                <!-- ACTIVAR / SUSPENDER -->

                                <?php if (!$esMiCuenta): ?>

                                    <form
                                        action="../../controllers/usuarioController.php"
                                        method="POST"
                                        class="inline-form"
                                    >

                                        <?= csrfField(); ?>

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int) $u['id'] ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="action"
                                            value="<?= $u['activo']
                                                ? 'desactivar_usuario'
                                                : 'activar_usuario' ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="btn-icon <?= $u['activo']
                                                ? 'btn-danger'
                                                : 'btn-warning' ?>"
                                            data-tooltip="<?= $u['activo']
                                                ? 'Suspender usuario'
                                                : 'Activar usuario' ?>"
                                            data-confirm="true"
                                            data-message="<?= $u['activo']
                                                ? '¿Deseas suspender este usuario?'
                                                : '¿Deseas activar este usuario?' ?>"
                                        >

                                            <i class="fa-solid fa-power-off"></i>

                                        </button>

                                    </form>

                                <?php endif; ?>

                                <!-- ELIMINAR -->

                                <?php if ($puedeEliminar): ?>

                                    <form
                                        action="../../controllers/usuarioController.php"
                                        method="POST"
                                        class="inline-form"
                                    >

                                        <?= csrfField(); ?>

                                        <input
                                            type="hidden"
                                            name="action"
                                            value="eliminar_usuario"
                                        >

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int) $u['id'] ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="btn-icon btn-delete"
                                            data-tooltip="Eliminar usuario"
                                            data-confirm="true"
                                            data-message="¿Deseas eliminar definitivamente este usuario?"
                                        >

                                            <i class="fa-solid fa-trash"></i>

                                        </button>

                                    </form>

                                <?php endif; ?>

                            <?php endif; ?>

                        </div>

                    </td>

                </tr>

            <?php endforeach; ?>

            </tbody>

        </table>

    </div>

</section>

<!-- ======================================================
     BOTONES
====================================================== -->

<footer class="form-actions">

   <a
    href="../dashboard.php"
    class="btn btn-back-dashboard"
>
    Volver al Dashboard
</a>

</footer>

</div>

<!-- ==========================================================
     MODAL DE CONFIRMACIÓN
========================================================== -->

<div

    id="confirmModal"

    class="confirm-modal"

    hidden

>

    <div class="confirm-modal-content">

        <!-- ==============================================
             ICONO
        =============================================== -->

        <div class="confirm-modal-icon">

            <i class="fa-solid fa-circle-question"></i>

        </div>

        <!-- ==============================================
             TÍTULO
        =============================================== -->

        <h3>

            Confirmar acción

        </h3>

        <!-- ==============================================
             MENSAJE
        =============================================== -->

        <p id="confirmMessage">

            ¿Deseas continuar?

        </p>

        <!-- ==============================================
             BOTONES
        =============================================== -->

        <div class="confirm-modal-actions">

            <button

                id="btnCancel"

                type="button"

                class="btn btn-back"

            >

                Cancelar

            </button>

            <button

                id="btnConfirm"

                type="button"

                class="btn btn-primary"

            >

                Continuar

            </button>

        </div>

    </div>

</div>

<!-- ==========================================================
     CONFIGURACIÓN JAVASCRIPT
========================================================== -->

<script>

window.USUARIOS_CONFIG = {

    usarModal: true,

    confirmarActivacion:

        "¿Deseas activar este usuario?",

    confirmarDesactivacion:

        "¿Deseas suspender este usuario?",

    administradorProtegido:

        "La cuenta Administrador Principal está protegida."

};

</script>

<!-- ==========================================================
     MÓDULO USUARIOS
========================================================== -->

<script

    src="<?= BASE_URL ?>/assets/js/modulos/usuarios/index.js"

></script>

<?php

require_once "../../includes/footer.php";

?>
