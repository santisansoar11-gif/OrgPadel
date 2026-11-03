<?php // Usado desde crear.php y editar.php, con $datos, $accion, $categorias, $modalidades. ?>
<form action="<?= e($accion); ?>" method="post" class="torneo-form">
    <?= csrf_field(); ?>
    <div class="form-grid">
        <div>
            <label for="titulo">Nombre del torneo *</label>
            <input id="titulo" name="titulo" type="text" maxlength="120" required value="<?= e($datos['titulo']); ?>" placeholder="Copa Primavera 4ta">
        </div>
        <div>
            <label for="codigo">Código único *</label>
            <input id="codigo" name="codigo" type="text" maxlength="40" required value="<?= e($datos['codigo']); ?>" placeholder="COPA-PRIMAVERA-4TA">
        </div>
        <div>
            <label for="id_categoria">Categoría *</label>
            <select id="id_categoria" name="id_categoria" required>
                <option value="">Seleccionar</option>
                <?php foreach ($categorias as $op): ?>
                    <option value="<?= e($op['id']); ?>" <?= (string) $datos['id_categoria'] === (string) $op['id'] ? 'selected' : ''; ?>><?= e($op['etiqueta']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="id_modalidad">Modalidad *</label>
            <select id="id_modalidad" name="id_modalidad" required>
                <option value="">Seleccionar</option>
                <?php foreach ($modalidades as $op): ?>
                    <option value="<?= e($op['id']); ?>" <?= (string) $datos['id_modalidad'] === (string) $op['id'] ? 'selected' : ''; ?>><?= e($op['etiqueta']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="lugar">Complejo o cancha *</label>
            <input id="lugar" name="lugar" type="text" maxlength="150" required value="<?= e($datos['lugar']); ?>" placeholder="Club Central - Cancha 1">
        </div>
        <div>
            <label for="fecha_inicio">Fecha y hora de inicio *</label>
            <input id="fecha_inicio" name="fecha_inicio" type="datetime-local" required value="<?= e($datos['fecha_inicio']); ?>">
        </div>
        <div>
            <label for="cupo_maximo">Cupo máximo de jugadores (número par) *</label>
            <input id="cupo_maximo" name="cupo_maximo" type="number" min="2" max="256" step="2" required value="<?= e($datos['cupo_maximo']); ?>">
            <small>Ejemplo: 16 jugadores equivalen a 8 parejas potenciales. Esta versión registra jugadores individualmente.</small>
        </div>
        <div>
            <label for="estado">Estado *</label>
            <select id="estado" name="estado" required>
                <?php foreach (opciones_torneo() as $codigo => $etiqueta): ?>
                    <option value="<?= e($codigo); ?>" <?= $datos['estado'] === $codigo ? 'selected' : ''; ?>><?= e($etiqueta); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <label for="descripcion">Descripción y detalles del torneo</label>
    <textarea id="descripcion" name="descripcion" maxlength="3000" rows="5" placeholder="Cómo será la competencia, premios, información para jugadores..."><?= e($datos['descripcion']); ?></textarea>
    <div class="actions">
        <button type="submit" class="btn"><?= e($boton_texto); ?></button>
        <a class="btn btn--secondary" href="index.php">Volver al panel</a>
    </div>
</form>
