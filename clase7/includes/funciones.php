<?php
// Funciones compartidas de seguridad y sesión. Nunca se imprime HTML aquí.
// Los horarios se ingresan en la zona del proyecto (Salta, Argentina).
date_default_timezone_set('America/Argentina/Salta');
if (session_status() !== PHP_SESSION_ACTIVE) {
    if (PHP_SAPI !== 'cli') {
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        session_set_cookie_params([
            'httponly' => true,
            'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'samesite' => 'Lax',
            'path' => '/',
        ]);
    }
    session_name('PTOSESSID');
    session_start();
}

function e($valor): string
{
    return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function campo_post(string $clave): string
{
    $valor = $_POST[$clave] ?? '';
    return is_string($valor) ? trim($valor) : '';
}

function campo_get(string $clave): string
{
    $valor = $_GET[$clave] ?? '';
    return is_string($valor) ? trim($valor) : '';
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function validar_csrf(): void
{
    $recibido = $_POST['csrf_token'] ?? null;
    if (!is_string($recibido) || !hash_equals(csrf_token(), $recibido)) {
        http_response_code(403);
        exit('Solicitud rechazada: token de seguridad inválido. Volvé al formulario y probá otra vez.');
    }
}

function redirigir(string $ruta): void
{
    header('Location: ' . $ruta, true, 303);
    exit;
}

function flash(string $tipo, string $mensaje): void
{
    $_SESSION['flash'] = ['tipo' => $tipo, 'mensaje' => $mensaje];
}

function obtener_flash(): ?array
{
    $mensaje = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return is_array($mensaje) ? $mensaje : null;
}

function usuario_actual(): ?array
{
    return isset($_SESSION['usuario']) && is_array($_SESSION['usuario']) ? $_SESSION['usuario'] : null;
}

function es_admin(): bool
{
    $usuario = usuario_actual();
    return $usuario !== null && ($usuario['rol'] ?? '') === 'Administrador';
}

function exigir_login(string $ruta_login = 'login.php'): void
{
    if (usuario_actual() === null) {
        flash('error', 'Iniciá sesión para continuar.');
        redirigir($ruta_login);
    }
}

function exigir_admin(PDO $pdo): void
{
    $usuario = usuario_actual();
    if (!$usuario) {
        flash('error', 'Iniciá sesión con una cuenta administradora.');
        redirigir('../login.php');
    }
    // Se vuelve a verificar el rol en MySQL en cada operación de administración.
    $stmt = $pdo->prepare('SELECT r.nombre FROM usuarios u INNER JOIN roles r ON r.id_rol = u.id_rol WHERE u.id_usuario = :id');
    $stmt->execute(['id' => (int) $usuario['id']]);
    if ($stmt->fetchColumn() !== 'Administrador') {
        http_response_code(403);
        exit('Acceso restringido a administradores.');
    }
}

function fecha_para_sql(string $valor): ?string
{
    // Formato enviado por input datetime-local: YYYY-MM-DDTHH:MM.
    $fecha = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $valor);
    return $fecha instanceof DateTimeImmutable && $fecha->format('Y-m-d\TH:i') === $valor
        ? $fecha->format('Y-m-d H:i:s')
        : null;
}

function fecha_visible(?string $fecha): string
{
    if (!$fecha) return 'Sin fecha';
    try { return (new DateTimeImmutable($fecha))->format('d/m/Y H:i'); }
    catch (Exception $e) { return $fecha; }
}

function opciones_torneo(): array
{
    return ['programado' => 'Inscripciones abiertas', 'en_curso' => 'En curso', 'finalizado' => 'Finalizado', 'cancelado' => 'Cancelado'];
}
