<footer class="site-footer">
    <p><strong><?= e($nombre_sitio); ?></strong></p>
    <p>Contacto: <?= e($email_soporte); ?></p>
    <p>&copy; <?= date('Y'); ?> <?= e($nombre_sitio); ?>.</p>
</footer>
<script src="<?= e($base_path ?? ''); ?>js/main.js" defer></script>
</body>
</html>
