<?php

require_once "../../middleware/auth.php";
require_once "../../middleware/permiso.php";
require_once "../../config/conexion.php";

if (!tienePermiso('gestionar_usuarios')) {

    header("Location: ../dashboard.php");
    exit;
}

/* =====================================
   ID
===================================== */

$id = (int)($_GET['id'] ?? 0);

/* =====================================
   USUARIO
===================================== */

$stmt = $pdo->prepare("
    SELECT *
    FROM usuarios
    WHERE id = ?
");

$stmt->execute([$id]);

$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {

    header("Location: index.php");
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

            <i class="fa-solid fa-user-gear"></i>

        </div>

        <div class="form-header-content">

            <h1 class="form-title">
                Editar Usuario
            </h1>

            <p class="form-subtitle">
                Actualiza la información del usuario y modifica sus permisos dentro del sistema.
            </p>

        </div>

    </div>

    <!-- =====================================
         INFORMACIÓN
    ====================================== -->

    <div class="form-info">

        <i class="fa-solid fa-circle-info"></i>

        Los cambios realizados se aplicarán inmediatamente al usuario seleccionado.

    </div>

    <!-- =====================================
         FORMULARIO
    ====================================== -->

    <form
        action="../../controllers/usuarioController.php"
        method="POST"
        class="form"
    >

        <input
            type="hidden"
            name="id"
            value="<?= (int)$usuario['id'] ?>"
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
                    value="<?= htmlspecialchars($usuario['nombre']) ?>"
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
                    value="<?= htmlspecialchars($usuario['usuario']) ?>"
                    required
                >

            </div>

            <!-- CONTRASEÑA -->

            <div class="form-group">

                <label class="form-label">

                    <i class="fa-solid fa-lock"></i>

                    Nueva contraseña

                </label>

                <input
                    type="password"
                    name="password"
                    class="form-input"
                    placeholder="Dejar vacío para conservar la actual"
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

                    <?php foreach($roles as $rol): ?>

                        <option
                            value="<?= (int)$rol['id'] ?>"
                            <?= $usuario['rol_id'] == $rol['id']
                                ? 'selected'
                                : '' ?>
                        >

                            <?= htmlspecialchars($rol['nombre']) ?>

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
                name="editar_usuario"
                class="btn btn-primary"
            >

                Guardar Cambios

            </button>

        </div>

    </form>

</div>

<?php require_once "../../includes/footer.php"; ?>