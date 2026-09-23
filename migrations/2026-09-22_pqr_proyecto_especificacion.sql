-- Migración: catálogo de Proyecto y campo especificacion en PQRS.
-- Fecha: 2026-09-22

CREATE TABLE IF NOT EXISTS `proyecto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `pqr`
  ADD COLUMN IF NOT EXISTS `especificacion` text DEFAULT NULL;
