CREATE DATABASE IF NOT EXISTS farmacheck;

USE farmacheck;

-- Tabla para almacenar las preguntas
CREATE TABLE IF NOT EXISTS preguntas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nivel VARCHAR(255),
    nivel_descripcion TEXT,
    necesidad VARCHAR(255),
    descripcion TEXT,
    explicacion TEXT,
    tips TEXT
);

-- Tabla para almacenar las respuestas de los usuarios
CREATE TABLE IF NOT EXISTS respuestas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(255),
    pregunta_id INT,
    respuesta VARCHAR(255),
    FOREIGN KEY (pregunta_id) REFERENCES preguntas(id)
);

-- Tabla para almacenar las sesiones de los usuarios
CREATE TABLE IF NOT EXISTS sesiones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(255) UNIQUE,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completado BOOLEAN DEFAULT 0
);
