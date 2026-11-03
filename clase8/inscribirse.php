<?php
require_once __DIR__ . '/includes/auth_guard.php';
require_once __DIR__ . '/config/conexion.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Método no permitido.'); }
auth_requerir_login();
validar_csrf();
$id = filter_var(campo_post('id_torneo'), FILTER_VALIDATE_INT);
if ($id === false || $id < 1) { http_response_code(400); exit('ID inválido.'); }
try {
    $pdo->beginTransaction();
    // El bloqueo de la fila de torneo serializa las altas y protege su cupo.
    $stmt = $pdo->prepare('SELECT id_torneo, id_categoria, id_modalidad, fecha_inicio, cupo_maximo, estado FROM torneos WHERE id_torneo = :id FOR UPDATE');
    $stmt->execute(['id' => $id]);
    $torneo = $stmt->fetch();
    $stmt = $pdo->prepare('SELECT u.id_usuario, u.id_categoria, u.genero, r.nombre AS rol
        FROM usuarios u INNER JOIN roles r ON r.id_rol = u.id_rol WHERE u.id_usuario = :id');
    $stmt->execute(['id' => usuario_actual()['id']]);
    $jugador = $stmt->fetch();
    if (!$torneo || !$jugador || $jugador['rol'] !== 'Jugador') throw new DomainException('No podés inscribirte con esta cuenta.');
    if ($torneo['estado'] !== 'programado' || strtotime($torneo['fecha_inicio']) <= time()) throw new DomainException('La inscripción para este torneo está cerrada.');
    if ((int) $torneo['id_categoria'] !== (int) $jugador['id_categoria']) throw new DomainException('Tu categoría no coincide con la del torneo.');

    $stmt = $pdo->prepare('SELECT nombre FROM modalidades WHERE id_modalidad = :id');
    $stmt->execute(['id' => $torneo['id_modalidad']]);
    $modalidad = $stmt->fetchColumn();
    if (in_array($modalidad, ['Masculino', 'Femenino'], true) && $jugador['genero'] !== $modalidad) {
        throw new DomainException('El torneo está reservado a la modalidad ' . $modalidad . '.');
    }

    $stmt = $pdo->prepare('SELECT id_inscripcion, estado FROM inscripciones WHERE id_usuario = :usuario AND id_torneo = :torneo FOR UPDATE');
    $stmt->execute(['usuario' => $jugador['id_usuario'], 'torneo' => $id]);
    $existente = $stmt->fetch();
    if ($existente && $existente['estado'] !== 'rechazada') throw new DomainException('Ya estás inscripto/a en este torneo.');

    $stmt = $pdo->prepare('SELECT id_inscripcion FROM inscripciones WHERE id_torneo = :id AND estado <> :rechazada FOR UPDATE');
    $stmt->execute(['id' => $id, 'rechazada' => 'rechazada']);
    if (count($stmt->fetchAll()) >= (int) $torneo['cupo_maximo']) throw new DomainException('No quedan lugares disponibles.');

    if ($existente) {
        $stmt = $pdo->prepare('UPDATE inscripciones SET estado = :pendiente WHERE id_inscripcion = :id');
        $stmt->execute(['pendiente' => 'pendiente', 'id' => $existente['id_inscripcion']]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO inscripciones (id_usuario, id_torneo, estado) VALUES (:usuario, :torneo, :pendiente)');
        $stmt->execute(['usuario' => $jugador['id_usuario'], 'torneo' => $id, 'pendiente' => 'pendiente']);
    }
    $pdo->commit();
    flash('ok', 'Inscripción guardada en MySQL. Está pendiente de confirmación.');
} catch (DomainException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    flash('error', $e->getMessage());
} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('Error inscripción: ' . $e->getMessage());
    flash('error', 'No se pudo completar la inscripción.');
}
redirigir('torneo.php?id=' . $id);
