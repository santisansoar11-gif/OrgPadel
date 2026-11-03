<?php
require_once __DIR__ . '/_comun.php';
$titulo_pagina = 'Administrar torneos';
$descripcion_pagina = 'Panel de gestión y seguimiento de torneos.';
$stmt = $pdo->prepare('SELECT t.id_torneo, t.codigo, t.titulo, t.lugar, t.fecha_inicio, t.cupo_maximo, t.estado, c.codigo AS categoria, m.nombre AS modalidad,
    (SELECT COUNT(*) FROM inscripciones i WHERE i.id_torneo = t.id_torneo AND i.estado <> :rechazado) AS ocupados
    FROM torneos t
    INNER JOIN categorias c ON c.id_categoria = t.id_categoria
    INNER JOIN modalidades m ON m.id_modalidad = t.id_modalidad
    ORDER BY t.fecha_inicio DESC, t.id_torneo DESC');
$stmt->execute(['rechazado' => 'rechazada']);
$torneos = $stmt->fetchAll();
require_once __DIR__ . '/../includes/header.php';
?>
<main class="page-content admin-content">
    <p class="eyebrow">PANEL DE ORGANIZACIÓN</p>
    <h1>Gestión de torneos</h1>
    <h2>Administrá tus competencias desde la misma web.</h2>
    <div class="admin-summary">
        <div class="stat-card"><strong><?= e(count($torneos)); ?></strong><span>Torneos registrados</span></div>
        <div class="stat-card"><strong><?= e(array_sum(array_column($torneos, 'ocupados'))); ?></strong><span>Inscripciones activas</span></div>
        <div class="stat-card"><strong><?= e(count(array_filter($torneos, static fn($t) => $t['estado'] === 'programado'))); ?></strong><span>Inscripciones abiertas</span></div>
    </div>
    <div class="toolbar-actions">
        <a class="btn" href="crear.php">+ Crear torneo</a>
        <a class="btn btn--secondary" href="inscripciones.php">Administrar inscripciones</a>
        <a class="btn btn--secondary" href="usuarios.php">Usuarios y contraseñas</a>
        <a class="text-link" href="../torneos-de-padel.php">Ver catálogo público</a>
    </div>
    <?php if (!$torneos): ?>
        <div class="card"><p>No hay torneos. Creá uno para comenzar.</p></div>
    <?php else: ?>
        <div class="table-scroll"><table class="data-table">
            <thead><tr><th>Torneo</th><th>Categoría</th><th>Modalidad</th><th>Fecha</th><th>Cupos</th><th>Estado</th><th>Acciones</th></tr></thead>
            <tbody>
            <?php foreach ($torneos as $torneo): ?>
                <tr>
                    <td><strong><?= e($torneo['titulo']); ?></strong><br><small><?= e($torneo['codigo']); ?></small></td>
                    <td><?= e($torneo['categoria']); ?></td>
                    <td><?= e($torneo['modalidad']); ?></td>
                    <td><?= e(fecha_visible($torneo['fecha_inicio'])); ?></td>
                    <td><?= e($torneo['ocupados']); ?>/<?= e($torneo['cupo_maximo']); ?></td>
                    <td><span class="status-chip"><?= e(opciones_torneo()[$torneo['estado']] ?? $torneo['estado']); ?></span></td>
                    <td><div class="row-actions">
                        <a href="../torneo.php?id=<?= e($torneo['id_torneo']); ?>">Ver</a>
                        <a href="editar.php?id=<?= e($torneo['id_torneo']); ?>">Editar</a>
                        <form method="post" action="eliminar.php" class="inline-form" data-confirm="¿Eliminar este torneo y todas sus inscripciones? Esta acción no se puede deshacer.">
                            <?= csrf_field(); ?>
                            <input type="hidden" name="id" value="<?= e($torneo['id_torneo']); ?>">
                            <button type="submit" class="link-danger">Eliminar</button>
                        </form>
                    </div></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
    <?php endif; ?>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
