<?php
require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/includes/funciones.php';
$titulo_pagina = 'Inicio';
$descripcion_pagina = 'Padel Tournament Organizer: torneos, categorías e inscripciones online.';
$stmt = $pdo->prepare('SELECT t.id_torneo, t.titulo, t.fecha_inicio, t.lugar, c.codigo AS categoria
    FROM torneos t INNER JOIN categorias c ON c.id_categoria = t.id_categoria
    WHERE t.estado = :estado AND t.fecha_inicio > :ahora ORDER BY t.fecha_inicio ASC LIMIT 3');
$stmt->execute(['estado' => 'programado', 'ahora' => date('Y-m-d H:i:s')]);
$proximos = $stmt->fetchAll();
require_once __DIR__ . '/includes/header.php';
?>
<main class="page-content">
    <p class="eyebrow">PADEL TOURNAMENT ORGANIZER</p>
    <h1>Organizá, competí y viví el pádel</h1>
    <h2>Un espacio para crear y participar en torneos de tu categoría.</h2>
    <p>Bienvenido/a a <?= e($nombre_sitio); ?>. Desde esta web podés consultar torneos disponibles, conocer sus fechas y sedes, crear una cuenta e inscribirte. La organización administra los torneos desde su panel.</p>
    <p><a href="torneos-de-padel.php" class="btn">Explorar torneos</a></p>
    <h3>Próximos torneos</h3>
    <?php if (!$proximos): ?><div class="card"><p>Aún no hay torneos programados. Volvé a consultar pronto.</p></div><?php else: ?>
        <div class="torneos-grid">
            <?php foreach ($proximos as $t): ?>
                <article class="tournament-card">
                    <span class="category-chip"><?= e($t['categoria']); ?></span>
                    <h3><?= e($t['titulo']); ?></h3>
                    <p>📅 <?= e(fecha_visible($t['fecha_inicio'])); ?></p>
                    <p>📍 <?= e($t['lugar']); ?></p>
                    <a class="btn" href="torneo.php?id=<?= e($t['id_torneo']); ?>">Ver detalles</a>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
