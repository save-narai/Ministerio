<?php
session_start();

require_once "../config/conexion.php";

// Verificar que venga por POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../index.php");
    exit();
}

$usuario = $_POST["usuario"] ?? "";
$password = $_POST["password"] ?? "";

// Buscar usuario con su rol
$stmt = $pdo->prepare("
    SELECT u.*, r.nombre AS rol_nombre
    FROM usuarios u
    INNER JOIN roles r ON u.rol_id = r.id
    WHERE u.usuario = :usuario
    AND u.activo = 1
");

$stmt->execute(["usuario" => $usuario]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Verificar contraseña
if ($user && password_verify($password, $user["password"])) {

    session_regenerate_id(true);

    $_SESSION["user_id"] = $user["id"];
    $_SESSION["nombre"] = $user["nombre"];
    $_SESSION["rol"] = $user["rol_nombre"];

    header("Location: ../views/dashboard.php");
    exit();
}

// Si falla
$_SESSION["error"] = "Usuario o contraseña incorrectos";
header("Location: ../index.php");
exit();
