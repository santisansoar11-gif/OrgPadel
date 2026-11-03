<?php
require_once __DIR__ . '/_comun.php';
$titulo_pagina = 'Administrar inscripciones';
$stmt = $pdo->prepare('SELECT i.id_inscripcion, i.estado, i.created_at, u.nombre_completo, u.email, t.titulo, t.codigo, c.codigo AS categoria
    FROM inscripciones i INNER JOIN usuarios u ON u.id_usuario = i.id_usuario
    INNER JOIN torneos t ON t.id_torneo = i.id_torneo
    INNER JOIN categorias c ON c.id_categoria = t.id_categoria
    ORDER BY i.created_at DESC, i.id_inscripcion DESC');
$stmt->execute();
$inscripciones = $stmt->fetchAll();
require_once __DIR__ . '/../includes/header.php';
?>
<main class="page-content admin-content">
    <p class="eyebrow">JUGADORES</p>
    <h1>Inscripciones recibidas</h1>
    <h2>Confirmá o rechazá inscripciones desde la base de datos.</h2>
    <p><a class="btn btn--secondary" href="index.php">Volver a torneos</a></p>
    <?php if (!$inscripciones): ?><div class="card">Todavía no hay inscripciones.</div><?php else: ?>
        <div class="table-scroll"><table class="data-table">
        <thead><tr><th>Jugador</th><th>Torneo</th><th>Fecha</th><th>Estado</th><th>Modificar estado</th></tr></thead>
        <tbody>
        <?php foreach ($inscripciones as $item): ?>
        <tr>
            <td><strong><?= e($item['nombre_completo']); ?></strong><br><small><?= e($item['email']); ?></small></td>
            <td><?= e($item['titulo']); ?> <small>(<?= e($item['categoria']); ?>)</small></td>
            <td><?= e(fecha_visible($item['created_at'])); ?></td>
            <td><?= e(ucfirst($item['estado'])); ?></td>
            <td><form class="inline-form inline-status" method="post" action="actualizar-inscripcion.php">
                <?= csrf_field(); ?>
                <input type="hidden" name="id" value="<?= e($item['id_inscripcion']); ?>">
                <select name="estado" aria-label="Estado de <?= e($item['nombre_completo']); ?>">
                    <?php foreach (['pendiente','confirmada','rechazada'] as $estado): ?>
                        <option value="<?= e($estado); ?>" <?= $item['estado'] === $estado ? 'selected' : ''; ?>><?= e(ucfirst($estado)); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn">Guardar</button>
            </form></td>
        </tr>
        <?php endforeach; ?>
        </tbody></table></div>
    <?php endif; ?>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
