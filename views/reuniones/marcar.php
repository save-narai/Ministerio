<?php
require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/permiso.php";
require_once __DIR__ . "/../../config/conexion.php";

if (!tienePermiso('gestionar_reuniones')) {
    header("Location: ../dashboard.php");
    exit;
}

$reunion_id = $_GET["reunion_id"] ?? null;
if (!$reunion_id) die("Reunión inválida");

/* REUNIÓN */
$stmt = $pdo->prepare("SELECT * FROM reuniones WHERE id = ?");
$stmt->execute([$reunion_id]);
$reunion = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$reunion) die("No existe");

/* JÓVENES */
$jovenes = $pdo->query("
    SELECT id, nombre_completo,
    TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) AS edad
    FROM jovenes
    WHERE estado_actividad = 'ELIMINADO'
    ORDER BY nombre_completo ASC
")->fetchAll(PDO::FETCH_ASSOC);

/* CSS */
$extraCSS = '
<link rel="stylesheet" href="' . BASE_URL . '/assets/css/modules/reuniones/marcar.css">
';

require_once __DIR__ . "/../../includes/header.php";
?>

<div class="marcar">

    <!-- HEADER -->
    <div class="marcar__header">
        <h1>Marcar asistencia</h1>
        <div class="marcar__fecha">
            <?= date("d/m/Y", strtotime($reunion["fecha"])) ?>
        </div>
    </div>

    <!-- FILTROS -->
    <div class="marcar__filtros">
        <button type="button" class="active" onclick="filtrar('todos', this)">Todos</button>
        <button type="button" onclick="filtrar('teen', this)">Teenagers</button>
        <button type="button" onclick="filtrar('remanente', this)">Remanente</button>
    </div>

    <!-- BUSCADOR -->
    <input type="text" id="buscador" placeholder="Buscar joven..." class="buscador">

    <form method="POST" action="<?= BASE_URL ?>/controllers/asistenciaController.php">
        <input type="hidden" name="reunion_id" value="<?= $reunion_id ?>">

        <!-- ACCIONES -->
        <div class="acciones-masivas">
            <button type="button" onclick="checkAll()">Todos ✔</button>
            <button type="button" onclick="uncheckAll()">Ninguno ✖</button>
        </div>

        <!-- LISTA -->
        <div class="lista">
        <?php foreach($jovenes as $j): 
            $grupoEdad = ($j["edad"] >= 15 && $j["edad"] <= 17)
                ? "teen"
                : "remanente";
        ?>

        <div class="fila" data-edad="<?= $grupoEdad ?>">

            <!-- NOMBRE -->
            <div class="info">
                <strong><?= htmlspecialchars($j["nombre_completo"]) ?></strong>
            </div>

            <div class="checks-grid">

    <!-- ASISTENCIA -->
    <label title="Asistencia">
        <input type="checkbox" name="asistencia[]" value="<?= $j["id"] ?>">
        <span>✔</span>
    </label>

    <!-- CONEXIÓN -->
    <label title="Conexión">
        <input type="checkbox" name="conexion[]" value="<?= $j["id"] ?>">
        <span>C</span>
    </label>

    <!-- DISCIPULADO -->
    <label title="Discipulado">
        <input type="checkbox" name="discipulado[]" value="<?= $j["id"] ?>">
        <span>D</span>
    </label>

    <!-- PRIMERA VEZ -->
    <label title="Primera vez">
        <input type="checkbox" name="primera_vez[]" value="<?= $j["id"] ?>">
        <span>P</span>
    </label>

</div>

            <!-- GRUPO OCULTO -->
            <input type="hidden"
                   name="grupo_edad[<?= $j["id"] ?>]"
                   value="<?= $grupoEdad ?>">

        </div>

        <?php endforeach; ?>
        </div>

        <!-- BOTONES -->
        <div class="form-actions">
            <button type="submit" name="guardar_asistencia" class="btn-primary">
                Guardar asistencia
            </button>
            <a href="index.php" class="btn-secondary">
                Cancelar
            </a>
        </div>

    </form>
</div>

<!-- SCRIPT -->
<script>

/* BUSCADOR */
document.getElementById("buscador").addEventListener("keyup", function() {
    let filtro = this.value.toLowerCase();
    document.querySelectorAll(".fila").forEach(f => {
        f.style.display = f.innerText.toLowerCase().includes(filtro) ? "" : "none";
    });
});

/* CHECK ALL */
function checkAll(){
    document.querySelectorAll('input[name="asistencia[]"]').forEach(c => c.checked = true);
}

function uncheckAll(){
    document.querySelectorAll('input[type="checkbox"]').forEach(c => c.checked = false);
}

/* FILTROS */
function filtrar(tipo, btn){
    let filas = document.querySelectorAll('.fila');

    filas.forEach(fila => {
        let edad = fila.dataset.edad;

        if(tipo === 'todos'){
            fila.style.display = 'flex';
        } else {
            fila.style.display = (edad === tipo) ? 'flex' : 'none';
        }
    });

    document.querySelectorAll('.marcar__filtros button')
        .forEach(b => b.classList.remove('active'));

    btn.classList.add('active');
}

</script>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>