-- 1. Borrar la base de datos existente
DROP DATABASE IF EXISTS asan_tech;
-- 2. Crear la base de datos
CREATE DATABASE asan_tech;
USE asan_tech;
-- 3. Crear la tabla 'clientes'
CREATE TABLE clientes (
    id_cliente INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    contrasena VARCHAR(255) NOT NULL
);
-- 4. Crear la tabla 'servicios'
CREATE TABLE servicios (
    id_servicio INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    nombre_servicio VARCHAR(100) NOT NULL,
    descripcion TEXT,
    fecha_inicio DATETIME,
    fecha_fin DATETIME,
    costo DECIMAL(10,2),
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE CASCADE
);

