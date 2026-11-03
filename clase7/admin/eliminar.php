<?php
require_once __DIR__ . '/_comun.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Método no permitido.'); }
validar_csrf();
$id = filter_var(campo_post('id'), FILTER_VALIDATE_INT);
if ($id === false || $id < 1) { http_response_code(400); exit('ID de torneo inválido.'); }
try {
    $pdo->beginTransaction();
    // Limpieza explícita de relaciones, sin desactivar claves foráneas.
    $stmt = $pdo->prepare('DELETE FROM inscripciones WHERE id_torneo = :id');
    $stmt->execute(['id' => $id]);
    $stmt = $pdo->prepare('DELETE FROM torneos WHERE id_torneo = :id');
    $stmt->execute(['id' => $id]);
    if ($stmt->rowCount() !== 1) {
        $pdo->rollBack();
        flash('error', 'El torneo ya no existe.');
    } else {
        $pdo->commit();
        flash('ok', 'Torneo e inscripciones relacionadas eliminados.');
    }
} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('Error al eliminar torneo: ' . $e->getMessage());
    flash('error', 'No se pudo eliminar el torneo.');
}
redirigir('index.php');
