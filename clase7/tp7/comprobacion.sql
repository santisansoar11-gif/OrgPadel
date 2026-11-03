-- COMPROBACIÓN DE TP7 - SOLO LECTURA, NO MODIFICA LA BASE.
-- Se puede ejecutar desde phpMyAdmin > pestaña SQL.
USE padel_tournament_organizer;

-- Torneos creados desde el panel, con categoría y modalidad:
SELECT t.id_torneo, t.codigo, t.titulo, c.codigo AS categoria,
       m.nombre AS modalidad, t.lugar, t.fecha_inicio,
       t.cupo_maximo, t.estado, t.created_at, t.updated_at
FROM torneos t
INNER JOIN categorias c ON c.id_categoria = t.id_categoria
INNER JOIN modalidades m ON m.id_modalidad = t.id_modalidad
ORDER BY t.id_torneo DESC;

-- Inscripciones de jugadores guardadas en MySQL:
SELECT i.id_inscripcion, u.nombre_completo AS jugador,
       t.titulo AS torneo, i.estado AS estado_inscripcion, i.created_at
FROM inscripciones i
INNER JOIN usuarios u ON u.id_usuario = i.id_usuario
INNER JOIN torneos t ON t.id_torneo = i.id_torneo
ORDER BY i.id_inscripcion DESC;

-- Conteo total de registros para demostrar cambios CRUD:
SELECT (SELECT COUNT(*) FROM torneos) AS cantidad_torneos,
       (SELECT COUNT(*) FROM inscripciones) AS cantidad_inscripciones,
       (SELECT COUNT(*) FROM usuarios) AS cantidad_usuarios,
       (SELECT COUNT(*) FROM consultas) AS cantidad_consultas;
