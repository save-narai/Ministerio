<?php
require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/permiso.php";

if (!tienePermiso('gestionar_jovenes')) {
    header("Location: ../dashboard.php");
    exit;
}
$extraCSS = '
<link rel="stylesheet" href="' . BASE_URL . '/assets/css/modules/jovenes/crear.css">
';


require_once __DIR__ . "/../../includes/header.php";
?>

<div class="card card-form">

    <!-- TOAST -->
    <div id="toast" class="toast"></div>

    <h2>Crear Joven</h2>

    <!-- ERROR -->
    <?php if(isset($_SESSION["error"])): ?>
    <script>
        document.addEventListener("DOMContentLoaded", ()=>{
            showToast(<?= json_encode($_SESSION["error"]) ?>, "error");
        });
    </script>
    <?php unset($_SESSION["error"]); endif; ?>

    <!-- SUCCESS -->
    <?php if(isset($_SESSION["success"])): ?>
    <script>
        document.addEventListener("DOMContentLoaded", ()=>{
            showToast(<?= json_encode($_SESSION["success"]) ?>, "success");
        });
    </script>
    <?php unset($_SESSION["success"]); endif; ?>

    <!-- 🔥 CONFIRMACIÓN DUPLICADOS -->
    <?php if(isset($_SESSION["confirmacion"])): 
        $conf = $_SESSION["confirmacion"];
    ?>
    <div class="toast show warning">
        <strong>⚠ Posible duplicado</strong><br>
        <?= htmlspecialchars($conf["mensaje"]) ?>

        <form method="POST"
              action="<?= BASE_URL ?>/controllers/jovenController.php">

            <?php foreach($conf["datos"] as $k=>$v): ?>
                <input type="hidden"
                       name="<?= $k ?>"
                       value="<?= htmlspecialchars($v) ?>">
            <?php endforeach; ?>

            <input type="hidden" name="confirmar" value="1">

            <div style="margin-top:10px;">
                <button class="btn-primary">Sí continuar</button>
                <a href="crear.php" class="btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
    <?php unset($_SESSION["confirmacion"]); endif; ?>

 <form id="formJoven"
      action="<?= BASE_URL ?>/controllers/jovenController.php"
      method="POST">

    <input type="hidden"
           name="csrf_token"
           value="<?= $_SESSION['csrf_token'] ?>">

    <!-- NOMBRE -->
    <div class="form-group">
        <label>Nombre Completo</label>
        <input type="text" name="nombre_completo" required>
    </div>

    <!-- FECHA / EDAD -->
    <div class="form-row">
        <div class="form-group">
            <label>Fecha nacimiento</label>
            <input type="date" name="fecha_nacimiento" id="fecha">
        </div>

        <div class="form-group">
            <label>Edad</label>
            <input type="number" name="edad_manual" id="edad">
        </div>
    </div>

    <!-- TEL / INGRESO -->
    <div class="form-row">
        <div class="form-group">
            <label>Teléfono</label>
            <input type="tel"
                   name="telefono"
                   id="telefono"
                   maxlength="10">

            <small id="telefonoError" class="telefono-error"></small>

            <div class="check-wrapper">
                <label class="check-custom">
                    <input type="checkbox" name="sinTelefono" id="sinTelefono">
                    <span class="checkmark"></span>
                    <span>No tiene teléfono</span>
                </label>
            </div>
        </div>

        <div class="form-group">
            <label>Fecha Ingreso</label>
            <input type="date"
                   name="fecha_ingreso"
                   value="<?= date('Y-m-d') ?>"
                   required>
        </div>
    </div>

    <!-- GENERO / ESTADO -->
    <div class="form-row">
        <div class="form-group">
            <label>Género</label>
            <select name="genero">
                <option value="">Seleccionar</option>
                <option value="M">Masculino</option>
                <option value="F">Femenino</option>
            </select>
        </div>

        <div class="form-group">
            <label>Estado Espiritual</label>
            <select name="estado_espiritual">
                <option value="">Seleccionar</option>
                <option value="NUEVO">Nuevo</option>
                <option value="CONGREGANTE">Congregante</option>
                <option value="DISCIPULADO">Discipulado</option>
                <option value="SERVIDOR">Servidor</option>
                <option value="LIDER">Líder</option>
            </select>
        </div>
    </div>

    <!-- SERVIDOR -->
    <div class="form-group">
        <label>¿Es servidor?</label>
        <select name="es_servidor">
            <option value="0">No</option>
            <option value="1">Sí</option>
        </select>
    </div>

    <!-- BOTONES -->
    <div class="btn-group">
        <a href="index.php" class="btn-secondary">Volver</a>
        <button type="submit"
                name="crear_joven"
                class="btn-primary">
            Guardar
        </button>
    </div>

</form>

<!-- JS -->
<script>
function showToast(msg, type="error"){
    const toast = document.getElementById("toast");
    toast.textContent = msg;
    toast.className = "toast show " + type;

    setTimeout(()=>{
        toast.classList.remove("show");
    }, 3000);
}

document.addEventListener("DOMContentLoaded", ()=>{

    const fecha = document.getElementById("fecha");
    const edad = document.getElementById("edad");
    const tel = document.getElementById("telefono");
    const check = document.getElementById("sinTelefono");
    const error = document.getElementById("telefonoError");

    // BLOQUEO FECHA / EDAD
    function sync(){
        edad.disabled = !!fecha.value;
        fecha.disabled = !!edad.value;
    }
    fecha.addEventListener("change", sync);
    edad.addEventListener("input", sync);

    // VALIDAR TEL
    function valido(n){
        return /^3\d{9}$/.test(n) && !/^(\d)\1+$/.test(n);
    }

    tel.addEventListener("input", ()=>{
        tel.value = tel.value.replace(/\D/g,'');

        if(tel.value.length === 10){
            if(valido(tel.value)){
                error.textContent = "Número válido";
                error.className = "telefono-error success";
                tel.classList.add("input-success");
            }else{
                error.textContent = "Número inválido";
                error.className = "telefono-error error";
                tel.classList.add("input-error");
            }
        }else{
            error.textContent = "";
            tel.classList.remove("input-error","input-success");
        }
    });

    // SIN TEL
    check.addEventListener("change", ()=>{
        tel.disabled = check.checked;
        if(check.checked){
            tel.value = "";
            error.textContent = "";
        }
    });

});
</script>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>