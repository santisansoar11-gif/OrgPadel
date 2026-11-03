<?php
require_once __DIR__ . '/includes/funciones.php';
require_once __DIR__ . '/config/conexion.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Método no permitido.'); }
exigir_login();
validar_csrf();
$id = filter_var(campo_post('id_inscripcion'), FILTER_VALIDATE_INT);
if ($id === false || $id < 1) { http_response_code(400); exit('ID inválido.'); }
try {
    $stmt = $pdo->prepare('DELETE i FROM inscripciones i INNER JOIN torneos t ON t.id_torneo = i.id_torneo
        WHERE i.id_inscripcion = :id AND i.id_usuario = :usuario AND t.estado = :estado AND t.fecha_inicio > :ahora');
    $stmt->execute(['id' => $id, 'usuario' => usuario_actual()['id'], 'estado' => 'programado', 'ahora' => date('Y-m-d H:i:s')]);
    flash($stmt->rowCount() ? 'ok' : 'error', $stmt->rowCount() ? 'Inscripción cancelada.' : 'No se puede cancelar esta inscripción.');
} catch (PDOException $e) {
    error_log('Error cancelación: ' . $e->getMessage());
    flash('error', 'No se pudo cancelar la inscripción.');
}
redirigir('mis-inscripciones.php');
