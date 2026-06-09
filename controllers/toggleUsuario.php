<?php
require_once "../config/conexion.php";
require_once "../middleware/auth.php";
require_once "../middleware/permiso.php";



if (!tienePermiso('gestionar_usuarios')) {
    die("Acceso denegado.");
}

$id = $_GET["id"] ?? null;

if (!$id) {
    header("Location: ../views/usuarios/index.php");
    exit();
}

// Obtener estado actual
$stmt = $pdo->prepare("SELECT activo FROM usuarios WHERE id = :id");
$stmt->execute(["id" => $id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    header("Location: ../views/usuarios/index.php");
    exit();
}

// Cambiar estado
$nuevoEstado = $usuario["activo"] ? 0 : 1;

$stmt = $pdo->prepare("
    UPDATE usuarios
    SET activo = :estado
    WHERE id = :id
");

$stmt->execute([
    "estado" => $nuevoEstado,
    "id" => $id
]);

header("Location: ../views/usuarios/index.php");
exit();
