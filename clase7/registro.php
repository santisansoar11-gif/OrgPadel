<?php
require_once __DIR__ . '/includes/funciones.php';
require_once __DIR__ . '/config/conexion.php';
$titulo_pagina = 'Registro de jugadores';
$descripcion_pagina = 'Creá tu cuenta para inscribirte a torneos de pádel.';
if (usuario_actual()) redirigir('torneos-de-padel.php');

$stmt = $pdo->prepare('SELECT id_categoria, codigo, descripcion FROM categorias ORDER BY id_categoria');
$stmt->execute();
$categorias = $stmt->fetchAll();
$datos = ['nombre' => '', 'email' => '', 'telefono' => '', 'genero' => '', 'categoria' => ''];
$errores = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validar_csrf();
    foreach (array_keys($datos) as $clave) $datos[$clave] = campo_post($clave);
    $password = campo_post('contrasena');
    if (mb_strlen($datos['nombre']) < 3 || mb_strlen($datos['nombre']) > 100) $errores[] = 'Escribí tu nombre completo (3 a 100 caracteres).';
    if (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($datos['email']) > 150) $errores[] = 'Escribí un correo válido.';
    if (mb_strlen($datos['telefono']) > 30) $errores[] = 'El teléfono es demasiado largo.';
    if (!in_array($datos['genero'], ['Masculino', 'Femenino', 'Otro', 'Prefiero no decir'], true)) $errores[] = 'Elegí una opción en género.';
    $id_cat = filter_var($datos['categoria'], FILTER_VALIDATE_INT);
    if ($id_cat === false || !in_array((int) $id_cat, array_column($categorias, 'id_categoria'), false)) $errores[] = 'Seleccioná una categoría existente.';
    if (strlen($password) < 8 || strlen($password) > 72) $errores[] = 'La contraseña debe tener entre 8 y 72 caracteres.';

    if (!$errores) {
        try {
            $stmt = $pdo->prepare('INSERT INTO usuarios
                (id_rol, id_categoria, nombre_completo, email, password_hash, telefono, genero)
                VALUES ((SELECT id_rol FROM roles WHERE nombre = :rol), :categoria, :nombre, :email, :clave, :telefono, :genero)');
            $stmt->execute([
                'rol' => 'Jugador', 'categoria' => (int) $id_cat,
                'nombre' => $datos['nombre'], 'email' => strtolower($datos['email']),
                'clave' => password_hash($password, PASSWORD_DEFAULT),
                'telefono' => $datos['telefono'] ?: null, 'genero' => $datos['genero'],
            ]);
            session_regenerate_id(true);
            $_SESSION['usuario'] = ['id' => (int) $pdo->lastInsertId(), 'nombre' => $datos['nombre'], 'rol' => 'Jugador'];
            flash('ok', 'Cuenta creada correctamente. Ya podés inscribirte a un torneo.');
            redirigir('torneos-de-padel.php');
        } catch (PDOException $e) {
            error_log('Registro fallido: ' . $e->getMessage());
            $errores[] = $e->getCode() === '23000' ? 'Este correo ya está registrado.' : 'No se pudo crear la cuenta. Revisá que ejecutaste el TP6.';
        }
    }
}
require_once __DIR__ . '/includes/header.php';
?>
<main class="page-content auth-page">
    <p class="eyebrow">COMUNIDAD DE PÁDEL</p>
    <h1>Crear cuenta de jugador</h1>
    <h2>Registrate una sola vez y elegí el torneo donde querés competir.</h2>
    <?php if ($errores): ?><div class="card form-feedback--error" role="alert"><strong>Revisá los datos:</strong><ul><?php foreach ($errores as $error): ?><li><?= e($error); ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <form action="registro.php" method="post" data-validate data-resumen novalidate>
        <?= csrf_field(); ?>
        <label for="nombre">Nombre completo:</label>
        <input type="text" id="nombre" name="nombre" maxlength="100" required placeholder="Juan Pérez" value="<?= e($datos['nombre']); ?>">
        <span class="form-error" id="error-nombre"></span>
        <label for="email">Correo electrónico:</label>
        <input type="email" id="email" name="email" maxlength="150" required placeholder="nombre@ejemplo.com" value="<?= e($datos['email']); ?>">
        <span class="form-error" id="error-email"></span>
        <label for="telefono">Teléfono (opcional):</label>
        <input type="tel" id="telefono" name="telefono" maxlength="30" value="<?= e($datos['telefono']); ?>">
        <span class="form-error" id="error-telefono"></span>
        <label for="genero">Género:</label>
        <select id="genero" name="genero" required>
            <option value="">Seleccioná una opción</option>
            <?php foreach (['Masculino', 'Femenino', 'Otro', 'Prefiero no decir'] as $op): ?>
                <option value="<?= e($op); ?>" <?= $datos['genero'] === $op ? 'selected' : ''; ?>><?= e($op); ?></option>
            <?php endforeach; ?>
        </select>
        <span class="form-error" id="error-genero"></span>
        <label for="categoria">Categoría de juego:</label>
        <select id="categoria" name="categoria" required>
            <option value="">Seleccioná tu categoría</option>
            <?php foreach ($categorias as $c): ?><option value="<?= e($c['id_categoria']); ?>" <?= $datos['categoria'] === (string) $c['id_categoria'] ? 'selected' : ''; ?>><?= e($c['codigo']); ?> — <?= e($c['descripcion']); ?></option><?php endforeach; ?>
        </select>
        <span class="form-error" id="error-categoria"></span>
        <label for="contrasena">Contraseña (mínimo 8 caracteres):</label>
        <input type="password" id="contrasena" name="contrasena" minlength="8" maxlength="72" autocomplete="new-password" required>
        <span class="form-error" id="error-contrasena"></span>
        <input type="submit" value="Crear cuenta">
    </form>
    <p>¿Ya tenés cuenta? <a href="login.php">Iniciá sesión</a>.</p>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
