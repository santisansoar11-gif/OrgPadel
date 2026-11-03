-- TP6 - Padel Tournament Organizer
-- Diseño relacional normalizado hasta 3FN.
-- Ejecutar primero este archivo en phpMyAdmin o MySQL (XAMPP / Laragon).
-- ATENCION: este script crea tablas si no existen, pero el CREATE INDEX
-- del final debe ejecutarse una sola vez por base de datos.
-- No elimina bases de datos ni tablas existentes.

CREATE DATABASE IF NOT EXISTS padel_tournament_organizer
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE padel_tournament_organizer;

-- 1. Tabla maestra: roles de acceso.
CREATE TABLE IF NOT EXISTS roles (
    id_rol INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(30) NOT NULL UNIQUE,
    descripcion VARCHAR(200) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabla maestra: nivel/categoria del jugador o del torneo.
CREATE TABLE IF NOT EXISTS categorias (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(5) NOT NULL UNIQUE,
    descripcion VARCHAR(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabla maestra: modalidad de juego.
CREATE TABLE IF NOT EXISTS modalidades (
    id_modalidad INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(20) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Usuarios: datos personales y autenticacion.
-- password_hash almacena el HASH generado por password_hash() de PHP,
-- nunca una contraseña en texto plano.
CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    id_rol INT NOT NULL,
    id_categoria INT NULL,
    nombre_completo VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    telefono VARCHAR(30) NULL,
    genero VARCHAR(30) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_usuarios_roles
        FOREIGN KEY (id_rol) REFERENCES roles(id_rol),
    CONSTRAINT fk_usuarios_categorias
        FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Tabla principal del negocio: torneos disponibles.
CREATE TABLE IF NOT EXISTS torneos (
    id_torneo INT AUTO_INCREMENT PRIMARY KEY,
    id_categoria INT NOT NULL,
    id_modalidad INT NOT NULL,
    codigo VARCHAR(40) NOT NULL UNIQUE,
    titulo VARCHAR(120) NOT NULL,
    descripcion TEXT NULL,
    lugar VARCHAR(150) NOT NULL,
    fecha_inicio DATETIME NOT NULL,
    cupo_maximo INT UNSIGNED NOT NULL DEFAULT 16,
    estado VARCHAR(20) NOT NULL DEFAULT 'programado',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_torneos_categorias
        FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria),
    CONSTRAINT fk_torneos_modalidades
        FOREIGN KEY (id_modalidad) REFERENCES modalidades(id_modalidad)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Tabla de relacion N:M entre usuarios y torneos.
-- La restriccion UNIQUE impide inscribir dos veces al mismo usuario
-- en el mismo torneo.
CREATE TABLE IF NOT EXISTS inscripciones (
    id_inscripcion INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_torneo INT NOT NULL,
    estado VARCHAR(20) NOT NULL DEFAULT 'pendiente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_inscripciones_usuario_torneo UNIQUE (id_usuario, id_torneo),
    CONSTRAINT fk_inscripciones_usuarios
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario),
    CONSTRAINT fk_inscripciones_torneos
        FOREIGN KEY (id_torneo) REFERENCES torneos(id_torneo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Consultas de contacto: pueden proceder de visitantes sin cuenta.
CREATE TABLE IF NOT EXISTS consultas (
    id_consulta INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(80) NOT NULL,
    email VARCHAR(150) NOT NULL,
    mensaje VARCHAR(1000) NOT NULL,
    estado VARCHAR(20) NOT NULL DEFAULT 'pendiente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Indice explicito: agiliza la busqueda/ordenamiento de torneos por fecha.
CREATE INDEX idx_torneos_fecha_inicio ON torneos (fecha_inicio);
