<?php
require_once __DIR__ . '/includes/funciones.php';
require_once __DIR__ . '/config/conexion.php';
$titulo_pagina = 'Iniciar sesión';
$email = '';
$error = '';
if (usuario_actual()) redirigir(es_admin() ? 'admin/index.php' : 'mis-inscripciones.php');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validar_csrf();
    $email = strtolower(campo_post('email')); 
    // La contraseña no se recorta: los espacios también son parte de ella.
    $password = isset($_POST['contrasena']) && is_string($_POST['contrasena']) ? $_POST['contrasena'] : '';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        $error = 'Ingresá un correo y contraseña válidos.';
    } else {
        $stmt = $pdo->prepare('SELECT u.id_usuario, u.nombre_completo, u.password_hash, r.nombre AS rol
            FROM usuarios u INNER JOIN roles r ON r.id_rol = u.id_rol WHERE u.email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $usuario = $stmt->fetch();
        if ($usuario && password_verify($password, (string) $usuario['password_hash'])) {
            iniciar_sesion_usuario((int) $usuario['id_usuario'], $usuario['nombre_completo'], $usuario['rol']);
            flash('ok', 'Bienvenido/a, ' . $usuario['nombre_completo'] . '.');
            redirigir($usuario['rol'] === 'Administrador' ? 'admin/index.php' : 'mis-inscripciones.php');
        } else {
            // Mensaje genérico: no revela si existe la cuenta.
            $error = 'Correo o contraseña incorrectos.';
        }
    }
}
require_once __DIR__ . '/includes/header.php';
?>
<main class="page-content auth-page">
    <p class="eyebrow">ACCESO SEGURO</p>
    <h1>Iniciar sesión</h1>
    <h2>Ingresá para inscribirte o administrar torneos.</h2>
    <?php if (campo_get('registrado') === '1'): ?><div class="card form-feedback--success" role="status">¡Cuenta creada! Iniciá sesión con el correo y contraseña que elegiste.</div><?php endif; ?>
    <?php if (campo_get('salida') === '1'): ?><div class="card form-feedback--success" role="status">Cerraste la sesión correctamente.</div><?php endif; ?>
    <?php if (campo_get('error') === 'acceso_denegado'): ?><div class="card form-feedback--error" role="alert">Iniciá sesión para acceder a esta página.</div><?php endif; ?>
    <?php if ($error): ?><div class="card form-feedback--error" role="alert"><?= e($error); ?></div><?php endif; ?>
    <form method="post" action="login.php">
        <?= csrf_field(); ?>
        <label for="email">Correo electrónico</label>
        <input type="email" id="email" name="email" autocomplete="email" value="<?= e($email); ?>" required>
        <label for="contrasena">Contraseña</label>
        <input type="password" id="contrasena" name="contrasena" autocomplete="current-password" required>
        <button type="submit" class="btn">Ingresar</button>
    </form>
    <p>¿Todavía no tenés cuenta? <a href="registro.php">Registrate</a>.</p>
    <?php if (es_entorno_local()): ?>
        <p>¿Necesitás crear el primer administrador? <a href="crear-admin-local.php">Hacelo desde el navegador, sin CMD</a>.</p>
    <?php endif; ?>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
