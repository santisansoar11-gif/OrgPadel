-- TP6 - Padel Tournament Organizer
-- Ejecutar DESPUES de schema.sql, sobre una base recien creada.
-- Los nombres, correos, canchas y torneos siguientes son datos FICTICIOS.
-- Los hashes de ejemplo corresponden a contraseñas aleatorias no publicadas.

USE padel_tournament_organizer;

-- ================================================================
-- C: CREATE - Maestras (al menos 2 inserciones)
-- ================================================================
INSERT INTO roles (nombre, descripcion) VALUES
    ('Administrador', 'Gestiona torneos y participantes'),
    ('Jugador', 'Se registra para participar en torneos');

INSERT INTO categorias (codigo, descripcion) VALUES
    ('1ra', 'Para jugadores profesionales y de elite'),
    ('2da', 'Para jugadores avanzados con experiencia'),
    ('3ra', 'Para jugadores con nivel intermedio'),
    ('4ta', 'Para jugadores principiantes e intermedios'),
    ('5ta', 'Para jugadores de nivel basico'),
    ('6ta', 'Para jugadores de nivel principiante'),
    ('7ma', 'Para jugadores de nivel iniciado'),
    ('8va', 'Para jugadores de nivel muy basico');

INSERT INTO modalidades (nombre) VALUES
    ('Masculino'),
    ('Femenino'),
    ('Mixto');

-- Usuarios de prueba: se referencian roles y categorias por sus
-- identificadores, obtenidos mediante subconsultas.
INSERT INTO usuarios
    (id_rol, id_categoria, nombre_completo, email, password_hash, telefono, genero)
VALUES
    (
        (SELECT id_rol FROM roles WHERE nombre = 'Jugador'),
        (SELECT id_categoria FROM categorias WHERE codigo = '4ta'),
        'Lucia Torres', 'lucia.torres@example.test',
        '$2y$12$3xHAXgHgNTyRNltHb53cnOeDHwpMjCLu0Gsn4PdEGqv77HHEqh1ky',
        NULL, 'Femenino'
    ),
    (
        (SELECT id_rol FROM roles WHERE nombre = 'Jugador'),
        (SELECT id_categoria FROM categorias WHERE codigo = '4ta'),
        'Mateo Rios', 'mateo.rios@example.test',
        '$2y$12$/gEtSzf.qGnwZ1cE7sZwJ.gpFnqlaCxi/jFTbPvTc8wz5u6V0cDau',
        NULL, 'Masculino'
    ),
    (
        (SELECT id_rol FROM roles WHERE nombre = 'Jugador'),
        (SELECT id_categoria FROM categorias WHERE codigo = '6ta'),
        'Sofia Vega', 'sofia.vega@example.test',
        '$2y$12$xQ90LPrqcgHuujgSVwKMFeFEp37x7HXtO6LIab3jzW3Y8cWEcLRGy',
        NULL, 'Femenino'
    );

-- Tabla principal: dos torneos, cada uno relacionado con su categoria
-- y su modalidad mediante claves foraneas.
INSERT INTO torneos
    (id_categoria, id_modalidad, codigo, titulo, descripcion,
     lugar, fecha_inicio, cupo_maximo, estado)
VALUES
    (
        (SELECT id_categoria FROM categorias WHERE codigo = '4ta'),
        (SELECT id_modalidad FROM modalidades WHERE nombre = 'Mixto'),
        'COPA-PRIMAVERA-4TA-2026',
        'Copa Primavera - 4ta',
        'Torneo de prueba para categoria cuarta, modalidad mixta.',
        'Cancha central (ejemplo)', '2026-11-07 10:00:00', 16, 'programado'
    ),
    (
        (SELECT id_categoria FROM categorias WHERE codigo = '6ta'),
        (SELECT id_modalidad FROM modalidades WHERE nombre = 'Femenino'),
        'COPA-NOVIEMBRE-6TA-2026',
        'Copa Noviembre - 6ta',
        'Torneo de prueba para categoria sexta, modalidad femenina.',
        'Cancha techada (ejemplo)', '2026-11-21 09:00:00', 12, 'programado'
    );

-- Relacion N:M: dos jugadores inscriptos al primer torneo y una
-- jugadora inscripta al segundo. No se repiten datos personales.
INSERT INTO inscripciones (id_usuario, id_torneo, estado) VALUES
    (
        (SELECT id_usuario FROM usuarios WHERE email = 'lucia.torres@example.test'),
        (SELECT id_torneo FROM torneos WHERE codigo = 'COPA-PRIMAVERA-4TA-2026'),
        'confirmada'
    ),
    (
        (SELECT id_usuario FROM usuarios WHERE email = 'mateo.rios@example.test'),
        (SELECT id_torneo FROM torneos WHERE codigo = 'COPA-PRIMAVERA-4TA-2026'),
        'pendiente'
    ),
    (
        (SELECT id_usuario FROM usuarios WHERE email = 'sofia.vega@example.test'),
        (SELECT id_torneo FROM torneos WHERE codigo = 'COPA-NOVIEMBRE-6TA-2026'),
        'confirmada'
    );

-- Registro temporal para validar DELETE sin afectar datos de torneos.
INSERT INTO consultas (nombre, email, mensaje, estado)
VALUES (
    'Visitante de prueba',
    'consulta-temporal@example.test',
    'Consulta temporal creada para comprobar el borrado del TP6.',
    'pendiente'
);

-- ================================================================
-- R: READ - WHERE y ORDER BY
-- ================================================================
SELECT t.id_torneo, t.titulo, c.codigo AS categoria,
       t.fecha_inicio, t.cupo_maximo, t.estado
FROM torneos AS t
INNER JOIN categorias AS c ON c.id_categoria = t.id_categoria
WHERE c.codigo = '4ta' AND t.estado = 'programado'
ORDER BY t.fecha_inicio ASC;

-- READ: INNER JOIN entre usuarios, inscripciones, torneos,
-- categorias y modalidades.
SELECT u.nombre_completo AS jugador,
       u.email,
       t.titulo AS torneo,
       c.codigo AS categoria_torneo,
       m.nombre AS modalidad,
       i.estado AS estado_inscripcion
FROM inscripciones AS i
INNER JOIN usuarios AS u ON u.id_usuario = i.id_usuario
INNER JOIN torneos AS t ON t.id_torneo = i.id_torneo
INNER JOIN categorias AS c ON c.id_categoria = t.id_categoria
INNER JOIN modalidades AS m ON m.id_modalidad = t.id_modalidad
ORDER BY t.fecha_inicio ASC, u.nombre_completo ASC;

-- ================================================================
-- U: UPDATE - modificar un torneo especifico con WHERE
-- ================================================================
UPDATE torneos
SET cupo_maximo = 20
WHERE codigo = 'COPA-PRIMAVERA-4TA-2026';

-- Verificacion del UPDATE:
SELECT codigo, titulo, cupo_maximo
FROM torneos
WHERE codigo = 'COPA-PRIMAVERA-4TA-2026';

-- ================================================================
-- D: DELETE - eliminar SOLO el mensaje temporal de prueba
-- ================================================================
DELETE FROM consultas
WHERE email = 'consulta-temporal@example.test'
  AND mensaje = 'Consulta temporal creada para comprobar el borrado del TP6.';

-- Verificacion del DELETE (debe mostrar 0):
SELECT COUNT(*) AS consultas_temporales_restantes
FROM consultas
WHERE email = 'consulta-temporal@example.test';
