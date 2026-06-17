<?php

require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/permiso.php";
require_once __DIR__ . "/../../config/conexion.php";


if (!tienePermiso('gestionar_jovenes')) {

    header("Location: ../dashboard.php");
    exit();

}


$id = isset($_GET["id"]) 
    ? (int)$_GET["id"] 
    : 0;


if ($id <= 0) {

    header("Location: index.php");
    exit();

}


$stmt = $pdo->prepare("
    SELECT *
    FROM jovenes
    WHERE id = :id
");


$stmt->execute([
    "id"=>$id
]);


$joven = $stmt->fetch(PDO::FETCH_ASSOC);



if (!$joven) {

    header("Location:index.php");
    exit();

}



$extraCSS = '

<link rel="stylesheet" href="' . BASE_URL . '/assets/css/modules/jovenes/editar.css">

';


require_once __DIR__ . "/../../includes/header.php";

?>


<div class="form-card">


<div id="toast" class="toast"></div>



<div class="form-header">


<div class="form-header-icon">

<i class="fa-solid fa-user-pen"></i>

</div>



<div class="form-header-content">


<h1 class="form-title">

Editar Joven

</h1>


<p class="form-subtitle">

Actualiza la información del joven dentro del sistema.

</p>


</div>


</div>




<form

class="form"

action="<?= BASE_URL ?>/controllers/jovenController.php"

method="POST"

>



<input

type="hidden"

name="csrf_token"

value="<?= $_SESSION['csrf_token'] ?>"

>


<input

type="hidden"

name="id"

value="<?= $joven['id'] ?>"

>



<div class="form-grid">





<!-- NOMBRE -->

<div class="form-group form-group-full">


<label class="form-label">

Nombre Completo

</label>


<input

class="form-input"

type="text"

name="nombre_completo"

required

value="<?= htmlspecialchars($joven['nombre_completo']) ?>"

>


</div>





<!-- FECHA -->

<div class="form-group">


<label class="form-label">

Fecha nacimiento

</label>


<input

class="form-input"

type="date"

name="fecha_nacimiento"

id="fecha"

value="<?= $joven['fecha_nacimiento'] ?>"

>


</div>






<!-- EDAD -->

<div class="form-group">


<label class="form-label">

Edad

</label>


<input

class="form-input"

type="number"

name="edad_manual"

id="edad"

min="1"

max="100"

value="<?= $joven['edad_manual'] ?>"

>


</div>







<!-- TELEFONO -->


<div class="form-group">


<label class="form-label">

Teléfono

</label>



<input

class="form-input"

type="tel"

name="telefono"

id="telefono"

maxlength="10"

value="<?= htmlspecialchars($joven['telefono'] ?? '') ?>"

<?= empty($joven['telefono']) ? 'disabled' : '' ?>

>




<small

id="telefonoError"

class="telefono-error">

</small>





<div class="check-wrapper">


<label class="check-custom">


<input

type="checkbox"

id="sinTelefono"

name="sinTelefono"

<?= empty($joven['telefono']) ? "checked":"" ?>

>


<span class="checkmark"></span>


<span>

No tiene teléfono

</span>


</label>


</div>



</div>






<!-- INGRESO -->


<div class="form-group">


<label class="form-label">

Fecha ingreso

</label>


<input

class="form-input"

type="date"

name="fecha_ingreso"

required

value="<?= $joven['fecha_ingreso'] ?>"

>


</div>








<!-- GENERO -->


<div class="form-group">


<label class="form-label">

Género

</label>


<select

class="form-select"

name="genero"

>


<option value="">

Seleccionar

</option>


<option

value="M"

<?= $joven['genero']=="M"?'selected':'' ?>

>

Masculino

</option>



<option

value="F"

<?= $joven['genero']=="F"?'selected':'' ?>

>

Femenino

</option>



</select>


</div>







<!-- ESTADO -->


<div class="form-group">


<label class="form-label">

Estado espiritual

</label>


<select

class="form-select"

name="estado_espiritual"

>


<option value="">

Seleccionar

</option>


<?php


$estados=[

"NUEVO",

"CONGREGANTE",

"DISCIPULADO",

"SERVIDOR",

"LIDER"

];


foreach($estados as $estado):

?>


<option

value="<?= $estado ?>"

<?= $joven['estado_espiritual']==$estado?'selected':'' ?>

>


<?= $estado ?>


</option>


<?php endforeach; ?>


</select>


</div>







<!-- SERVIDOR -->


<div class="form-group form-group-full">


<label class="form-label">

¿Es servidor?

</label>



<select

class="form-select"

name="es_servidor"

>


<option

value="0"

<?= $joven['es_servidor']==0?'selected':'' ?>

>

No

</option>



<option

value="1"

<?= $joven['es_servidor']==1?'selected':'' ?>

>

Sí

</option>



</select>



</div>







<!-- OBSERVACIONES -->


<div class="form-group form-group-full">


<label class="form-label">

Observaciones

</label>



<textarea

class="form-textarea"

name="observaciones"

><?= htmlspecialchars($joven['observaciones'] ?? '') ?></textarea>



</div>



</div>








<div class="form-actions">



<a

href="<?= BASE_URL ?>/views/jovenes/index.php"

class="btn btn-back"

>


<i class="fa-solid fa-arrow-left"></i>

Volver


</a>




<button

type="submit"

name="editar_joven"

class="btn btn-primary"

>


Guardar cambios


</button>




</div>





</form>



</div>






<script>


function showToast(msg,type="error"){


const toast=document.getElementById("toast");


toast.textContent=msg;


toast.className="toast show "+type;



setTimeout(()=>{


toast.classList.remove("show");


},3000);


}




<?php if(isset($_SESSION["error"])): ?>


showToast(

<?= json_encode($_SESSION["error"]) ?>,

"error"

);


<?php unset($_SESSION["error"]); endif; ?>





<?php if(isset($_SESSION["success"])): ?>


showToast(

<?= json_encode($_SESSION["success"]) ?>,

"success"

);


<?php unset($_SESSION["success"]); endif; ?>






document.addEventListener("DOMContentLoaded",()=>{


const fecha=document.getElementById("fecha");

const edad=document.getElementById("edad");



function sync(){


edad.disabled=!!fecha.value;

fecha.disabled=!!edad.value;


}



fecha.addEventListener("change",sync);

edad.addEventListener("input",sync);


sync();


});



</script>



<script src="<?= BASE_URL ?>/assets/js/modules/jovenes/phone-validation.js"></script>



<?php require_once __DIR__ . "/../../includes/footer.php"; ?>