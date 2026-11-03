<?php
// Funciones compartidas de seguridad y sesión. Nunca se imprime HTML aquí.
// Los horarios se ingresan en la zona del proyecto (Salta, Argentina).
date_default_timezone_set('America/Argentina/Salta');
// Configuración de sesión antes de generar HTML o cabeceras.
if (session_status() !== PHP_SESSION_ACTIVE) {
    if (PHP_SAPI !== 'cli') {
        $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.use_trans_sid', '0');
        session_set_cookie_params([
            'lifetime' => 0,        // Se elimina al cerrar el navegador.
            'path' => '/',
            'secure' => $https,     // HTTPS en producción; HTTP en localhost XAMPP.
            'httponly' => true,     // JavaScript no puede leer la cookie.
            'samesite' => 'Lax',
        ]);
    }
    session_name('PTOSESSID');
    session_start();
}

/**
 * Guarda exactamente las variables solicitadas por el TP8.
 * Los roles de MySQL se normalizan: Administrador => admin; Jugador => usuario.
 */
function iniciar_sesion_usuario(int $id, string $nombre, string $rol_bd): void
{
    $rol = match ($rol_bd) {
        'Administrador' => 'admin',
        'Jugador' => 'usuario',
        default => throw new InvalidArgumentException('El rol del usuario no es válido.'),
    };
    // Evita el ataque de fijación de sesión.
    session_regenerate_id(true);
    $_SESSION = [];
    $_SESSION['usuario_id'] = $id;
    $_SESSION['usuario_nombre'] = $nombre;
    $_SESSION['rol'] = $rol;
    $_SESSION['inicio_sesion'] = time();
    $_SESSION['ultimo_acceso'] = time();
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/** Comprueba expiración por inactividad (30 min) y duración total (12 h). */
function sesion_vigente(): bool
{
    if (!isset($_SESSION['usuario_id'], $_SESSION['usuario_nombre'], $_SESSION['rol'])) return false;
    if (!is_int($_SESSION['usuario_id']) || $_SESSION['usuario_id'] < 1) return false;
    if (!is_string($_SESSION['usuario_nombre']) || !in_array($_SESSION['rol'], ['admin', 'usuario'], true)) return false;
    $ahora = time();
    $ultimo = (int) ($_SESSION['ultimo_acceso'] ?? 0);
    $inicio = (int) ($_SESSION['inicio_sesion'] ?? 0);
    if ($ultimo <= 0 || $inicio <= 0 || $ahora - $ultimo > 1800 || $ahora - $inicio > 43200) {
        unset($_SESSION['usuario_id'], $_SESSION['usuario_nombre'], $_SESSION['rol'], $_SESSION['inicio_sesion'], $_SESSION['ultimo_acceso']);
        return false;
    }
    $_SESSION['ultimo_acceso'] = $ahora;
    return true;
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
    if (!sesion_vigente()) return null;
    return [
        'id' => $_SESSION['usuario_id'],
        'nombre' => $_SESSION['usuario_nombre'],
        'rol' => $_SESSION['rol'],
    ];
}

function es_admin(): bool
{
    $usuario = usuario_actual();
    return $usuario !== null && $usuario['rol'] === 'admin';
}

// Compatibilidad con el código TP7 que ya llamaba estas funciones.
function exigir_login(string $ruta_login = 'login.php'): void
{
    require_once __DIR__ . '/auth_guard.php';
    auth_requerir_login($ruta_login);
}

function exigir_admin(PDO $pdo): void
{
    require_once __DIR__ . '/auth_guard.php';
    auth_requerir_rol($pdo, 'admin', '../login.php');
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

/**
 * Herramientas de instalación y diagnóstico: solo para XAMPP abierto en
 * localhost, nunca desde otra computadora de la red o un servidor público.
 */
function es_entorno_local(): bool
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
    if (str_starts_with($host, '[')) {
        $host = substr($host, 0, strpos($host, ']') !== false ? strpos($host, ']') + 1 : strlen($host));
    } else {
        $host = explode(':', $host, 2)[0];
    }
    // No depende solo de APP_ENV porque es fácil olvidar su configuración.
    return PHP_SAPI !== 'cli'
        && in_array($ip, ['127.0.0.1', '::1'], true)
        && in_array($host, ['localhost', '127.0.0.1', '[::1]'], true);
}

function exigir_entorno_local(): void
{
    if (!es_entorno_local()) {
        http_response_code(403);
        exit('Esta herramienta solo funciona desde localhost, en tu propia computadora.');
    }
}
