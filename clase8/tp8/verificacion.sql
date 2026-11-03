-- TP8: consultas de lectura para comprobar esquema existente.
-- No ejecuta CREATE, UPDATE ni DELETE. No importes nuevamente tp6/schema.sql.
USE padel_tournament_organizer;

SHOW COLUMNS FROM usuarios;
SHOW COLUMNS FROM roles;
SELECT r.nombre AS rol, COUNT(u.id_usuario) AS cantidad
FROM roles r LEFT JOIN usuarios u ON u.id_rol = r.id_rol
GROUP BY r.id_rol, r.nombre ORDER BY r.id_rol;
-- Nunca mostrar password_hash ni contraseñas de las cuentas.
SELECT u.id_usuario, u.nombre_completo, u.email, r.nombre AS rol, u.created_at
FROM usuarios u INNER JOIN roles r ON r.id_rol = u.id_rol
ORDER BY u.id_usuario DESC LIMIT 20;
