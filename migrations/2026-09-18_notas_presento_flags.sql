-- Migración: marca de presencia de cada nota en notas_estudiantes.
-- Permite distinguir "sin calificar" de "sacó 0", necesario para emitir diplomas.

ALTER TABLE `notas_estudiantes`
  ADD COLUMN `presento_tecnico` TINYINT(1) NOT NULL DEFAULT 0 AFTER `nota_tecnico`,
  ADD COLUMN `presento_ingles` TINYINT(1) NOT NULL DEFAULT 0 AFTER `nota_ingles`,
  ADD COLUMN `presento_habilidades` TINYINT(1) NOT NULL DEFAULT 0 AFTER `nota_habilidades`;
