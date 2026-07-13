<?php

declare(strict_types=1);

require_once __DIR__ . '/SessionService.php';
require_once __DIR__ . '/usuarioService.php';

/* ==========================================================
   AUTH SERVICE
========================================================== */

/**
 * Valida las credenciales del usuario.
 *
 * @throws Exception
 */
function autenticarUsuario(
    PDO $pdo,
    string $credencial,
    string $password
): array {

    $usuario = obtenerUsuarioPorCredencial($pdo, $credencial);

    if (!$usuario || !password_verify($password, $usuario['password'])) {
        throw new Exception('Usuario o contraseña incorrectos.');
    }

    if (!(bool) $usuario['activo']) {
        throw new Exception('La cuenta se encuentra deshabilitada.');
    }

    return $usuario;
}

/**
 * Inicia sesión.
 */
function loginUsuario(
    PDO $pdo,
    string $credencial,
    string $password
): array {

    $usuario = autenticarUsuario($pdo, $credencial, $password);

    iniciarSesionUsuario($usuario);

    return $usuario;
}

/**
 * Cierra la sesión actual.
 */
function logoutUsuario(): void
{
    cerrarSesion();
}

/* ==========================================================
   RECUPERACIÓN DE CONTRASEÑA
========================================================== */

/**
 * Genera un token seguro de recuperación.
 */
function generarTokenRecuperacion(): string
{
    return bin2hex(random_bytes(32));
}

/**
 * Guarda el token de recuperación.
 */
function guardarTokenRecuperacion(
    PDO $pdo,
    int $usuarioId,
    string $token
): void {

    // Elimina tokens anteriores del usuario.
    eliminarTokensUsuario($pdo, $usuarioId);

    $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

    $stmt = $pdo->prepare("
        INSERT INTO password_resets (
            usuario_id,
            token,
            expires_at
        ) VALUES (
            :usuario_id,
            :token,
            :expires_at
        )
    ");

    $stmt->execute([
        ':usuario_id' => $usuarioId,
        ':token'      => $token,
        ':expires_at' => $expiresAt,
    ]);
}

/**
 * Valida un token de recuperación.
 */
function validarTokenRecuperacion(
    PDO $pdo,
    string $token
): array|false {

    $stmt = $pdo->prepare("
        SELECT
            pr.id,
            pr.usuario_id,
            pr.token,
            pr.expires_at,
            u.id,
            u.nombre,
            u.usuario,
            u.correo,
            u.password,
            u.rol_id,
            u.activo
        FROM password_resets pr
        INNER JOIN usuarios u
            ON u.id = pr.usuario_id
        WHERE pr.token = :token
        LIMIT 1
    ");

    $stmt->execute([
        ':token' => $token,
    ]);

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        return false;
    }

    if (strtotime($usuario['expires_at']) < time()) {
        return false;
    }

    if (!(bool) $usuario['activo']) {
        return false;
    }

    return $usuario;
}

/**
 * Elimina un token de recuperación.
 */
function eliminarTokenRecuperacion(
    PDO $pdo,
    string $token
): void {

    $stmt = $pdo->prepare("
        DELETE FROM password_resets
        WHERE token = :token
    ");

    $stmt->execute([
        ':token' => $token,
    ]);
}

/**
 * Elimina todos los tokens de un usuario.
 */
function eliminarTokensUsuario(
    PDO $pdo,
    int $usuarioId
): void {

    $stmt = $pdo->prepare("
        DELETE FROM password_resets
        WHERE usuario_id = :usuario_id
    ");

    $stmt->execute([
        ':usuario_id' => $usuarioId,
    ]);
}

/**
 * Elimina todos los tokens expirados.
 */
function limpiarTokensExpirados(PDO $pdo): void
{
    $stmt = $pdo->prepare("
        DELETE FROM password_resets
        WHERE expires_at <= NOW()
    ");

    $stmt->execute();
}
