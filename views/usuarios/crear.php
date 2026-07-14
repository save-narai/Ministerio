<?php

declare(strict_types=1);

require_once "../../middleware/auth.php";
require_once "../../middleware/permiso.php";

require_once "../../config/conexion.php";

require_once "../../middleware/csrf.php";

generarCSRF();

/* ==========================================================
   SEGURIDAD
========================================================== */

if (!tienePermiso('gestionar_usuarios')) {

    header("Location: ../dashboard.php");

    exit;

}

/* ==========================================================
   CONFIGURACIÓN
========================================================== */

$pageTitle = "Crear Usuario";

/* ==========================================================
   ROLES DISPONIBLES
========================================================== */

/*
|--------------------------------------------------------------------------
| Se excluye el rol Administrador.
|
| La plataforma únicamente debe tener un Administrador
| principal.
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("

    SELECT

        id,

        nombre

    FROM roles

    WHERE LOWER(nombre)

        NOT IN (

            'administrador',

            'admin'

        )

    ORDER BY nombre ASC

");

$stmt->execute();

$roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ==========================================================
   HEADER
========================================================== */

require_once "../../includes/header.php";

?>

<div class="form-card">
<!-- ======================================================
     HEADER
====================================================== -->

<div class="form-header">

    <div class="form-header-icon">

        <i class="fa-solid fa-user-plus"></i>

    </div>

    <div class="form-header-content">

        <h1 class="form-title">

            Crear nuevo usuario

        </h1>

        <p class="form-subtitle">

            Registra un nuevo usuario y asígnale un rol dentro del Sistema de Seguimiento Ministerial.

        </p>

    </div>

</div>

<!-- ======================================================
     INFORMACIÓN
====================================================== -->

<div class="form-info">

    <i class="fa-solid fa-circle-info"></i>

    <div>

        <strong>

            Creación automática de credenciales

        </strong>

        <p>

            Al guardar el usuario, el sistema generará una contraseña temporal,
            la almacenará de forma segura y enviará automáticamente las
            credenciales al correo electrónico registrado.

        </p>

        <p>

            <strong>Importante:</strong>
            El rol <strong>Administrador Principal</strong> está protegido y
            no puede crearse desde este formulario.

        </p>

    </div>

</div>

<!-- ======================================================
     FORMULARIO
====================================================== -->

<form

    action="../../controllers/usuarioController.php"

    method="POST"

    id="crearUsuarioForm"

    class="form"

    autocomplete="off"

>

    <?= csrfField(); ?>

    <input

        type="hidden"

        name="action"

        value="crear_usuario"

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

                id="nombre"

                name="nombre"

                class="form-input"

                maxlength="120"

                placeholder="Nombre completo"

                autocomplete="name"

                required

            >

        </div>

        <!-- USUARIO -->

        <div class="form-group">

            <label class="form-label">

                <i class="fa-solid fa-at"></i>

                Nombre de usuario

            </label>

            <input

                type="text"

                id="usuario"

                name="usuario"

                class="form-input"

                maxlength="50"

                placeholder="Usuario para iniciar sesión"

                autocomplete="off"

                spellcheck="false"

                required

            >

        </div>

        <!-- CORREO -->

        <div class="form-group">

            <label class="form-label">

                <i class="fa-solid fa-envelope"></i>

                Correo electrónico

            </label>

            <input

                type="email"

                id="correo"

                name="correo"

                class="form-input"

                maxlength="150"

                placeholder="correo@ejemplo.com"

                autocomplete="email"

                required

            >

        </div>

        <!-- ROL -->

        <div class="form-group">

            <label class="form-label">

                <i class="fa-solid fa-shield-halved"></i>

                Rol del usuario

            </label>

            <select

                id="rol_id"

                name="rol_id"

                class="form-select"

                required

            >

                <option value="">

                    Seleccione un rol

                </option>

                <?php foreach ($roles as $rol): ?>

                    <option value="<?= (int) $rol['id'] ?>">

                        <?= htmlspecialchars($rol['nombre']) ?>

                    </option>

                <?php endforeach; ?>

            </select>

        </div>

    </div>
    <!-- ======================================================
         BOTONES
    ======================================================= -->

    <div class="form-actions">

        <a
            href="index.php"
            class="btn btn-back"
        >

        

            Cancelar

        </a>

        <button
            type="reset"
            class="btn btn-secondary"
        >

           

            Limpiar

        </button>

        <button
            type="submit"
            class="btn btn-primary"
            id="btnCrearUsuario"
        >

           

            Crear usuario

        </button>

    </div>

</form>

</div>

<!-- ==========================================================
     JAVASCRIPT
========================================================== -->

<script>

    window.BASE_URL = "<?= BASE_URL ?>";

</script>

<script
    src="<?= BASE_URL ?>/assets/js/modulos/usuarios/crear.js">
</script>

<?php

require_once "../../includes/footer.php";

?>