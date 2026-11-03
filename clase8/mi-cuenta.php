<?php
require_once __DIR__ . '/includes/auth_guard.php';
auth_requerir_login();
require_once __DIR__ . '/config/conexion.php';

$stmt = $pdo->prepare('SELECT u.nombre_completo, u.email, u.telefono, u.created_at,
    c.codigo AS categoria, r.nombre AS rol_bd
    FROM usuarios u
    INNER JOIN roles r ON r.id_rol = u.id_rol
    LEFT JOIN categorias c ON c.id_categoria = u.id_categoria
    WHERE u.id_usuario = :id LIMIT 1');
$stmt->execute(['id' => (int) $_SESSION['usuario_id']]);
$perfil = $stmt->fetch();
if (!$perfil) {
    unset($_SESSION['usuario_id'], $_SESSION['usuario_nombre'], $_SESSION['rol']);
    redirigir('login.php?error=acceso_denegado');
}
$titulo_pagina = 'Mi cuenta';
$descripcion_pagina = 'Datos de la cuenta, rol y estado de sesión.';
require_once __DIR__ . '/includes/header.php';
?>
<main class="page-content auth-page">
    <p class="eyebrow">ZONA PRIVADA · TP8</p>
    <h1>Mi cuenta</h1>
    <h2>Esta información solo aparece después de iniciar sesión.</h2>
    <div class="card">
        <p><strong>Nombre:</strong> <?= e($perfil['nombre_completo']); ?></p>
        <p><strong>Correo:</strong> <?= e($perfil['email']); ?></p>
        <p><strong>Tipo de cuenta:</strong> <?= e($perfil['rol_bd']); ?></p>
        <p><strong>Rol guardado en la sesión:</strong> <?= e($_SESSION['rol']); ?></p>
        <p><strong>Categoría de pádel:</strong> <?= e($perfil['categoria'] ?? 'No asignada'); ?></p>
        <p><strong>Cuenta creada:</strong> <?= e($perfil['created_at']); ?></p>
    </div>
    <div class="toolbar-actions">
        <a class="btn" href="mis-inscripciones.php">Mis inscripciones</a>
        <?php if (es_admin()): ?><a class="btn btn--secondary" href="admin/index.php">Administrar torneos</a><?php endif; ?>
    </div>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
