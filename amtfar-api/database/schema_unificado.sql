-- =========================================================================
-- SCRIPT DE CREACIÓN DE BASE DE DATOS UNIFICADA AMTFAR
-- Motor: MySQL (InnoDB)
-- Descripción: Integra la estructura web (Laravel) con el modelo de datos
-- interno del Backoffice (ASP/VBScript).
-- =========================================================================

-- 1. CONFIGURACIÓN INICIAL
SET FOREIGN_KEY_CHECKS=0;
DROP DATABASE IF EXISTS `amtfar`;
CREATE DATABASE `amtfar` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `amtfar`;

-- 2. TABLAS MAESTRAS (CATÁLOGOS Y UBICACIONES)
CREATE TABLE `provincias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE `localidades` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `provincia_id` int(11) DEFAULT NULL,
  `nombre` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`provincia_id`) REFERENCES `provincias`(`id`)
) ENGINE=InnoDB;

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE `escala_salarial` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `categoria_id` int(11) NOT NULL,
  `periodo_desde` int(11) NOT NULL,
  `periodo_hasta` int(11) DEFAULT NULL,
  `importe` decimal(12,2) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`categoria_id`) REFERENCES `categorias`(`id`)
) ENGINE=InnoDB;

CREATE TABLE `conceptos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(50) NOT NULL,
  `porcentaje` decimal(12,2) DEFAULT NULL,
  `importe` decimal(12,2) DEFAULT NULL,
  `siempre` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE `periodos` (
  `id` int(11) NOT NULL,
  `estado` int(11) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

-- 3. ENTIDADES PRINCIPALES (FARMACIAS, PERSONAS, EMPLEADOS)
CREATE TABLE `farmacias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `razon_social` varchar(100) NOT NULL,
  `nombre_fantasia` varchar(100) DEFAULT NULL,
  `cuit` bigint(20) NOT NULL UNIQUE,
  `direccion` varchar(100) DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `localidad_id` int(11) DEFAULT NULL,
  `estado_baja` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`localidad_id`) REFERENCES `localidades`(`id`)
) ENGINE=InnoDB;

-- Tabla maestra de Personas (originaria de ASP tb_Personas)
CREATE TABLE `personas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `cuil` bigint(20) NOT NULL UNIQUE,
  `tipo_documento` int(11) DEFAULT 1,
  `nro_documento` int(11) DEFAULT NULL,
  `direccion` varchar(100) DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `localidad_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`localidad_id`) REFERENCES `localidades`(`id`)
) ENGINE=InnoDB;

-- Tabla intermedia que asocia Personas con Farmacias (originaria de ASP tb_Empleados)
CREATE TABLE `empleados` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `farmacia_id` int(11) NOT NULL,
  `persona_id` int(11) NOT NULL,
  `categoria_id` int(11) NOT NULL,
  `fecha_ingreso` date DEFAULT NULL,
  `fecha_egreso` date DEFAULT NULL,
  `remuneracion_actual` decimal(12,2) DEFAULT NULL,
  `estado_baja` tinyint(1) DEFAULT 0, -- 0: Activo, 1: Baja
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_farmacia_persona` (`farmacia_id`, `persona_id`),
  FOREIGN KEY (`farmacia_id`) REFERENCES `farmacias`(`id`),
  FOREIGN KEY (`persona_id`) REFERENCES `personas`(`id`),
  FOREIGN KEY (`categoria_id`) REFERENCES `categorias`(`id`)
) ENGINE=InnoDB;

-- 4. GESTIÓN DE BOLETAS Y REMUNERACIONES (CORE DEL SISTEMA)
CREATE TABLE `boletas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `farmacia_id` int(11) NOT NULL,
  `periodo_id` int(11) NOT NULL,
  `fecha_vto` date NOT NULL,
  `fecha_boleta` date NOT NULL,
  `imponible_declarado` decimal(12,2) DEFAULT NULL,
  `imponible_calculado` decimal(12,2) DEFAULT NULL,
  `total_deposito` decimal(12,2) NOT NULL,
  `estado` int(11) NOT NULL DEFAULT 1, -- 1: Generada, 2: Impresa, 3: Pagada, 4: Anulada
  `fecha_pago` datetime DEFAULT NULL,
  `codigo_barras` varchar(100) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`farmacia_id`) REFERENCES `farmacias`(`id`),
  FOREIGN KEY (`periodo_id`) REFERENCES `periodos`(`id`)
) ENGINE=InnoDB;

-- Snapshot de lo aportado por cada empleado en esta boleta específica
CREATE TABLE `boletas_remuneraciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `boleta_id` int(11) NOT NULL,
  `empleado_id` int(11) NOT NULL,
  `categoria_id` int(11) NOT NULL,
  `remuneracion_declarada` decimal(12,2) NOT NULL,
  `fecha_ingreso_historica` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`boleta_id`) REFERENCES `boletas`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`empleado_id`) REFERENCES `empleados`(`id`),
  FOREIGN KEY (`categoria_id`) REFERENCES `categorias`(`id`)
) ENGINE=InnoDB;

-- Detalle de conceptos cobrados en esta boleta específica
CREATE TABLE `boletas_conceptos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `boleta_id` int(11) NOT NULL,
  `concepto_id` int(11) NOT NULL,
  `descripcion_historica` varchar(100) NOT NULL,
  `porcentaje_historico` decimal(12,2) DEFAULT NULL,
  `importe_calculado` decimal(12,2) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`boleta_id`) REFERENCES `boletas`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`concepto_id`) REFERENCES `conceptos`(`id`)
) ENGINE=InnoDB;

-- 5. IMPORTACIONES BANCARIAS (BACKOFFICE)
CREATE TABLE `importaciones_bancarias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_archivo` varchar(100) NOT NULL,
  `fecha_procesamiento` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE `importaciones_detalle` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `importacion_id` int(11) NOT NULL,
  `boleta_id` int(11) DEFAULT NULL, -- Relación con la boleta encontrada, si existe
  `fecha_pago` date NOT NULL,
  `importe_pagado` decimal(12,2) NOT NULL,
  `codigo_barras` varchar(100) NOT NULL,
  `estado_conciliacion` tinyint(1) DEFAULT 0, -- 0: Pendiente, 1: Conciliado, 2: Anulado/Error
  PRIMARY KEY (`id`),
  FOREIGN KEY (`importacion_id`) REFERENCES `importaciones_bancarias`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`boleta_id`) REFERENCES `boletas`(`id`)
) ENGINE=InnoDB;

-- 6. USUARIOS Y ROLES (SEGURIDAD)
CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `farmacia_id` int(11) DEFAULT NULL, -- Nulo si es de backoffice
  `rol_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `nombre_completo` varchar(100) DEFAULT NULL,
  `estado` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`farmacia_id`) REFERENCES `farmacias`(`id`),
  FOREIGN KEY (`rol_id`) REFERENCES `roles`(`id`)
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS=1;
