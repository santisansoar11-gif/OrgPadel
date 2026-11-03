<?php
require_once __DIR__ . '/_comun.php';
$titulo_pagina = 'Administrar usuarios';
$errores = [];
$id_actualizar = 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validar_csrf();
    $id_actualizar = filter_var(campo_post('id_usuario'), FILTER_VALIDATE_INT);
    $clave = isset($_POST['contrasena']) && is_string($_POST['contrasena']) ? $_POST['contrasena'] : '';
    $repetir = isset($_POST['confirmar']) && is_string($_POST['confirmar']) ? $_POST['confirmar'] : '';
    if ($id_actualizar === false || $id_actualizar < 1) $errores[] = 'Seleccioná un usuario válido.';
    if (strlen($clave) < 8 || strlen($clave) > 72) $errores[] = 'La nueva contraseña debe tener entre 8 y 72 caracteres.';
    if ($clave !== $repetir) $errores[] = 'Las contraseñas no coinciden.';
    if (!$errores) {
        try {
            $stmt = $pdo->prepare('UPDATE usuarios SET password_hash = :hash WHERE id_usuario = :id');
            $stmt->execute(['hash' => password_hash($clave, PASSWORD_BCRYPT), 'id' => (int) $id_actualizar]);
            if ($stmt->rowCount() > 0) {
                flash('ok', 'Contraseña actualizada en MySQL. Cerrá sesión e ingresá con la contraseña nueva.');
                redirigir('usuarios.php');
            }
            $errores[] = 'Ese usuario no existe.';
        } catch (Throwable $e) {
            error_log('Restablecimiento admin: ' . $e->getMessage());
            $errores[] = 'No se pudo actualizar la contraseña. Revisá MySQL.';
        }
    }
}
$stmt = $pdo->prepare('SELECT u.id_usuario, u.nombre_completo, u.email, u.created_at, r.nombre AS rol FROM usuarios u INNER JOIN roles r ON u.id_rol = r.id_rol ORDER BY u.id_usuario DESC');
$stmt->execute();
$usuarios = $stmt->fetchAll();
require_once __DIR__ . '/../includes/header.php';
?>
<main class="page-content admin-content">
    <p class="eyebrow">ADMINISTRACIÓN</p>
    <h1>Usuarios de la base de datos</h1>
    <p>Estas cuentas están guardadas en MySQL. Si alguien no puede iniciar sesión, el administrador puede asignarle una contraseña nueva. La anterior no se puede ver ni recuperar.</p>
    <?php if ($errores): ?><div class="card form-feedback--error" role="alert"><ul><?php foreach ($errores as $error): ?><li><?= e($error); ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <div class="table-scroll"><table class="data-table"><thead><tr><th>ID</th><th>Nombre</th><th>Email</th><th>Rol</th><th>Registrado</th><th>Acción</th></tr></thead><tbody>
        <?php foreach ($usuarios as $usuario): ?>
            <tr><td><?= e($usuario['id_usuario']); ?></td><td><?= e($usuario['nombre_completo']); ?></td><td><?= e($usuario['email']); ?></td><td><?= e($usuario['rol']); ?></td><td><?= e($usuario['created_at']); ?></td>
            <td><a href="usuarios.php?editar=<?= e($usuario['id_usuario']); ?>">Nueva contraseña</a></td></tr>
        <?php endforeach; ?>
    </tbody></table></div>
    <?php
    $id_editar = filter_var(campo_get('editar'), FILTER_VALIDATE_INT);
    $seleccionado = null;
    if ($id_editar && $id_editar > 0) {
        foreach ($usuarios as $u) {
            if ((int) $u['id_usuario'] === $id_editar) { $seleccionado = $u; break; }
        }
    }
    ?>
    <?php if ($seleccionado): ?>
        <div class="card"><h2>Asignar nueva contraseña</h2>
            <p>Cuenta: <strong><?= e($seleccionado['email']); ?></strong></p>
            <form method="post" action="usuarios.php">
                <?= csrf_field(); ?>
                <input type="hidden" name="id_usuario" value="<?= e($seleccionado['id_usuario']); ?>">
                <label for="contrasena">Contraseña nueva</label>
                <input type="password" id="contrasena" name="contrasena" minlength="8" maxlength="72" autocomplete="new-password" required>
                <label for="confirmar">Repetir contraseña</label>
                <input type="password" id="confirmar" name="confirmar" minlength="8" maxlength="72" autocomplete="new-password" required>
                <button type="submit" class="btn">Guardar contraseña nueva</button>
            </form>
        </div>
    <?php endif; ?>
    <p><a href="index.php">Volver a administrar torneos</a></p>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
