<?php
// Los enlaces funcionan tanto desde raíz como desde /admin/.
$actual = basename($_SERVER['SCRIPT_NAME'] ?? '');
$links = [
    'index.php' => 'Inicio', 'torneos-de-padel.php' => 'Torneos',
    'productos.php' => 'Productos', 'canchas.php' => 'Canchas',
    'cursos-de-padel.php' => 'Cursos', 'reglas-del-padel.php' => 'Reglas',
    'blog.php' => 'Blog', 'preguntas-frecuentes.php' => 'Preguntas',
    'acerca-de-nosotros.php' => 'Nosotros', 'cv.php' => 'Currículum', 'contacto.php' => 'Contacto',
];
?>
<nav class="site-nav" aria-label="Navegación principal">
    <div class="site-nav__inner">
        <ul>
            <?php foreach ($links as $archivo => $etiqueta): ?>
                <li><a href="<?= e($base_path . $archivo); ?>" class="<?= $actual === $archivo && $base_path === '' ? 'active' : ''; ?>"><?= e($etiqueta); ?></a></li>
            <?php endforeach; ?>
            <?php if (usuario_actual()): ?>
                <li><a href="<?= e($base_path); ?>mis-inscripciones.php">Mis inscripciones</a></li>
                <?php if (es_admin()): ?><li><a href="<?= e($base_path); ?>admin/index.php" class="nav-admin">Administrar</a></li><?php endif; ?>
                <li>
                    <form method="post" action="<?= e($base_path); ?>logout.php" class="nav-logout">
                        <?= csrf_field(); ?>
                        <button type="submit">Salir</button>
                    </form>
                </li>
            <?php else: ?>
                <li><a href="<?= e($base_path); ?>login.php">Ingresar</a></li>
                <li><a href="<?= e($base_path); ?>registro.php">Registrarme</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
