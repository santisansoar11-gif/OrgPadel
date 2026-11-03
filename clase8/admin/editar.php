<?php
require_once __DIR__ . '/_comun.php';
$titulo_pagina = 'Editar torneo';
$id_raw = campo_get('id');
if (!ctype_digit($id_raw) || (int) $id_raw < 1) { http_response_code(400); exit('ID de torneo inválido.'); }
$id = (int) $id_raw;
$stmt = $pdo->prepare('SELECT * FROM torneos WHERE id_torneo = :id');
$stmt->execute(['id' => $id]);
$torneo = $stmt->fetch();
if (!$torneo) { http_response_code(404); exit('Torneo no encontrado.'); }
$datos = $torneo;
$datos['fecha_inicio'] = str_replace(' ', 'T', substr($torneo['fecha_inicio'], 0, 16));
$errores = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validar_csrf();
    $errores = validar_torneo($pdo, $datos);
    if (!$errores) {
        $check = $pdo->prepare('SELECT COUNT(*) FROM inscripciones WHERE id_torneo = :id AND estado <> :rechazada');
        $check->execute(['id' => $id, 'rechazada' => 'rechazada']);
        $anotados = (int) $check->fetchColumn();
        if ((int) $datos['cupo_maximo'] < $anotados) {
            $errores[] = 'No podés fijar un cupo menor que las inscripciones activas (' . $anotados . ').';
        }
        if ($anotados > 0 && ((int) $datos['id_categoria'] !== (int) $torneo['id_categoria'] || (int) $datos['id_modalidad'] !== (int) $torneo['id_modalidad'])) {
            $errores[] = 'Hay jugadores anotados: no cambies categoría ni modalidad hasta resolver sus inscripciones.';
        }
    }
    if (!$errores) {
        try {
            $stmt = $pdo->prepare('UPDATE torneos SET id_categoria = :categoria, id_modalidad = :modalidad,
                codigo = :codigo, titulo = :titulo, descripcion = :descripcion, lugar = :lugar,
                fecha_inicio = :fecha, cupo_maximo = :cupo, estado = :estado WHERE id_torneo = :id');
            $stmt->execute([
                'categoria' => (int) $datos['id_categoria'], 'modalidad' => (int) $datos['id_modalidad'],
                'codigo' => $datos['codigo'], 'titulo' => $datos['titulo'],
                'descripcion' => $datos['descripcion'], 'lugar' => $datos['lugar'],
                'fecha' => fecha_para_sql($datos['fecha_inicio']), 'cupo' => (int) $datos['cupo_maximo'],
                'estado' => $datos['estado'], 'id' => $id,
            ]);
            flash('ok', 'Los cambios del torneo se guardaron correctamente.');
            redirigir('index.php');
        } catch (PDOException $e) {
            error_log('Error editando torneo: ' . $e->getMessage());
            $errores[] = ($e->getCode() === '23000') ? 'El código elegido ya está en uso.' : 'No fue posible actualizar el torneo.';
        }
    }
}
$categorias = opciones_maestras($pdo, 'categorias');
$modalidades = opciones_maestras($pdo, 'modalidades');
$accion = 'editar.php?id=' . $id;
$boton_texto = 'Guardar cambios';
require_once __DIR__ . '/../includes/header.php';
?>
<main class="page-content admin-content">
    <p class="eyebrow">MODIFICAR COMPETENCIA</p>
    <h1>Editar torneo</h1>
    <h2><?= e($torneo['titulo']); ?></h2>
    <?php if ($errores): ?><div class="card form-feedback--error" role="alert"><strong>Revisá estos datos:</strong><ul><?php foreach ($errores as $error): ?><li><?= e($error); ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <?php require __DIR__ . '/_formulario.php'; ?>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
