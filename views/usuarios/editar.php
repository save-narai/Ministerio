<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/bootstrap.php';

require_once __DIR__ . '/../../services/usuarioService.php';

/*
|--------------------------------------------------------------------------
| TODO
|--------------------------------------------------------------------------
|
| Cuando RolService esté terminado,
| reemplazar la consulta de roles por:
|
| obtenerRoles($pdo);
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
   ID
========================================================== */

$id = (int) (

    $_GET['id'] ?? 0

);

if (

    $id <= 0

) {

    header(

        "Location: index.php"

    );

    exit;

}

/* ==========================================================
   USUARIO
========================================================== */

$usuario = obtenerUsuarioPorId(

    $pdo,

    $id

);

if (

    !$usuario

) {

    header(

        "Location: index.php"

    );

    exit;

}

/* ==========================================================
   PROTECCIÓN
========================================================== */

$esAdministradorPrincipal =

    esAdministradorPrincipal(

        $pdo,

        $id

    );
$usuarioId = usuarioId();

$esMiCuenta =

    $usuarioId === $id;

$puedeGestionar =

    puedeGestionarUsuario(

        $pdo,

        $usuarioId,

        $id

    );

if (

    !$puedeGestionar

) {

    header(

        "Location: index.php"

    );

    exit;

}

/* ==========================================================
   ROLES
========================================================== */

$roles = $pdo->query("

    SELECT

        id,

        nombre

    FROM roles

    ORDER BY nombre ASC

")->fetchAll(PDO::FETCH_ASSOC);

/* ==========================================================
   CONFIGURACIÓN
========================================================== */

$titulo =

    'Editar Usuario';

$subtitulo =

    'Actualiza la información, el rol y los permisos del usuario seleccionado.';

/* ==========================================================
   HEADER
========================================================== */

require_once "../../includes/header.php";

?>

<div class="form-card">

    <!-- =====================================================
         HEADER
    ====================================================== -->

    <div class="form-header">

        <div class="form-header-icon">

            <i class="fa-solid fa-user-pen"></i>

        </div>

        <div class="form-header-content">

            <h1 class="form-title">

                Editar usuario

            </h1>

            <p class="form-subtitle">

                Actualiza la información del usuario y modifica su rol o contraseña cuando sea necesario.

            </p>

        </div>

    </div>

    <!-- =====================================================
         INFORMACIÓN
    ====================================================== -->

    <div class="form-info">

        <i class="fa-solid fa-circle-info"></i>

        <div>

            <strong>

                Actualización de información

            </strong>

            <p>

                Modifica los datos del usuario según sea necesario.
                Si dejas el campo de contraseña vacío, se conservará
                la contraseña actual.

            </p>

            <?php if ($esAdministradorPrincipal): ?>

                <p>

                    <strong>Importante:</strong>

                    Esta cuenta corresponde al
                    <strong>Administrador Principal</strong>.
                    Algunas opciones se encuentran protegidas por
                    motivos de seguridad.

                </p>

            <?php endif; ?>

        </div>

    </div>

    <!-- =====================================================
         FORMULARIO
    ====================================================== -->

    <form

        action="../../controllers/usuarioController.php"

        method="POST"

        class="form"

        autocomplete="off"

    >

        <?= csrfField(); ?>

        <input

            type="hidden"

            name="action"

            value="editar_usuario"

        >

        <input

            type="hidden"

            name="id"

            value="<?= (int) $usuario['id'] ?>"

        >

        <div class="form-grid">

            <!-- =====================================
                 NOMBRE
            ====================================== -->

            <div class="form-group">

                <label class="form-label">

                    <i class="fa-solid fa-user"></i>

                    Nombre completo

                </label>

                <input

                    type="text"

                    name="nombre"

                    class="form-input"

                    value="<?= htmlspecialchars($usuario['nombre']) ?>"

                    required

                >

            </div>

            <!-- =====================================
                 USUARIO
            ====================================== -->

            <div class="form-group">

                <label class="form-label">

                    <i class="fa-solid fa-at"></i>

                    Usuario

                </label>

                <input

                    type="text"

                    name="usuario"

                    class="form-input"

                    value="<?= htmlspecialchars($usuario['usuario']) ?>"

                    autocomplete="off"

                    required

                >

            </div>

            <!-- =====================================
                 CORREO
            ====================================== -->

            <div class="form-group">

                <label class="form-label">

                    <i class="fa-solid fa-envelope"></i>

                    Correo electrónico

                </label>

                <input

                    type="email"

                    name="correo"

                    class="form-input"

                    value="<?= htmlspecialchars($usuario['correo']) ?>"

                    autocomplete="email"

                    required

                >

            </div>

            <!-- =====================================
                 CONTRASEÑA
            ====================================== -->

            <div class="form-group">

                <label class="form-label">

                    <i class="fa-solid fa-lock"></i>

                    Nueva contraseña

                </label>

                <input

                    type="password"

                    name="password"

                    class="form-input"

                    placeholder="Déjalo vacío para conservar la contraseña actual"

                    autocomplete="new-password"

                >

                <small class="form-help">

                    Solo diligéncialo si deseas cambiar la contraseña.

                </small>

            </div>

            <!-- =====================================
                 ROL
            ====================================== -->

            <div class="form-group">

                <label class="form-label">

                    <i class="fa-solid fa-shield-halved"></i>

                    Rol del usuario

                </label>

                <?php if (

                    $esAdministradorPrincipal

                    &&

                    !$esMiCuenta

                ): ?>

                    <input

                        type="text"

                        class="form-input"

                        value="<?= htmlspecialchars($usuario['rol_nombre']) ?>"

                        readonly

                    >

                    <input

                        type="hidden"

                        name="rol_id"

                        value="<?= (int) $usuario['rol_id'] ?>"

                    >

                    <small class="form-help">

                        El rol del Administrador Principal no puede modificarse.

                    </small>

                <?php else: ?>

                    <select

                        name="rol_id"

                        class="form-select"

                        required

                    >

                        <?php foreach ($roles as $rol): ?>

                            <option

                                value="<?= (int) $rol['id'] ?>"

                                <?=

                                (int) $usuario['rol_id'] === (int) $rol['id']

                                    ? 'selected'

                                    : ''

                                ?>

                            >

                                <?= htmlspecialchars($rol['nombre']) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                <?php endif; ?>

            </div>

        </div>

        <!-- =====================================================
             BOTONES
        ====================================================== -->

        <div class="form-actions">

            <a

                href="<?= BASE_URL ?>/views/usuarios/index.php"

                class="btn btn-back"

            >


                Volver

            </a>

            <button

                type="submit"

                class="btn btn-primary"

            >

               

                Guardar cambios

            </button>

        </div>

    </form>

</div>

</div>

<!-- ==========================================================
     CONFIGURACIÓN JAVASCRIPT
========================================================== -->

<script>

window.USUARIO_EDITAR = {

    esAdministradorPrincipal:

        <?= $esAdministradorPrincipal ? 'true' : 'false' ?>,

    esMiCuenta:

        <?= $esMiCuenta ? 'true' : 'false' ?>

};

</script>

<!-- ==========================================================
     MÓDULO
========================================================== -->

<script

    src="<?= BASE_URL ?>/assets/js/modulos/usuarios/editar.js"

></script>

<?php

require_once "../../includes/footer.php";

?>

