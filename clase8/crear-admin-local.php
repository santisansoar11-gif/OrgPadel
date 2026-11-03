<?php
/**
 * Instalación inicial para XAMPP: crear el PRIMER administrador sin CMD.
 * Desactivado de forma automática cuando ya existe al menos un administrador.
 * Solo loopback/localhost; nunca accesible por la red.
 */
require_once __DIR__ . '/includes/funciones.php';
exigir_entorno_local();
require_once __DIR__ . '/config/conexion.php';

$titulo_pagina = 'Crear el primer administrador';
$descripcion_pagina = 'Configuración local de la primera cuenta administradora.';
$errores = [];
$datos = ['nombre' => '', 'email' => ''];

function administradores_existentes(PDO $pdo): int
{
    $consulta = $pdo->prepare('SELECT COUNT(*) FROM usuarios u INNER JOIN roles r ON r.id_rol = u.id_rol WHERE r.nombre = :rol');
    $consulta->execute(['rol' => 'Administrador']);
    return (int) $consulta->fetchColumn();
}

$hay_administrador = administradores_existentes($pdo) > 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validar_csrf();
    if ($hay_administrador) {
        $errores[] = 'Ya existe un administrador. Por seguridad no se pueden crear más desde este instalador.';
    } else {
        $datos['nombre'] = campo_post('nombre');
        $datos['email'] = strtolower(campo_post('email'));
        // Nunca recortar las contraseñas: los espacios también son caracteres.
        $clave = isset($_POST['contrasena']) && is_string($_POST['contrasena']) ? $_POST['contrasena'] : '';
        $repetir = isset($_POST['confirmar']) && is_string($_POST['confirmar']) ? $_POST['confirmar'] : '';
        if (mb_strlen($datos['nombre']) < 3 || mb_strlen($datos['nombre']) > 100) $errores[] = 'Escribí un nombre de entre 3 y 100 caracteres.';
        if (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL) || strlen($datos['email']) > 150) $errores[] = 'Escribí un correo válido.';
        if (strlen($clave) < 8 || strlen($clave) > 72) $errores[] = 'La contraseña debe tener entre 8 y 72 caracteres.';
        if ($clave !== $repetir) $errores[] = 'Las dos contraseñas no coinciden.';
        if (!$errores) {
            try {
                $pdo->beginTransaction();
                $query = $pdo->prepare('SELECT id_rol FROM roles WHERE nombre = :nombre LIMIT 1');
                $query->execute(['nombre' => 'Administrador']);
                $id_rol = $query->fetchColumn();
                if (!$id_rol) {
                    $query = $pdo->prepare('INSERT INTO roles (nombre, descripcion) VALUES (:nombre, :descripcion)');
                    $query->execute(['nombre' => 'Administrador', 'descripcion' => 'Gestiona torneos e inscripciones']);
                    $id_rol = (int) $pdo->lastInsertId();
                }
                // Una nueva comprobación antes del INSERT para evitar registrar otro administrador.
                if (administradores_existentes($pdo) > 0) {
                    throw new RuntimeException('Ya se creó la cuenta administradora.');
                }
                $query = $pdo->prepare('INSERT INTO usuarios (id_rol, id_categoria, nombre_completo, email, password_hash) VALUES (:rol, NULL, :nombre, :email, :hash)');
                $query->execute([
                    'rol' => $id_rol, 'nombre' => $datos['nombre'], 'email' => $datos['email'],
                    'hash' => password_hash($clave, PASSWORD_BCRYPT),
                ]);
                $id_creado = (int) $pdo->lastInsertId();
                $query = $pdo->prepare('SELECT COUNT(*) FROM usuarios WHERE id_usuario = :id');
                $query->execute(['id' => $id_creado]);
                if ((int) $query->fetchColumn() !== 1) throw new RuntimeException('No se confirmó el registro nuevo.');
                $pdo->commit();
                $_SESSION = [];
                session_regenerate_id(true);
                flash('ok', '¡Administrador creado y guardado en MySQL! Iniciá sesión con el correo y contraseña que elegiste.');
                redirigir('login.php');
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                error_log('Creación de primer administrador: ' . $e->getMessage());
                $errores[] = (string) $e->getCode() === '23000'
                    ? 'Este correo ya existe como cuenta de jugador. Para el administrador elegí otro correo.'
                    : 'No se pudo guardar el administrador. Comprobá la conexión con MySQL.';
            }
        }
    }
}
require_once __DIR__ . '/includes/header.php';
?>
<main class="page-content auth-page">
    <p class="eyebrow">SOLO EN TU COMPUTADORA · XAMPP</p>
    <h1>Crear el primer administrador</h1>
    <?php if ($hay_administrador): ?>
        <div class="card"><p>Ya existe al menos un administrador. Este asistente quedó bloqueado automáticamente.</p>
        <p><a class="btn" href="login.php">Ir a iniciar sesión</a></p></div>
    <?php else: ?>
        <p>Este asistente reemplaza al comando de CMD. No modifica ni borra las cuentas que registraste antes. Para administrar torneos necesitás un correo distinto si ya te registraste como jugador.</p>
        <?php if ($errores): ?><div class="card form-feedback--error" role="alert"><strong>No se creó la cuenta:</strong><ul><?php foreach ($errores as $error): ?><li><?= e($error); ?></li><?php endforeach; ?></ul></div><?php endif; ?>
        <form method="post" action="crear-admin-local.php">
            <?= csrf_field(); ?>
            <label for="nombre">Nombre completo</label>
            <input type="text" id="nombre" name="nombre" maxlength="100" autocomplete="name" value="<?= e($datos['nombre']); ?>" required>
            <label for="email">Correo para el administrador</label>
            <input type="email" id="email" name="email" maxlength="150" autocomplete="email" value="<?= e($datos['email']); ?>" required>
            <label for="contrasena">Contraseña nueva (mínimo 8 caracteres)</label>
            <input type="password" id="contrasena" name="contrasena" minlength="8" maxlength="72" autocomplete="new-password" required>
            <label for="confirmar">Repetir contraseña</label>
            <input type="password" id="confirmar" name="confirmar" minlength="8" maxlength="72" autocomplete="new-password" required>
            <button type="submit" class="btn">Crear administrador y guardar en MySQL</button>
        </form>
    <?php endif; ?>
    <p>Cuando termines, podés borrar <code>crear-admin-local.php</code> de tu carpeta del sitio: el panel de administración seguirá funcionando.</p>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
