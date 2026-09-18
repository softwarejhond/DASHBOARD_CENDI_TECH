-- Migración: porcentajes para el cálculo de nota_final en notas_estudiantes.
-- Fila única (id = 1) editable desde phpMyAdmin.

CREATE TABLE IF NOT EXISTS `notas_pesos` (
  `id` int(11) NOT NULL,
  `peso_tecnico` decimal(5,2) NOT NULL DEFAULT 50.00,
  `peso_ingles` decimal(5,2) NOT NULL DEFAULT 25.00,
  `peso_habilidades` decimal(5,2) NOT NULL DEFAULT 25.00,
  `fecha_actualizacion` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO `notas_pesos` (`id`, `peso_tecnico`, `peso_ingles`, `peso_habilidades`)
VALUES (1, 50.00, 25.00, 25.00);
