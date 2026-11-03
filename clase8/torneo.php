<?php
require_once __DIR__ . '/includes/funciones.php';
require_once __DIR__ . '/config/conexion.php';
$id = filter_var(campo_get('id'), FILTER_VALIDATE_INT);
if ($id === false || $id < 1) { http_response_code(400); exit('ID de torneo inválido.'); }
$stmt = $pdo->prepare('SELECT t.*, c.codigo AS categoria, m.nombre AS modalidad,
    (SELECT COUNT(*) FROM inscripciones i WHERE i.id_torneo = t.id_torneo AND i.estado <> :rechazada) AS ocupados
    FROM torneos t INNER JOIN categorias c ON c.id_categoria = t.id_categoria
    INNER JOIN modalidades m ON m.id_modalidad = t.id_modalidad
    WHERE t.id_torneo = :id');
$stmt->execute(['rechazada' => 'rechazada', 'id' => $id]);
$torneo = $stmt->fetch();
if (!$torneo || ($torneo['estado'] === 'cancelado' && !es_admin())) { http_response_code(404); exit('Torneo no encontrado.'); }
$titulo_pagina = $torneo['titulo'];
$abierto = $torneo['estado'] === 'programado' && strtotime($torneo['fecha_inicio']) > time() && (int) $torneo['ocupados'] < (int) $torneo['cupo_maximo'];
$ya_inscrito = false;
$usuario = usuario_actual();
if ($usuario) {
    $stmt = $pdo->prepare('SELECT estado FROM inscripciones WHERE id_torneo = :torneo AND id_usuario = :usuario LIMIT 1');
    $stmt->execute(['torneo' => $id, 'usuario' => (int) $usuario['id']]);
    $estado_inscripcion = $stmt->fetchColumn();
    $ya_inscrito = $estado_inscripcion && $estado_inscripcion !== 'rechazada';
}
require_once __DIR__ . '/includes/header.php';
?>
<main class="page-content tournament-detail">
    <p><a href="torneos-de-padel.php">← Volver a torneos</a></p>
    <p class="eyebrow">DETALLES DE LA COMPETENCIA</p>
    <h1><?= e($torneo['titulo']); ?></h1>
    <h2><?= e($torneo['categoria']); ?> · <?= e($torneo['modalidad']); ?></h2>
    <div class="detail-layout">
        <section class="detail-panel">
            <div class="tournament-top"><span class="category-chip"><?= e($torneo['categoria']); ?></span><span class="status-chip"><?= e($abierto ? 'Inscripciones abiertas' : ($torneo['estado'] === 'programado' ? 'Inscripciones cerradas' : (opciones_torneo()[$torneo['estado']] ?? $torneo['estado']))); ?></span></div>
            <h3>Información del torneo</h3>
            <dl class="detail-list">
                <dt>Fecha y hora</dt><dd><?= e(fecha_visible($torneo['fecha_inicio'])); ?></dd>
                <dt>Sede</dt><dd><?= e($torneo['lugar']); ?></dd>
                <dt>Modalidad</dt><dd><?= e($torneo['modalidad']); ?></dd>
                <dt>Categoría</dt><dd><?= e($torneo['categoria']); ?></dd>
                <dt>Código</dt><dd><?= e($torneo['codigo']); ?></dd>
            </dl>
            <h3>Descripción</h3>
            <p><?= nl2br(e($torneo['descripcion'] ?: 'Próximamente más detalles de esta competencia.')); ?></p>
        </section>
        <aside class="detail-panel entry-panel">
            <h3>Inscripciones</h3>
            <p class="entry-number"><?= e($torneo['ocupados']); ?> <small>de <?= e($torneo['cupo_maximo']); ?> jugadores</small></p>
            <div class="capacity-bar"><i style="width: <?= e(min(100, round(((int) $torneo['ocupados'] / max(1, (int) $torneo['cupo_maximo'])) * 100))); ?>%"></i></div>
            <?php if ($ya_inscrito): ?>
                <p><strong>Ya estás inscripto/a.</strong> Estado: <?= e(ucfirst($estado_inscripcion)); ?>.</p>
                <a class="btn btn--secondary" href="mis-inscripciones.php">Ver mis inscripciones</a>
            <?php elseif (!$abierto): ?>
                <p>Las inscripciones no están abiertas (cupo completo, fecha iniciada o torneo cerrado).</p>
            <?php elseif (!$usuario): ?>
                <p>Ingresá con tu cuenta para reservar un lugar.</p>
                <a class="btn" href="login.php">Ingresar para inscribirme</a>
                <p>¿No tenés cuenta? <a href="registro.php">Registrate acá</a>.</p>
            <?php elseif (es_admin()): ?>
                <p>Los administradores gestionan torneos desde el panel. Usá una cuenta de jugador para inscribirte.</p>
            <?php else: ?>
                <form action="inscribirse.php" method="post" class="compact-form">
                    <?= csrf_field(); ?>
                    <input type="hidden" name="id_torneo" value="<?= e($torneo['id_torneo']); ?>">
                    <button class="btn" type="submit">Inscribirme a este torneo</button>
                </form>
                <small>Tu inscripción queda pendiente hasta que la organización la confirme. Debés pertenecer a la categoría indicada.</small>
            <?php endif; ?>
        </aside>
    </div>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
