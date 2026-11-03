-- SOLO SI no ejecutaste consultas.sql en el TP6 o faltan roles/categorias/modalidades.
-- Insertar referencias sin destruir ni duplicar las existentes.
-- Si ya tenés todo el TP6 funcionando, no hace falta ejecutarlo.
USE padel_tournament_organizer;
INSERT IGNORE INTO roles (nombre, descripcion) VALUES
('Administrador', 'Puede gestionar torneos e inscripciones'),
('Jugador', 'Puede anotarse a torneos');
INSERT IGNORE INTO categorias (codigo, descripcion) VALUES
('1ra', 'Profesional'), ('2da', 'Avanzado'), ('3ra', 'Intermedio'),
('4ta', 'Intermedio'), ('5ta', 'Básico'), ('6ta', 'Principiante'),
('7ma', 'Iniciado'), ('8va', 'Inicial');
INSERT IGNORE INTO modalidades (nombre) VALUES
('Masculino'), ('Femenino'), ('Mixto');
