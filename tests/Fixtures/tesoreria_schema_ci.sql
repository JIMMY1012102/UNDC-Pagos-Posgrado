-- Sanitary CI-only schema fixture matching the six master table definitions
-- and signed INT primary keys from the supplied February 2026 tesoreria.sql.
-- Deliberately contains NO institutional or personally identifying rows.
-- NOT a substitute for the original backup nor a production migration.

CREATE TABLE tm_oficina (
 oficina_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
 oficina_nom VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
 estado INT NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE tm_rol (
 rol_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
 rol_nom VARCHAR(50) NOT NULL,
 estado INT NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE tm_usuario (
 usu_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
 usu_nom VARCHAR(45) NOT NULL,
 usu_ape VARCHAR(45) NOT NULL,
 usu_telf VARCHAR(11) DEFAULT NULL,
 usu_correo VARCHAR(100) NOT NULL,
 rol_id INT NOT NULL,
 oficina_id INT NOT NULL,
 fech_crea DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 fech_modi DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
 fech_elim DATETIME DEFAULT NULL,
 estado INT NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE tm_conceptos (
 id_concepto INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
 codigo_concepto VARCHAR(50) NOT NULL,
 codigo_banco VARCHAR(50) DEFAULT NULL,
 es_gravado TINYINT(1) DEFAULT 0,
 concepto VARCHAR(255) NOT NULL,
 clasificador_ingresos INT DEFAULT NULL,
 costo DECIMAL(10,2) DEFAULT NULL,
 cuenta_corriente INT DEFAULT NULL,
 plan_contable INT DEFAULT NULL,
 centro_costo INT DEFAULT NULL,
 afecto_division TINYINT(1) DEFAULT 0,
 creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE tm_recaudacion (
 id_recaudacion INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
 codigo_deudor VARCHAR(20) DEFAULT NULL,
 codigo_cuota VARCHAR(20) DEFAULT NULL,
 nombre_deudor VARCHAR(150) DEFAULT NULL,
 referencia1 VARCHAR(50) DEFAULT NULL,
 referencia2 VARCHAR(50) DEFAULT NULL,
 emision DATE DEFAULT NULL,
 vencimiento DATE DEFAULT NULL,
 documento VARCHAR(10) DEFAULT NULL,
 moneda VARCHAR(5) DEFAULT NULL,
 concepto1 DECIMAL(10,2) DEFAULT NULL,
 concepto2 DECIMAL(10,2) DEFAULT NULL,
 concepto3 DECIMAL(10,2) DEFAULT NULL,
 concepto4 DECIMAL(10,2) DEFAULT NULL,
 concepto5 DECIMAL(10,2) DEFAULT NULL,
 concepto6 DECIMAL(10,2) DEFAULT NULL,
 concepto7 DECIMAL(10,2) DEFAULT NULL,
 tipo_operacion CHAR(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE tm_resultado (
 i_resultado INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
 codigo_rubro VARCHAR(5) NOT NULL,
 codigo_empresa VARCHAR(5) NOT NULL,
 codigo_servicio VARCHAR(5) NOT NULL,
 moneda VARCHAR(10) NOT NULL,
 codigo_deudor VARCHAR(20) NOT NULL,
 codigo_cuota VARCHAR(30) NOT NULL,
 numero_documento VARCHAR(20) DEFAULT NULL,
 nombre_deudor VARCHAR(150) NOT NULL,
 fecha_pago DATE DEFAULT NULL,
 hora_pago TIME DEFAULT NULL,
 importe_pagado DECIMAL(12,2) DEFAULT 0.00,
 importe_mora DECIMAL(12,2) DEFAULT 0.00,
 importe_descuento DECIMAL(12,2) DEFAULT 0.00,
 fecha_emision DATE DEFAULT NULL,
 fecha_vencimiento DATE DEFAULT NULL,
 numero_operacion VARCHAR(30) DEFAULT NULL,
 numero_cheque VARCHAR(30) DEFAULT NULL,
 indicador_cheque VARCHAR(30) DEFAULT NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
