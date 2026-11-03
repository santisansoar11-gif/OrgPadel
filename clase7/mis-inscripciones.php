<?php
require_once __DIR__ . '/includes/funciones.php';
exigir_login();
require_once __DIR__ . '/config/conexion.php';
$titulo_pagina = 'Mis inscripciones';
$stmt = $pdo->prepare('SELECT i.id_inscripcion, i.estado, t.id_torneo, t.titulo, t.fecha_inicio, t.estado AS estado_torneo, c.codigo AS categoria
    FROM inscripciones i INNER JOIN torneos t ON t.id_torneo = i.id_torneo
    INNER JOIN categorias c ON c.id_categoria = t.id_categoria
    WHERE i.id_usuario = :usuario ORDER BY t.fecha_inicio ASC');
$stmt->execute(['usuario' => usuario_actual()['id']]);
$inscripciones = $stmt->fetchAll();
require_once __DIR__ . '/includes/header.php';
?>
<main class="page-content">
    <p class="eyebrow">MI PERFIL</p>
    <h1>Mis inscripciones</h1>
    <h2>Consultá si la organización confirmó tus torneos.</h2>
    <?php if (!$inscripciones): ?><div class="card"><p>Por ahora no te inscribiste a ningún torneo.</p><a class="btn" href="torneos-de-padel.php">Explorar torneos</a></div><?php else: ?>
        <div class="torneos-grid">
            <?php foreach ($inscripciones as $i): ?>
                <article class="tournament-card">
                    <span class="category-chip"><?= e($i['categoria']); ?></span>
                    <h3><?= e($i['titulo']); ?></h3>
                    <p>📅 <?= e(fecha_visible($i['fecha_inicio'])); ?></p>
                    <p>Inscripción: <strong><?= e(ucfirst($i['estado'])); ?></strong></p>
                    <a class="btn" href="torneo.php?id=<?= e($i['id_torneo']); ?>">Ver torneo</a>
                    <?php if ($i['estado'] !== 'rechazada' && $i['estado_torneo'] === 'programado' && strtotime($i['fecha_inicio']) > time()): ?>
                        <form method="post" action="cancelar-inscripcion.php" class="compact-form" data-confirm="¿Confirmás que querés cancelar tu inscripción?">
                            <?= csrf_field(); ?><input type="hidden" name="id_inscripcion" value="<?= e($i['id_inscripcion']); ?>">
                            <button type="submit" class="btn btn--secondary">Cancelar inscripción</button>
                        </form>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
