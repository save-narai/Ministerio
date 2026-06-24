<?php

require_once "../../middleware/auth.php";
require_once "../../middleware/permiso.php";
require_once "../../config/conexion.php";

if (!tienePermiso('gestionar_usuarios')) {

    header("Location: ../dashboard.php");
    exit;
}

/* =====================================
   ROLES
===================================== */

$roles = $pdo->query("
    SELECT
        id,
        nombre
    FROM roles
    ORDER BY nombre ASC
")->fetchAll(PDO::FETCH_ASSOC);

/* =====================================
   HEADER
===================================== */

require_once "../../includes/header.php";

?>

<div class="form-card">

    <!-- =====================================
         HEADER
    ====================================== -->

    <div class="form-header">

        <div class="form-header-icon">

            <i class="fa-solid fa-user-plus"></i>

        </div>

        <div class="form-header-content">

            <h1 class="form-title">
                Crear Usuario
            </h1>

            <p class="form-subtitle">
                Registra un nuevo usuario y asígnale un rol dentro del sistema.
            </p>

        </div>

    </div>

    <!-- =====================================
         INFORMACIÓN
    ====================================== -->

    <div class="form-info">

        <i class="fa-solid fa-circle-info"></i>

        Los permisos del usuario dependerán del rol asignado dentro del sistema.

    </div>

    <!-- =====================================
         FORMULARIO
    ====================================== -->

    <form
        action="../../controllers/usuarioController.php"
        method="POST"
        class="form"
    >

        <div class="form-grid">

            <!-- NOMBRE -->

            <div class="form-group">

                <label class="form-label">

                    <i class="fa-solid fa-user"></i>

                    Nombre completo

                </label>

                <input
                    type="text"
                    name="nombre"
                    class="form-input"
                    placeholder="Nombre completo del usuario"
                    required
                >

            </div>

            <!-- USUARIO -->

            <div class="form-group">

                <label class="form-label">

                    <i class="fa-solid fa-at"></i>

                    Usuario

                </label>

                <input
                    type="text"
                    name="usuario"
                    class="form-input"
                    placeholder="Nombre de usuario"
                    autocomplete="off"
                    required
                >

            </div>

            <!-- CONTRASEÑA -->

            <div class="form-group">

                <label class="form-label">

                    <i class="fa-solid fa-lock"></i>

                    Contraseña

                </label>

                <input
                    type="password"
                    name="password"
                    class="form-input"
                    placeholder="Ingrese una contraseña"
                    autocomplete="new-password"
                    required
                >

            </div>

            <!-- ROL -->

            <div class="form-group">

                <label class="form-label">

                    <i class="fa-solid fa-shield-halved"></i>

                    Rol

                </label>

                <select
                    name="rol_id"
                    class="form-select"
                    required
                >

                    <option value="">
                        Seleccionar rol
                    </option>

                    <?php foreach($roles as $rol): ?>

                        <option
                            value="<?= (int)$rol['id'] ?>"
                        >

                            <?= htmlspecialchars(
                                $rol['nombre']
                            ) ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>

        </div>

        <!-- BOTONES -->

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
                name="crear_usuario"
                class="btn btn-primary"
            >

                Guardar Usuario

            </button>

        </div>

    </form>

</div>

<?php require_once "../../includes/footer.php"; ?>