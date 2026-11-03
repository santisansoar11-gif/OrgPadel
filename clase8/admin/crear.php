<?php
require_once __DIR__ . '/_comun.php';
$titulo_pagina = 'Crear torneo';
$descripcion_pagina = 'Alta de torneos de pádel.';
$datos = [
    'codigo' => '', 'titulo' => '', 'descripcion' => '', 'lugar' => '',
    'id_categoria' => '', 'id_modalidad' => '',
    'fecha_inicio' => '', 'cupo_maximo' => '16', 'estado' => 'programado',
];
$errores = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validar_csrf();
    $errores = validar_torneo($pdo, $datos);
    if (!$errores) {
        try {
            $stmt = $pdo->prepare('INSERT INTO torneos
                (id_categoria, id_modalidad, codigo, titulo, descripcion, lugar, fecha_inicio, cupo_maximo, estado)
                VALUES (:categoria, :modalidad, :codigo, :titulo, :descripcion, :lugar, :fecha, :cupo, :estado)');
            $stmt->execute([
                'categoria' => (int) $datos['id_categoria'], 'modalidad' => (int) $datos['id_modalidad'],
                'codigo' => $datos['codigo'], 'titulo' => $datos['titulo'],
                'descripcion' => $datos['descripcion'], 'lugar' => $datos['lugar'],
                'fecha' => fecha_para_sql($datos['fecha_inicio']), 'cupo' => (int) $datos['cupo_maximo'],
                'estado' => $datos['estado'],
            ]);
            flash('ok', 'El torneo se creó en MySQL y ya aparece en el sitio.');
            redirigir('index.php');
        } catch (PDOException $e) {
            error_log('Error creando torneo: ' . $e->getMessage());
            $errores[] = ($e->getCode() === '23000') ? 'Ese código de torneo ya existe.' : 'No se pudo guardar el torneo. Revisá los datos.';
        }
    }
}
$categorias = opciones_maestras($pdo, 'categorias');
$modalidades = opciones_maestras($pdo, 'modalidades');
$accion = 'crear.php';
$boton_texto = 'Crear torneo';
require_once __DIR__ . '/../includes/header.php';
?>
<main class="page-content admin-content">
    <p class="eyebrow">NUEVA COMPETENCIA</p>
    <h1>Crear torneo</h1>
    <h2>Configurá la categoría, modalidad, fecha, sede y cupos.</h2>
    <?php if ($errores): ?><div class="card form-feedback--error" role="alert"><strong>Revisá estos datos:</strong><ul><?php foreach ($errores as $error): ?><li><?= e($error); ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <?php require __DIR__ . '/_formulario.php'; ?>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
