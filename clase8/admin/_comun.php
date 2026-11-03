<?php
require_once __DIR__ . '/../includes/auth_guard.php';
// Guardar el orden: impedir el acceso ANTES de conectarse a MySQL.
auth_requerir_login('../login.php');
if (!es_admin()) {
    http_response_code(403);
    exit('Acceso denegado: esta sección es para administradores.');
}
require_once __DIR__ . '/../config/conexion.php';
auth_requerir_rol($pdo, 'admin', '../login.php');
$base_path = '../';

function opciones_maestras(PDO $pdo, string $tabla): array
{
    // Tabla fija elegida por el código (no por el usuario).
    if ($tabla === 'categorias') {
        $stmt = $pdo->prepare('SELECT id_categoria AS id, codigo AS etiqueta FROM categorias ORDER BY id_categoria');
    } else {
        $stmt = $pdo->prepare('SELECT id_modalidad AS id, nombre AS etiqueta FROM modalidades ORDER BY id_modalidad');
    }
    $stmt->execute();
    return $stmt->fetchAll();
}

function validar_torneo(PDO $pdo, array &$datos): array
{
    $errores = [];
    $datos = [
        'codigo' => strtoupper(campo_post('codigo')),
        'titulo' => campo_post('titulo'),
        'descripcion' => campo_post('descripcion'),
        'lugar' => campo_post('lugar'),
        'id_categoria' => campo_post('id_categoria'),
        'id_modalidad' => campo_post('id_modalidad'),
        'fecha_inicio' => campo_post('fecha_inicio'),
        'cupo_maximo' => campo_post('cupo_maximo'),
        'estado' => campo_post('estado'),
    ];
    if (!preg_match('/^[A-Z0-9-]{4,40}$/', $datos['codigo'])) {
        $errores[] = 'El código debe tener de 4 a 40 caracteres: letras, números o guiones.';
    }
    if (mb_strlen($datos['titulo']) < 4 || mb_strlen($datos['titulo']) > 120) {
        $errores[] = 'El nombre debe tener de 4 a 120 caracteres.';
    }
    if (mb_strlen($datos['descripcion']) > 3000) $errores[] = 'La descripción es demasiado extensa.';
    if (mb_strlen($datos['lugar']) < 3 || mb_strlen($datos['lugar']) > 150) {
        $errores[] = 'Indicá una sede válida (3 a 150 caracteres).';
    }
    foreach (['id_categoria' => 'categorías', 'id_modalidad' => 'modalidades'] as $clave => $tabla) {
        $id = filter_var($datos[$clave], FILTER_VALIDATE_INT);
        $lista = opciones_maestras($pdo, $tabla);
        if ($id === false || $id < 1 || !in_array((int) $id, array_column($lista, 'id'), false)) {
            $errores[] = 'Seleccioná una opción válida en ' . $tabla . '.';
        }
    }
    if (fecha_para_sql($datos['fecha_inicio']) === null) $errores[] = 'Ingresá una fecha y hora válidas.';
    $cupo = filter_var($datos['cupo_maximo'], FILTER_VALIDATE_INT);
    if ($cupo === false || $cupo < 2 || $cupo > 256 || $cupo % 2 !== 0) {
        $errores[] = 'El cupo de jugadores debe ser un número par entre 2 y 256.';
    }
    if (!array_key_exists($datos['estado'], opciones_torneo())) $errores[] = 'El estado del torneo no es válido.';
    return $errores;
}
