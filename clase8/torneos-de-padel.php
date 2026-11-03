<?php
require_once __DIR__ . '/includes/funciones.php';
require_once __DIR__ . '/config/conexion.php';
$titulo_pagina = 'Torneos de pádel';
$descripcion_pagina = 'Torneos disponibles con fechas, categorías, modalidad y cupos en tiempo real.';

$stmt = $pdo->prepare('SELECT id_categoria, codigo FROM categorias ORDER BY id_categoria');
$stmt->execute();
$categorias = $stmt->fetchAll();
$filtrar = campo_get('categoria');
if ($filtrar !== '' && !in_array($filtrar, array_column($categorias, 'codigo'), true)) $filtrar = '';
$sql = 'SELECT t.id_torneo, t.titulo, t.descripcion, t.lugar, t.fecha_inicio, t.cupo_maximo, t.estado,
    c.codigo AS categoria, m.nombre AS modalidad,
    (SELECT COUNT(*) FROM inscripciones i WHERE i.id_torneo = t.id_torneo AND i.estado <> :rechazada) AS ocupados
    FROM torneos t INNER JOIN categorias c ON c.id_categoria = t.id_categoria
    INNER JOIN modalidades m ON m.id_modalidad = t.id_modalidad
    WHERE t.estado <> :cancelado';
$params = ['rechazada' => 'rechazada', 'cancelado' => 'cancelado'];
if ($filtrar !== '') { $sql .= ' AND c.codigo = :categoria'; $params['categoria'] = $filtrar; }
$sql .= ' ORDER BY t.fecha_inicio ASC, t.id_torneo DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$torneos = $stmt->fetchAll();
require_once __DIR__ . '/includes/header.php';
?>
<main class="page-content tournaments-page">
    <p class="eyebrow">CALENDARIO DE COMPETENCIAS</p>
    <h1>Explorá torneos de pádel</h1>
    <h2>Elegí tu categoría y conocé los torneos disponibles.</h2>
    <section class="filter-panel">
        <form action="torneos-de-padel.php" method="get" class="filter-form">
            <label for="categoria">Filtrar por categoría</label>
            <select id="categoria" name="categoria">
                <option value="">Todas las categorías</option>
                <?php foreach ($categorias as $c): ?><option value="<?= e($c['codigo']); ?>" <?= $c['codigo'] === $filtrar ? 'selected' : ''; ?>><?= e($c['codigo']); ?></option><?php endforeach; ?>
            </select>
            <button class="btn" type="submit">Buscar torneos</button>
        </form>
    </section>
    <?php if (!$torneos): ?>
        <div class="card"><p>No hay torneos publicados para esta categoría.</p></div>
    <?php else: ?>
        <div class="torneos-grid">
            <?php foreach ($torneos as $torneo): ?>
                <?php $abierto = $torneo['estado'] === 'programado' && strtotime($torneo['fecha_inicio']) > time() && (int) $torneo['ocupados'] < (int) $torneo['cupo_maximo']; ?>
                <article class="tournament-card">
                    <div class="tournament-top"><span class="category-chip"><?= e($torneo['categoria']); ?></span><span class="status-chip"><?= $abierto ? 'Inscripciones abiertas' : e($torneo['estado'] === 'programado' ? ((int)$torneo['ocupados'] >= (int)$torneo['cupo_maximo'] ? 'Cupos completos' : 'Inscripción cerrada') : (opciones_torneo()[$torneo['estado']] ?? $torneo['estado'])); ?></span></div>
                    <h3><?= e($torneo['titulo']); ?></h3>
                    <p class="tournament-desc"><?= e(mb_strimwidth((string) $torneo['descripcion'], 0, 180, '…', 'UTF-8')); ?></p>
                    <div class="tournament-meta">
                        <span>📅 <?= e(fecha_visible($torneo['fecha_inicio'])); ?></span>
                        <span>📍 <?= e($torneo['lugar']); ?></span>
                        <span>🏓 <?= e($torneo['modalidad']); ?></span>
                    </div>
                    <div class="capacity"><span><?= e($torneo['ocupados']); ?> / <?= e($torneo['cupo_maximo']); ?> jugadores inscriptos</span>
                        <div class="capacity-bar"><i style="width: <?= e(min(100, round(((int) $torneo['ocupados'] / max(1, (int) $torneo['cupo_maximo'])) * 100))); ?>%"></i></div>
                    </div>
                    <a class="btn" href="torneo.php?id=<?= e($torneo['id_torneo']); ?>">Ver torneo</a>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <p class="info-note">Las inscripciones son individuales en esta versión del TP7. La formación automática de parejas y el fixture son funcionalidades futuras.</p>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
