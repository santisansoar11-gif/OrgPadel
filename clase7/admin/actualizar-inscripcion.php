<?php
require_once __DIR__ . '/_comun.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Método no permitido.'); }
validar_csrf();
$id = filter_var(campo_post('id'), FILTER_VALIDATE_INT);
$estado = campo_post('estado');
if ($id === false || $id < 1 || !in_array($estado, ['pendiente', 'confirmada', 'rechazada'], true)) {
    http_response_code(400); exit('Datos inválidos.');
}
try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare('SELECT i.estado, i.id_torneo FROM inscripciones i WHERE i.id_inscripcion = :id FOR UPDATE');
    $stmt->execute(['id' => $id]);
    $actual = $stmt->fetch();
    if (!$actual) { $pdo->rollBack(); flash('error', 'No se encontró la inscripción.'); redirigir('inscripciones.php'); }
    // Si se reactiva una inscripción rechazada, controlar cupos también.
    if ($actual['estado'] === 'rechazada' && $estado !== 'rechazada') {
        $torneo = $pdo->prepare('SELECT cupo_maximo FROM torneos WHERE id_torneo = :id FOR UPDATE');
        $torneo->execute(['id' => $actual['id_torneo']]);
        $cupo = (int) $torneo->fetchColumn();
        $cantidad = $pdo->prepare('SELECT COUNT(*) FROM inscripciones WHERE id_torneo = :id AND estado <> :rechazada');
        $cantidad->execute(['id' => $actual['id_torneo'], 'rechazada' => 'rechazada']);
        if ((int) $cantidad->fetchColumn() >= $cupo) {
            $pdo->rollBack(); flash('error', 'No se puede reactivar: no quedan cupos.'); redirigir('inscripciones.php');
        }
    }
    $stmt = $pdo->prepare('UPDATE inscripciones SET estado = :estado WHERE id_inscripcion = :id');
    $stmt->execute(['estado' => $estado, 'id' => $id]);
    $pdo->commit();
    flash('ok', 'El estado de la inscripción fue actualizado.');
} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('Error actualizando inscripción: ' . $e->getMessage());
    flash('error', 'No se pudo actualizar la inscripción.');
}
redirigir('inscripciones.php');
