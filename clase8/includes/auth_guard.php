<?php
/**
 * TP8: Guardián de rutas reutilizable.
 * Se incluye AL PRINCIPIO de las páginas protegidas, antes de enviar HTML.
 */
require_once __DIR__ . '/funciones.php';

function auth_requerir_login(string $ruta_login = 'login.php'): void
{
    if (!headers_sent()) header('Cache-Control: private, no-store, max-age=0');
    if (!isset($_SESSION['usuario_id']) || usuario_actual() === null) {
        // Evita redirigir a URLs proporcionadas por el visitante.
        $permitidas = ['login.php', '../login.php'];
        if (!in_array($ruta_login, $permitidas, true)) $ruta_login = 'login.php';
        redirigir($ruta_login . '?error=acceso_denegado');
    }
}

function auth_requerir_rol(PDO $pdo, string $rol_requerido = 'admin', string $ruta_login = '../login.php'): void
{
    auth_requerir_login($ruta_login);
    if (!in_array($rol_requerido, ['admin', 'usuario'], true)) {
        throw new InvalidArgumentException('Rol solicitado no reconocido.');
    }
    if (($_SESSION['rol'] ?? '') !== $rol_requerido) {
        http_response_code(403);
        exit('Acceso denegado: no tenés permisos para esta sección.');
    }

    // Comprobación independiente contra MySQL: evita aceptar un rol modificado
    // en la BD después del inicio de sesión o una cuenta eliminada.
    $stmt = $pdo->prepare('SELECT r.nombre FROM usuarios u INNER JOIN roles r ON u.id_rol = r.id_rol WHERE u.id_usuario = :id LIMIT 1');
    $stmt->execute(['id' => (int) $_SESSION['usuario_id']]);
    $rol_bd = $stmt->fetchColumn();
    $equivalencia = ['admin' => 'Administrador', 'usuario' => 'Jugador'];
    if ($rol_bd !== $equivalencia[$rol_requerido]) {
        unset($_SESSION['usuario_id'], $_SESSION['usuario_nombre'], $_SESSION['rol']);
        http_response_code(403);
        exit('El usuario o sus permisos cambiaron. Volvé a iniciar sesión.');
    }
}
