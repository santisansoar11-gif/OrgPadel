<?php
require_once __DIR__ . '/includes/funciones.php';
require_once __DIR__ . '/config/conexion.php';
$titulo_pagina = 'Iniciar sesión';
$email = '';
$error = '';
if (usuario_actual()) redirigir(es_admin() ? 'admin/index.php' : 'torneos-de-padel.php');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validar_csrf();
    $email = strtolower(campo_post('email'));
    $password = campo_post('contrasena');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        $error = 'Ingresá un correo y contraseña válidos.';
    } else {
        $stmt = $pdo->prepare('SELECT u.id_usuario, u.nombre_completo, u.password_hash, r.nombre AS rol
            FROM usuarios u INNER JOIN roles r ON r.id_rol = u.id_rol WHERE u.email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $usuario = $stmt->fetch();
        if ($usuario && password_verify($password, $usuario['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['usuario'] = ['id' => (int) $usuario['id_usuario'], 'nombre' => $usuario['nombre_completo'], 'rol' => $usuario['rol']];
            flash('ok', 'Bienvenido/a, ' . $usuario['nombre_completo'] . '.');
            redirigir($usuario['rol'] === 'Administrador' ? 'admin/index.php' : 'torneos-de-padel.php');
        } else {
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
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
